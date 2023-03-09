<?php

namespace App\Http\Controllers\Admin;

use App\Constants\RespondentStatus;
use App\Constants\TargetStatus;
use App\Http\Controllers\Controller;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Target;
use App\TargetTrack;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use MathPHP\Exception\BadDataException;
use MathPHP\Statistics\Descriptive;

class TargetTracksController extends Controller {

    private $pageTitle = 'Targets & Progress';

    /**
     * Display the specified resource.
     *
     * @param  string  $projectCode
     *
     * @return View
     * @throws AuthorizationException
     */
    public function index(string $projectCode): ?View {
        $this->authorize('manage-projects');

        $quotas = $this->getQuotas($projectCode);
        $stats = $this->getStates($projectCode);
        $title = $this->pageTitle;

        return view('admin.projects.target_tracks.index', compact('projectCode', 'stats',
            'quotas', 'title'));
    }

    public function editQuotas(string $projectCode) {
        $this->authorize('manage-projects');

        $quotas = $this->getQuotas($projectCode);
        $totalCompletesLimit = DB::table('projects')
            ->where('project_code', $projectCode)
            ->value('total_complete_limit');

        $title = $this->pageTitle;
        return view('admin.projects.target_tracks.edit', compact('projectCode', 'quotas',
            'title', 'totalCompletesLimit'));
    }

    public function updateQuotas(string $projectCode): RedirectResponse {
        $this->authorize('manage-projects');

        $data = request()->all(['quota']);

        Validator::make($data, [
            'quota'   => ['required', 'array'],
            'quota.*' => ['required', 'numeric'],
        ])->validate();

        foreach ($data['quota'] as $targetTrackId => $quotaLimit) {
            TargetTrack::find($targetTrackId)->update([
                'quota_amount' => $quotaLimit,
            ]);
        }

        return redirect()->route('admin.projects.target_track.index', ['project_code' => $projectCode]);
    }

    public function updateCompleteLimit(string $projectCode): RedirectResponse {
        $this->authorize('manage-projects');

        $data = request()->all(['complete_limit']);

        Validator::make($data, [
            'complete_limit' => ['required', 'integer'],
        ])->validate();

        DB::table('projects')
            ->where('project_code', $projectCode)
            ->update(['total_complete_limit' => $data['complete_limit']]);

        try {
            cache()->forget(ProjectUtils::getTotalCompleteLimitCacheKey($projectCode));
        } catch (\Exception $e) {
        }

        return redirect()->route('admin.projects.target_track.index', ['project_code' => $projectCode]);
    }

    /**
     * @param  string  $projectCode
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function generateTargets(string $projectCode): RedirectResponse {
        $this->authorize('admin-projects');

        $targetsList = $this->getTargetsList($projectCode);

        $targets = [];
        foreach ($targetsList as $criteria => $values) {

            foreach ($values as $value) {
                $targets[] = Target::firstOrCreate([
                    'project_code' => $projectCode,
                    'criteria'     => $criteria,
                    'value'        => $value,
                ], [
                    'status' => TargetStatus::OPEN,
                ]);
            }
        }

        if ( ! $this->buildTargetTracks($projectCode, $targets)) {
            return redirect()->route('admin.projects.target_track.index', ['project_code' => $projectCode]);
        }

        $this->handleRecountQuotas($projectCode);

        return redirect()->route('admin.projects.target_track.index', ['project_code' => $projectCode]);
    }

    /**
     * @param  string  $projectCode
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function recountCompletes(string $projectCode): RedirectResponse {
        $this->authorize('admin-projects');

        $this->handleRecountQuotas($projectCode, true);

        return redirect()->route('admin.projects.target_track.index', ['project_code' => $projectCode]);
    }

    /**
     * @param  string  $projectCode
     *
     * @return string[][]|null
     */
    private function getTargetsList(string $projectCode): ?array {
        return ProjectUtils::getConfigs($projectCode, true)['targets'] ?? null;
    }

    /**
     * (Re)build target tracks.
     *
     * @param  string  $projectCode
     * @param  array  $targets
     *
     * @return bool
     */
    private function buildTargetTracks(string $projectCode, array $targets): bool {
        if (count($targets) === 0) {
            return false;
        }

        // Delete existing tracks and pivot records.
        foreach ($targets as $target) {
            $target->targetTracks()->detach();
        }
        $oldTargetTracks = TargetTrack::query()
            ->where('project_code', $projectCode)
            ->get();

        TargetTrack::query()
            ->where('project_code', $projectCode)
            ->delete();

        $targetsIdByCriteria = [];
        foreach (collect($targets)->groupBy(['criteria']) as $criteria => $targetsList) {
            $targetsIdByCriteria[$criteria] = Arr::pluck($targetsList, 'id');
        }

        $targetsJoins = ProjectUtils::buildTargetsJoins($projectCode, $targetsIdByCriteria, true);

        foreach ($targetsJoins as $targetsJoin) {
            $quotaAmount = 100;
            $quotaPercentage = 0;

            // If exactly the same target track already existed, set the same quota as previously.
            foreach ($oldTargetTracks as $oldTargetTrack) {
                if (count($oldTargetTrack->reference) !== count($targetsJoin)) {
                    continue;
                }

                if (array_diff($oldTargetTrack->reference,$targetsJoin)) {
                    continue;
                }

                $quotaAmount = $oldTargetTrack->quota_amount;
                $quotaPercentage = $oldTargetTrack->quota_percentage;
            }

            $targetTrack = TargetTrack::create([
                'project_code'     => $projectCode,
                'quota_amount'     => $quotaAmount,
                'quota_percentage' => $quotaPercentage,
                'relation'         => '[' . implode('] AND [', $targetsJoin) . ']',
                'reference'        => $targetsJoin,
            ]);

            $targetTrack->targets()->sync($targetsJoin);
        }

        return true;
    }

    /**
     * Assumes passed targets hit one of each required criteria target.
     *
     * @param  string  $projectCode
     * @param  bool  $forceAllQuotas
     */
    private function handleRecountQuotas(string $projectCode, bool $forceAllQuotas = false): void {
        // Reset counts
        TargetTrack::query()
            ->where('project_code', $projectCode)
            ->update(['count' => 0]);

        // Count and increment accordingly
        $successfulRespondents = Respondent::query()
            ->where('project_code', $projectCode)
            ->where('current_status', RespondentStatus::COMPLETED)
            ->pluck('target_hits', 'id');

        foreach ($successfulRespondents as $targetHits) {
            ProjectUtils::incrementHitQuotas($projectCode, $targetHits, $forceAllQuotas, true);
        }
    }

    /**
     * @param  string  $projectCode
     *
     * @return array
     */
    private function getStates(string $projectCode): array {
        $stats = [];
        $this->getProgressStates($stats, $projectCode);

        $countScreenOuts = $stats['counts_per_status'][RespondentStatus::SCREEN_OUT] ?? 0;
        $countCompletes = $stats['counts_per_status'][RespondentStatus::COMPLETED] ?? 0;
        $this->getIRStats($stats, $countCompletes, $countScreenOuts);
        $this->getLOIStats($stats, $projectCode);

        return $stats;
    }

    private function getLOIStats(array &$stats, string $projectCode): void {
        $lois = DB::table('respondents')
            ->where('project_code', $projectCode)
            ->where('current_status', RespondentStatus::COMPLETED)
            ->where('is_test', false)
            ->pluck('actual_loi')
            ->toArray();
        // Make sure all LOI values are not empty.
        $lois = array_filter($lois);

        $stats['loi'] = [
            'outliers'  => null,
            'qualified' => null,
            'average'   => null,
        ];

        $loiOutlierBorders = $this->getLOIOutlierBorders($lois);
        $stats['loi'] = array_merge($stats['loi'], $loiOutlierBorders);
        if (empty($loiOutlierBorders)) {
            return;
        }

        $qualifiedLois = [];
        foreach ($lois as $loi) {
            if ($loi >= $loiOutlierBorders['min_loi'] && $loi <= $loiOutlierBorders['max_loi']) {
                $stats['loi']['qualified']++;
                $qualifiedLois[] = $loi;
                continue;
            }

            $stats['loi']['outliers']++;
        }

        if ($qualifiedLois) {
            $stats['loi']['average'] = array_sum($qualifiedLois) / count($qualifiedLois);
        }
    }

    /**
     * @param  array  $stats
     * @param  int  $completes
     * @param  int  $screenOuts
     */
    private function getIRStats(array &$stats, int $completes, int $screenOuts): void {
        $stats['ir'] = null;
        if (empty($completes)) {
            return;
        }

        if (empty($screenOuts)) {
            $stats['ir'] = 1;
            return;
        }

        $stats['ir'] = $completes / ($completes + $screenOuts);
    }

    private function getProgressStates(array &$stats, string $projectCode): void {
        $countsPerStatusPerDate = [
            'field_dates'                => null,
            'counts_per_status_per_date' => null,
            'counts_per_date'            => null,
            'counts_per_status'          => null,
        ];

        $countsPerStatus = DB::table('respondents')
            ->selectRaw('current_status, DATE(updated_at) AS date, COUNT(*) AS count')
            ->where('project_code', $projectCode)
            ->where('is_test', false)
            ->groupBy(['current_status', 'date'])
            ->orderBy('date')
            ->get()
            ->groupBy('current_status');
        if ($countsPerStatus->isEmpty()) {
            $stats = array_merge($stats, $countsPerStatusPerDate);
            return;
        }

        $fieldDates = array_unique($countsPerStatus->pluck('*.date')->flatten()->toArray());
        // Sort dates ascending.
        usort($fieldDates, static function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $countsPerStatusPerDate = [];
        $countsPerDate = [];
        $totalPerStatus = [];
        foreach ($countsPerStatus as $status => $statusCounts) {
            if ( ! isset($totalPerStatus[$status])) {
                $totalPerStatus[$status] = 0;
            }

            $keyByDate = collect($statusCounts)->keyBy('date');

            foreach ($fieldDates as $date) {
                $countsPerStatusPerDate[$status][$date] = 0;

                if ( ! isset($countsPerDate[$date])) {
                    $countsPerDate[$date] = 0;
                }

                if ($foundData = $keyByDate->get($date)) {
                    $countsPerStatusPerDate[$status][$date] = $foundData->count;
                    $countsPerDate[$date] += $foundData->count;
                    $totalPerStatus[$status] += $foundData->count;
                }
            }
        }

        $stats['field_dates'] = $fieldDates;
        $stats['counts_per_status_per_date'] = $countsPerStatusPerDate;
        $stats['counts_per_status'] = $totalPerStatus;
        $stats['counts_per_date'] = $countsPerDate;
    }

    /**
     * @param  array  $lois
     *
     * @return array
     */
    private function getLOIOutlierBorders(array $lois): array {
        $result = [
            'min_loi'    => null,
            'max_loi'    => null,
            'median_loi' => null,
        ];

        if (empty($lois)) {
            return $result;
        }

        try {
            $states = Descriptive::fiveNumberSummary($lois);
            $iqr = Descriptive::iqr($lois);

            $result['min_loi'] = $states['Q1'] - (1.5 * $iqr);
            $result['max_loi'] = $states['Q3'] + (1.5 * $iqr);
            $result['median_loi'] = $states['median'];
        } catch (BadDataException $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return $result;
        }

        return $result;
    }

    /**
     * @param  string  $projectCode
     *
     * @return array
     */
    private function getQuotas(string $projectCode): array {
        $targetTracks = TargetTrack::query()
            ->with('targets')
            ->where('project_code', $projectCode)
            ->get();

        $quotas = [];
        foreach ($targetTracks as $targetTrack) {
            if ($targetTrack->targets->count() === 0) {
                continue;
            }

            $quotaTrack = [];
            foreach ($targetTrack->targets as $target) {
                $quotaTrack[] = "{$target->criteria}: {$target->value}";
            }

            $quotas[] = [
                'id'       => $targetTrack->id,
                'quota'    => $targetTrack->quota_amount,
                'count'    => $targetTrack->count,
                'relation' => $targetTrack->relation,
                'label'    => implode(', ', $quotaTrack),
            ];
        }

        return $quotas;
    }
}

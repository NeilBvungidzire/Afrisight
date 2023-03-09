<?php

namespace App\Libraries\Project;

use App\Constants\RespondentStatus;
use App\Jobs\EngageAudienceForSurvey;
use App\Target;
use App\TargetTrack;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @deprecated
 */
class deprecated_AudienceEngagement {

    /**
     * @var Collection|null
     */
    private $openTargets = null;

    public function run(): void
    {
        $openAutoEngageRecords = \App\AudienceEngagement::getNext();

        foreach ($openAutoEngageRecords as $record) {
            $criteria = $record->applicable_criteria;
            $batchSizeLimit = $record->batch_size;
            if (empty($criteria) || empty($batchSizeLimit)) {
                continue;
            }

            $batchCode = Str::random(10);
            $size = $this->handleOpenRecord($record->project_code, $criteria, $batchSizeLimit, $batchCode,
                $record->meta_data);

            $record->batchRun($size);
            $record->batches()->create([
                'code' => $batchCode,
                'size' => $size,
            ]);
        }
    }

    /**
     * @param string     $projectCode
     * @param array      $applicableCriteria
     * @param int        $batchSizeLimit
     * @param string     $batchCode
     * @param array|null $options
     *
     * @return int
     */
    private function handleOpenRecord(
        string $projectCode,
        array  $applicableCriteria,
        int    $batchSizeLimit,
        string $batchCode,
        array  $options = null
    ): int
    {
        $openTracks = TargetTrack::query()
            ->where('project_code', $projectCode)
            ->whereRaw('count < quota_amount')
            ->get();

        $this->setOpenTargets($openTracks);

        $openTracks = TargetTrack::setAdditionalStates($openTracks);

        $countEngagedPersons = 0;
        foreach ($openTracks as $track) {
            $projectConfigs = ProjectUtils::getConfigs($track->project_code);
            if ( ! $projectConfigs['live']) {
                continue;
            }

            if ( ! $incentivePackage = ProjectUtils::getIncentivePackage($track->project_code)) {
                continue;
            }

            $batchSize = $track->calculateBatchSize($batchSizeLimit, $track->priority_weight);
            $personsToEngage = $this->getPersonToEngage($projectCode, $applicableCriteria, $track, $batchSize,
                $options['can_reinvite'] ?? false);

            $count = $personsToEngage->count();
            if ($count === 0) {
                continue;
            }

            $personsId = $personsToEngage->pluck('id')->toArray();
            if (app()->environment('production')) {
                EngageAudienceForSurvey::dispatch($personsId, $projectCode, $incentivePackage, 'email',
                    ['batch_code' => $batchCode])
                    ->delay(now()->addSeconds(1));
            }

            $countEngagedPersons += $count;
        }

        return $countEngagedPersons;
    }

    /**
     * @param string      $projectCode
     * @param array       $applicableCriteria
     * @param TargetTrack $targetTrack
     * @param int         $limit
     * @param bool        $canReinvite
     *
     * @return Collection
     */
    private function getPersonToEngage(
        string      $projectCode,
        array       $applicableCriteria,
        TargetTrack $targetTrack,
        int         $limit,
        bool        $canReinvite = false
    ): Collection
    {
        $quotaTargetsByCriteria = $targetTrack->targets->keyBy('criteria');

        // Only use criteria which is allowed.
        foreach ($quotaTargetsByCriteria as $criteria => $target) {
            if (in_array($criteria, $applicableCriteria)) {
                continue;
            }

            $quotaTargetsByCriteria->forget($criteria);
        }

        // No target to search for.
        if ($quotaTargetsByCriteria->count() === 0) {
            return new Collection();
        }

        // Build list of criteria and values to query on.
        $targetsToQueryByCriteria = $quotaTargetsByCriteria;

        // Get list of missing criteria.
        $missingCriteria = array_diff($applicableCriteria, $quotaTargetsByCriteria->keys()->toArray());
        if ( ! empty($missingCriteria) && $this->openTargets !== null) {
            // Get all targets by missing criteria and add to collection.
            $missingTargets = $this->openTargets->whereIn('criteria', $missingCriteria);

            foreach ($missingTargets as $missingTarget) {
                $targetsToQueryByCriteria->add($missingTarget);
            }
        }

        $targetsToQueryByCriteria = $targetsToQueryByCriteria->groupBy('criteria');

        $query = new AudienceQueryBuilder();

        $query->setLimit($limit);

        // Language restriction
        $languageRestrictions = ProjectUtils::getLanguageRestrictions($projectCode);
        if ( ! empty($languageRestrictions)) {
            $query->setLanguage($languageRestrictions);
        }

        foreach ($targetsToQueryByCriteria as $criteria => $targets) {
            $values = $targets->pluck('value')->toArray();

            if ($criteria === 'country') {
                $query->setCountries($values);
                continue;
            }

            if ($criteria === 'gender') {
                $query->setGenders($values);
                continue;
            }

            if ($criteria === 'age_range') {
                $query->setAgeRanges($values);
                continue;
            }
        }

        // Excluding other projects participants
        $excludeProjects = ProjectUtils::getConfigs($projectCode)['configs']['exclude_respondents_from_projects'] ?? null;
        if ( ! empty($excludeProjects)) {
            foreach ($excludeProjects as $excludeProject) {
                $query->excludeRespondents($excludeProject, [
                    RespondentStatus::RESELECTED,
                    RespondentStatus::INVITED,
                    RespondentStatus::REMINDED,
                    RespondentStatus::TARGET_UNSUITABLE,
                    RespondentStatus::QUOTA_FULL,
                    RespondentStatus::CLOSED,
                ]);
            }
        }

        // Re-invite
        if ($canReinvite) {
            $query->excludeRespondents($projectCode, [
                RespondentStatus::INVITED,
            ]);
        } else {
            $query->excludeRespondents($projectCode);
        }

        return $query->orderBy()->getQuery()->get();
    }

    /**
     * @param Collection $targetTracks
     */
    private function setOpenTargets(Collection $targetTracks)
    {
        $ids = $targetTracks->pluck('reference')->flatten()->flip()->flip();

        if (empty($ids)) {
            return;
        }

        $this->openTargets = Target::find($ids);
    }
}

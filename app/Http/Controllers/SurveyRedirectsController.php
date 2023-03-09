<?php

namespace App\Http\Controllers;

use App\Constants\RespondentStatus;
use App\ExternalRespondent;
use App\Jobs\RetrieveInternalPersonIdForExternalSource;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SurveyRedirectsController extends Controller {

    public function completed() {
        $status = RespondentStatus::COMPLETED;

        if ($redirect = $this->handleExternalRespondent($status)) {
            return redirect($redirect);
        }

        [
            'uuid'         => $uuid,
            'project_code' => $projectCode,
        ] = $this->handleRespondent($status);

        return $this->redirect($uuid, $status, $projectCode);
    }

    public function terminated() {
        $status = RespondentStatus::DISQUALIFIED;

        if ($redirect = $this->handleExternalRespondent($status)) {
            return redirect($redirect);
        }

        [
            'uuid'         => $uuid,
            'project_code' => $projectCode,
        ] = $this->handleRespondent($status);

        return $this->redirect($uuid, $status, $projectCode);
    }

    public function quotaReached() {
        $status = RespondentStatus::QUOTA_FULL;

        if ($redirect = $this->handleExternalRespondent($status)) {
            return redirect($redirect);
        }

        [
            'uuid'         => $uuid,
            'project_code' => $projectCode,
        ] = $this->handleRespondent($status);

        return $this->redirect($uuid, $status, $projectCode);
    }

    public function closed() {
        $status = RespondentStatus::CLOSED;

        if ($redirect = $this->handleExternalRespondent($status)) {
            return redirect($redirect);
        }

        [
            'uuid'         => $uuid,
            'project_code' => $projectCode,
        ] = $this->handleRespondent($status);

        return $this->redirect($uuid, $status, $projectCode);
    }

    public function screenOut() {
        $status = RespondentStatus::SCREEN_OUT;

        if ($redirect = $this->handleExternalRespondent($status)) {
            return redirect($redirect);
        }

        [
            'uuid'         => $uuid,
            'project_code' => $projectCode,
        ] = $this->handleRespondent($status);

        return $this->redirect($uuid, $status, $projectCode);
    }

    /**
     * @param  string  $cipher
     * @return RedirectResponse|View
     */
    public function feedback(string $cipher) {
        ['uuid' => $uuid, 'status' => $status, 'project_code' => $projectCode] = decrypt($cipher);

        $noRewardMentioning = false;
        if ($status === RespondentStatus::COMPLETED && ! empty($projectCode)) {
            $noRewardMentioning = ProjectUtils::getConfigs($projectCode)['configs']['subtle_rewarding'] ?? false;
        }

        if ($status === RespondentStatus::COMPLETED) {
            return view('redirects.completed', compact('noRewardMentioning'));
        }

        if ($status === RespondentStatus::DISQUALIFIED) {
            return view('redirects.disqualified');
        }

        if ($status === RespondentStatus::QUOTA_FULL) {
            return view('redirects.quota_reached');
        }

        if ($status === RespondentStatus::CLOSED) {
            return view('redirects.closed');
        }

        if ($status === RespondentStatus::SCREEN_OUT) {
            return view('redirects.screen-out');
        }

        // Not expected or no status was found.
        return redirect()->route('home');
    }

    /**
     * @param  string|null  $uuid
     * @param  string|null  $currentStatus
     * @param  string|null  $projectCode
     * @return RedirectResponse
     */
    private function redirect(?string $uuid, ?string $currentStatus, ?string $projectCode): RedirectResponse {
        $cipher = encrypt([
            'uuid'         => $uuid,
            'status'       => $currentStatus,
            'project_code' => $projectCode,
        ]);

        return redirect()->route('survey-redirect.feedback', ['cipher' => $cipher]);
    }

    private function handleRespondent(string $status): array {
        $uuid = null;
        if ($respondentIdParam = request()->query('rid_param')) {
            $uuid = request()->query($respondentIdParam);
        }
        if ( ! $uuid) {
            $uuid = request()->query('id');
        }

        $response = [
            'uuid'         => $uuid,
            'project_code' => null,
        ];

        if (empty($uuid)) {
            return $response;
        }

        /** @var Respondent|null $respondent * */
        $respondent = Respondent::query()
            ->where('uuid', $uuid)
            ->first();
        if ( ! $respondent) {
            Log::error('Could not find the respondent on redirect page.', [
                'uuid' => $uuid,
            ]);

            return $response;
        }

        $response['project_code'] = $projectCode = $respondent->project_code;

        if ($respondent->current_status !== RespondentStatus::STARTED) {
            Log::error('Respondent status was ' . $status . ' and not STARTED', [
                'uuid' => $uuid,
            ]);

            return $response;
        }

        $currentTimeString = date('Y-m-d H:i:s');
        $respondent->update([
            'current_status' => $status,
            'status_history' => array_merge($respondent->status_history, [
                $status => $currentTimeString,
            ]),
            'actual_loi'     => $this->getLOI($respondent, $currentTimeString),
        ]);

        // Handle quota count
        $quotaHandlerMethod = ProjectUtils::getConfigs($projectCode)['configs']['quota_count_method'] ?? null;
        if ($quotaHandlerMethod) {
            $this->{$quotaHandlerMethod}($status, $respondent);
        }

        return $response;
    }

    /**
     * @param  Respondent  $respondent
     * @param  string  $newTime
     *
     * @return int|null
     */
    private function getLOI(Respondent $respondent, string $newTime): ?int {
        if ( ! isset($respondent->status_history[RespondentStatus::STARTED])) {
            return null;
        }

        $startTime = Date::createFromFormat('Y-m-d H:i:s', $respondent->status_history[RespondentStatus::STARTED]);
        $endTime = Date::createFromFormat('Y-m-d H:i:s', $newTime);

        return $startTime->diffInMinutes($endTime, false);
    }

    /**
     * @param  string  $status
     *
     * @return string|null
     */
    private function handleExternalRespondent(string $status): ?string {
        $uuid = null;
        if ($respondentIdParam = request()->query('rid_param')) {
            $uuid = request()->query($respondentIdParam);
        }
        if ( ! $uuid) {
            $uuid = request()->query('id');
        }

        if (empty($uuid)) {
            return null;
        }

        $externalRespondent = ExternalRespondent::query()
            ->where('uuid', $uuid)
            ->where('status', RespondentStatus::STARTED)
            ->first();

        if ( ! $externalRespondent) {
            return null;
        }

        if ( ! $externalRespondent->person_id && $externalRespondent->source === 'cint') {
            RetrieveInternalPersonIdForExternalSource::dispatch($externalRespondent->id);
        }

        return route('intermediary.finish', ['uuid' => $uuid, 'status' => $status]);
    }

    /**
     * @param  string  $status
     * @param  Respondent  $respondent
     */
    private function handleProjectQuota(string $status, Respondent $respondent): void {
        if ($status !== RespondentStatus::COMPLETED) {
            return;
        }

        if (empty($respondent->target_hits)) {
            return;
        }

        $forceAllQuotas = ProjectUtils::getConfigs($respondent->project_code)['configs']['force_all_quotas'] ?? true;

        ProjectUtils::incrementHitQuotas($respondent->project_code, $respondent->target_hits, $forceAllQuotas, true);
    }
}

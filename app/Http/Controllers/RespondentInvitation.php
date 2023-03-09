<?php

namespace App\Http\Controllers;

use App\Constants\DataPointAttribute;
use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Constants\TargetStatus;
use App\DataPoint;
use App\Libraries\GeoIPData;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Target;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class RespondentInvitation extends Controller {

    /**
     * @param string $uuid
     *
     * @return RedirectResponse
     */
    public function land(string $uuid): RedirectResponse
    {
        // Check if exists.
        $respondentInvitation = \App\RespondentInvitation::with(['respondent' => static function($query) {
            $query->select('id', 'uuid');
        }])
            ->where('uuid', $uuid)
            ->first();

        if ( ! $respondentInvitation || ! $respondent = $respondentInvitation->respondent) {
            Log::error('Could not find the respondent.', [
                'uuid' => $uuid,
            ]);

            return redirect()->route('home');
        }

        // Mark invitation as opened, if not yet.
        if (in_array($respondentInvitation->status, [
            RespondentInvitationStatus::SEND,
            RespondentInvitationStatus::DISPLAYED,
            RespondentInvitationStatus::REDIRECTED,
        ], true)) {
            $respondentInvitation->status = RespondentInvitationStatus::OPENED;
            $respondentInvitation->save();
        }

        return redirect()->route('invitation.entry', ['uuid' => $respondent->uuid]);
    }

    public function entry(string $uuid): RedirectResponse
    {
        /** @var Respondent|null $respondent */
        $respondent = Respondent::query()
            ->where('uuid', $uuid)
            ->first();
        if ( ! $respondent) {
            Log::error('Could not find the respondent.', [
                'uuid' => $uuid,
            ]);

            return redirect()->route('home');
        }

        $this->setDataPoints($respondent->person_id);

        if ( ! $projectCode = $respondent->project_code) {
            Log::error("Could not find the project by project code: ${projectCode}.", [
                'uuid' => $uuid,
            ]);

            return redirect()->route('home');
        }
        $projectEnvs = ProjectUtils::getConfigs($projectCode);

        if ( ! $projectEnvs || empty($projectEnvs['configs']) || ! ProjectUtils::isLive($projectCode)) {
            return redirect()->route('survey-redirect.closed', ['id' => $uuid]);
        }

        if ($projectEnvs['configs']['customized_qualification'] ?? null) {
            // Customized qualification flow.
            return $this->customizedQualification($respondent, $uuid, $projectEnvs['configs']['customized_qualification']);
        }

        if ($projectEnvs['configs']['needs_qualification'] ?? null) {
            // Predefined qualification params flow.
            return $this->withQualificationRound(
                $respondent,
                $uuid,
                $projectEnvs['configs']['background_check'] ?? false
            );
        }

        // Without any qualification flow.
        return $this->withoutQualificationRound($respondent, $uuid);
    }

    /**
     * @param Respondent $respondent
     * @param string     $uuid
     * @param string     $routName
     * @return RedirectResponse
     */
    private function customizedQualification(Respondent $respondent, string $uuid, string $routName): RedirectResponse
    {
        switch ($respondent->current_status) {

            case RespondentStatus::QUOTA_FULL:
            case RespondentStatus::CLOSED:
                return redirect()->route('survey-redirect.quota-full', ['id' => $uuid]);

            case RespondentStatus::DISQUALIFIED:
            case RespondentStatus::TARGET_UNSUITABLE:
                return redirect()->route('survey-redirect.terminate', ['id' => $uuid]);

            case RespondentStatus::SCREEN_OUT:
                return redirect()->route('survey-redirect.screen-out', ['id' => $uuid]);

            case RespondentStatus::SELECTED:
            case RespondentStatus::RESELECTED:
            case RespondentStatus::INVITED:
            case RespondentStatus::REMINDED:
            case RespondentStatus::ENROLLING:
                return redirect()->route($routName, ['uuid' => $uuid]);

            case RespondentStatus::STARTED:
            case RespondentStatus::TARGET_SUITABLE:
                return $this->start($uuid, $respondent);
        }

        return redirect()->route('home');
    }

    /**
     * @param Respondent $respondent
     * @param string     $uuid
     * @param bool       $backgroundCheck
     *
     * @return RedirectResponse
     */
    private function withQualificationRound(Respondent $respondent, string $uuid, bool $backgroundCheck = false): RedirectResponse
    {
        switch ($respondent->current_status) {

            case RespondentStatus::QUOTA_FULL:
            case RespondentStatus::CLOSED:
                return redirect()->route('survey-redirect.quota-full', ['id' => $uuid]);

            case RespondentStatus::DISQUALIFIED:
            case RespondentStatus::TARGET_UNSUITABLE:
                return redirect()->route('survey-redirect.terminate', ['id' => $uuid]);

            case RespondentStatus::SCREEN_OUT:
                return redirect()->route('survey-redirect.screen-out', ['id' => $uuid]);

            case RespondentStatus::SELECTED:
            case RespondentStatus::RESELECTED:
            case RespondentStatus::INVITED:
            case RespondentStatus::REMINDED:
            case RespondentStatus::ENROLLING:
                if ($backgroundCheck) {
                    return redirect()->route('enrollment.background_qualification_check', ['uuid' => $uuid]);
                }

                return redirect()->route('enrollment.questionnaire', ['uuid' => $uuid]);

            case RespondentStatus::STARTED:
            case RespondentStatus::TARGET_SUITABLE:
                return $this->start($uuid, $respondent);
        }

        return redirect()->route('home');
    }

    /**
     * @param Respondent $respondent
     * @param string     $uuid
     *
     * @return RedirectResponse
     */
    private function withoutQualificationRound(Respondent $respondent, string $uuid): RedirectResponse
    {
        switch ($respondent->current_status) {

            case RespondentStatus::QUOTA_FULL:
            case RespondentStatus::CLOSED:
                return redirect()->route('survey-redirect.quota-full', ['id' => $uuid]);

            case RespondentStatus::DISQUALIFIED:
            case RespondentStatus::TARGET_UNSUITABLE:
                return redirect()->route('survey-redirect.terminate', ['id' => $uuid]);

            case RespondentStatus::SCREEN_OUT:
                return redirect()->route('survey-redirect.screen-out', ['id' => $uuid]);

            case RespondentStatus::SELECTED:
            case RespondentStatus::RESELECTED:
            case RespondentStatus::INVITED:
            case RespondentStatus::REMINDED:
            case RespondentStatus::ENROLLING:
            case RespondentStatus::STARTED:
            case RespondentStatus::TARGET_SUITABLE:
                return $this->start($uuid, $respondent);
        }

        return redirect()->route('home');
    }

    /**
     * @param string     $uuid
     * @param Respondent $respondent
     * @return RedirectResponse
     */
    private function start(string $uuid, Respondent $respondent): RedirectResponse
    {
        $projectCode = $respondent->project_code;

        if ( ! $respondent) {
            $respondent->uuid = $uuid;
        }

        $projectEnvs = ProjectUtils::getConfigs($projectCode);
        if ( ! $projectEnvs || empty($projectEnvs['configs'])) {
            Log::error('Project configs not set correctly');

            return redirect()->route('survey-redirect.closed', ['id' => $respondent->uuid]);
        }
        if ( ! $projectEnvs['live']) {
            return redirect()->route('survey-redirect.closed', ['id' => $respondent->uuid]);
        }

        // Only enable respondents entering the survey if the device they are on at the moment is allowed.
        if ( ! ProjectUtils::isUserDeviceTypeAllowed($projectCode)) {
            return redirect()->route('home');
        }

        $respondent->current_status = RespondentStatus::STARTED;
        $respondent->status_history = array_merge($respondent->status_history, [
            RespondentStatus::STARTED => date('Y-m-d H:i:s'),
        ]);
        $respondent->save();

        if ($projectEnvs['configs']['customized_survey_link'] ?? false) {
            return $this->customizedLink($respondent, $projectEnvs);
        }

        if ($respondent->is_test) {
            if (isset($projectEnvs['configs']['survey_link_test'])) {
                $link = Str::replaceFirst('{RID}', $respondent->uuid, $projectEnvs['configs']['survey_link_test']);
                Log::channel('survey_links')->info($link, [
                    'link_name'     => 'survey_link_test',
                    'respondent_id' => $respondent->id,
                ]);
                return redirect()->away($link);
            }

            Log::error("Test survey link for project ${projectCode} not set correctly");

            return redirect()->route('survey-redirect.closed', ['id' => $respondent->uuid]);
        }

        if (isset($projectEnvs['configs']['survey_link_live'])) {
            $link = Str::replaceFirst('{RID}', $respondent->uuid, $projectEnvs['configs']['survey_link_live']);
            Log::channel('survey_links')->info($link, [
                'link_name'     => 'survey_link_live',
                'respondent_id' => $respondent->id,
            ]);
            return redirect()->away($link);
        }

        Log::error("Live survey link for project ${projectCode} not set correctly");

        return redirect()->route('survey-redirect.closed', ['id' => $respondent->uuid]);
    }

    private function customizedLink(Respondent $respondent, ?array $configs): RedirectResponse
    {
        $locale = strtolower($respondent->person->language_code);
        $link = null;

        if ($locale === 'fr') {
            $link = Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_fr']);
        }

        if ($locale === 'pt') {
            $link = Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_pt']);
        }

        if ($locale === 'en') {
            $link = Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_en']);
        }

        if ($link) {
            return redirect()->away($link);
        }

        return redirect()->route('home');

//        if (isset($configs['configs']['survey_link_live_2']) && $this->sendToInfluential((array)$respondent->target_hits, $respondent->project_code)) {
//            return redirect()->away(Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_2']));
//        }
//
//        if (isset($configs['configs']['survey_link_live_1']) && $this->sendToBbcUser((array)$respondent->target_hits, $respondent->project_code)) {
//            return redirect()->away(Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_1']));
//        }
//
//        return redirect()->away(Str::replaceFirst('{RID}', $respondent->uuid, $configs['configs']['survey_link_live_0']));

    }

    private function sendToInfluential(array $hits, string $projectCode): bool
    {
        if ($projectCode !== 'tsr_003_ng') {
            return false;
        }

        $highIncomeTargetId = Target::query()
            ->where('project_code', $projectCode)
            ->where('status', TargetStatus::OPEN)
            ->where('criteria', 'monthly_household_income_level')
            ->where('value', 'HIGH')
            ->value('id');

        if ( ! in_array($highIncomeTargetId, $hits, false)) {
            return false;
        }

        $highIncomeCount = \App\TargetTrack::query()
            ->where('project_code', $projectCode)
            ->where('reference->monthly_household_income_level', $highIncomeTargetId)
            ->value('count');

        return $highIncomeCount < 300;
    }

    private function sendToBbcUser(array $hits, string $projectCode): bool
    {
        $bbcUserTargetId = Target::query()
            ->where('project_code', $projectCode)
            ->where('status', TargetStatus::OPEN)
            ->where('criteria', 'bbc_users')
            ->where('value', 'YES')
            ->value('id');

        if ( ! in_array($bbcUserTargetId, $hits, false)) {
            return false;
        }

        $bcUserCount = \App\TargetTrack::query()
            ->where('project_code', $projectCode)
            ->where('reference->bbc_users', $bbcUserTargetId)
            ->value('count');

        return $bcUserCount < 100;
    }

    /**
     * Set available data points.
     *
     * @param int $personId
     */
    private function setDataPoints(int $personId)
    {
        // Set device type
        if ($deviceType = $this->getDeviceTypeViaBrowserData()) {
            DataPoint::saveDatapoint($personId, $deviceType, 1, 'BROWSER');
        }

        // Set GEO IP data
        $ipAddress = last(request()->getClientIps());
        if ( ! empty($ipAddress) && $geoIpDataPoints = GeoIPData::lookupForSingle($ipAddress)) {
            foreach ($geoIpDataPoints as $dataPointAttribute => $dataPointValue) {
                DataPoint::saveDatapoint($personId, $dataPointAttribute, $dataPointValue, 'GEO_IP');
            }
        }
    }

    /**
     * @return string|null
     */
    private function getDeviceTypeViaBrowserData(): ?string
    {
        $agent = new Agent();
        $deviceType = null;
        if ($agent->isMobile()) {
            $deviceType = DataPointAttribute::MOBILE;
        } elseif ($agent->isTablet()) {
            $deviceType = DataPointAttribute::TABLET;
        } elseif ($agent->isDesktop()) {
            $deviceType = DataPointAttribute::DESKTOP;
        }

        return $deviceType;
    }
}

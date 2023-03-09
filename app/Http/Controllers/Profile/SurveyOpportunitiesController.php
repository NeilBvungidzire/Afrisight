<?php

namespace App\Http\Controllers\Profile;

use App\Cint\Facades\Cint;
use App\Constants\InvitationType;
use App\Constants\RespondentInvitationStatus;
use App\Constants\RespondentStatus;
use App\Constants\TargetStatus;
use App\Country;
use App\Libraries\Project\ProjectUtils;
use App\Person;
use App\Respondent;
use App\Target;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class SurveyOpportunitiesController extends BaseController {

    public function index() {
        try {
            $person = cache()->remember('PERSON_BY_USER_ID_' . auth()->user()->id, now()->addHour(),
                static function () {
                    return auth()->user()->person;
                });
        } catch (Exception $e) {
            return redirect()->back();
        }

        $this->setDataPoints($person->id);

        $profileComplete = $person->minimal_profile_data_is_available;

        // Own surveys.
        $ownSurveyOpportunities = [];
        if ($profileComplete) {
            $ownSurveyOpportunities = $this->getOwnSurveyOpportunities($person);
        }

        // Cint surveys
        $cintSurveyOpportunities = [];
        if (count($ownSurveyOpportunities) === 0 && $profileComplete) {
            $cintSurveyOpportunities = Cint::initialize($person)->getSurveyOpportunities();
        }

        return view('profile.survey-opportunities.index', compact(
            'cintSurveyOpportunities', 'profileComplete', 'ownSurveyOpportunities'
        ));
    }

    private function getOwnSurveyOpportunities(Person $person): array {
        $surveys = [];

        $projects = ProjectUtils::getConfigs();
        foreach ($projects as $projectCode => $project) {
            if ( ! $project['live']) {
                unset($projects[$projectCode]);
                continue;
            }

            if ( ! isset($project['enabled_via_web_app'])) {
                unset($projects[$projectCode]);
                continue;
            }

            if ( ! $project['enabled_via_web_app']) {
                unset($projects[$projectCode]);
                continue;
            }

            if ($survey = $this->checkForProject($person, $projectCode, $project['targets'])) {
                $surveys[] = $survey;
            }
        }

        return $surveys;
    }

    private function checkForProject(Person $person, string $projectCode, array $projectTargets): ?array {
        $targetsToCheck = ['country', 'gender', 'age_range'];
        $projectTargetCriteria = array_intersect($targetsToCheck, array_keys($projectTargets));

        // Check if is within the audience target group.
        try {
            $countriesCodeById = cache()->remember('COUNTRIES_CODE_BY_ID', now()->addDay(), function () {
                return Country::all()->pluck('iso_alpha_2', 'id');
            });
        } catch (Exception $exception) {
            return null;
        }

        // Country
        $personCountryId = $person->country_id ?: null;
        if (in_array('country', $projectTargetCriteria)) {
            if ( ! isset($countriesCodeById[$personCountryId])) {
                return null;
            }

            if ( ! in_array($countriesCodeById[$personCountryId], $projectTargets['country'])) {
                return null;
            }
        }

        // Language restriction
        $languageRestrictions = ProjectUtils::getLanguageRestrictions($projectCode);
        if ( ! empty($languageRestrictions) && ! in_array($person->language_code, $languageRestrictions, true)) {
            return null;
        }

        // Gender
        $personGenderCode = $person->gender_code ?: null;
        if (in_array('gender', $projectTargetCriteria)) {
            if ( ! in_array($personGenderCode, $projectTargets['gender'], true)) {
                return null;
            }
        }

        // Age range
        $personDateOfBirth = $person->date_of_birth ?: null;
        if (in_array('age_range', $projectTargetCriteria)) {
            if (empty($personDateOfBirth)) {
                return null;
            }

            $isInRange = false;
            $ageRanges = $projectTargets['age_range'];
            foreach ($ageRanges as $ageRange) {
                [$minAge, $maxAge] = explode('-', $ageRange);

                if ($this->isBetweenAge($personDateOfBirth, $minAge, $maxAge)) {
                    $isInRange = true;
                    break;
                }
            }

            if ( ! $isInRange) {
                return null;
            }
        }

        // Check if participant already participated in a project, which will exclude the same person participate in
        // this project.
        $excludeProjects = ProjectUtils::getConfigs($projectCode)['configs']['exclude_respondents_from_projects'] ?? null;
        if ( ! empty($excludeProjects)) {
            $statusesToIgnore = Arr::except(RespondentStatus::getConstants(), [
                RespondentStatus::INVITED,
                RespondentStatus::REMINDED,
                RespondentStatus::ENROLLING,
                RespondentStatus::TARGET_UNSUITABLE,
                RespondentStatus::QUOTA_FULL,
                RespondentStatus::CLOSED,
            ]);
            $alreadyParticipated = Respondent::query()
                ->where('person_id', $person->id)
                ->whereIn('project_code', $excludeProjects)
                ->whereIn('current_status', $statusesToIgnore)
                ->exists();

            if ($alreadyParticipated) {
                return null;
            }
        }

        $incentivePackage = ProjectUtils::getIncentivePackage($projectCode);
        if (empty($incentivePackage)) {
            return null;
        }

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $incentivePackage)) {
            $surveys = $this->generateInvitation($respondent, $incentivePackage);
        }

        return $surveys;
    }

    private function generateInvitation(Respondent $respondent, array $batchInputs): array {
        $invitation = $respondent->invitations()->create([
            'type'   => InvitationType::APP,
            'status' => RespondentInvitationStatus::DISPLAYED,
        ]);

        return array_merge([
            'url' => URL::route('invitation.land', $invitation->uuid),
        ], $batchInputs);
    }

    /**
     * @param  Person  $person
     * @param  string  $projectCode
     * @param  array  $respondentMetaData
     *
     * @return Respondent|null
     */
    private function getRespondent(Person $person, string $projectCode, array $respondentMetaData): ?Respondent {
        // Check if this person already participated in this project.
        $participationAsRespondents = $person->respondent()
            ->where('project_code', '=', $projectCode)
            ->get();

        $respondent = null;

        // Person already participated in this project.
        if ($participationAsRespondents->count()) {
            foreach ($participationAsRespondents as $asRespondent) {
                switch ($asRespondent->current_status) {

                    // Check if person already finished this project.
                    case RespondentStatus::COMPLETED:
                    case RespondentStatus::DISQUALIFIED:
                        // Don't need this respondent, because it already finished the project.
                        break;

                    case RespondentStatus::SELECTED:
                    case RespondentStatus::RESELECTED:
                    case RespondentStatus::INVITED:
                    case RespondentStatus::REMINDED:
                    case RespondentStatus::ENROLLING:
                    case RespondentStatus::TARGET_SUITABLE:
                    case RespondentStatus::STARTED:
                        $respondent = $asRespondent;
                        break;

                    default:
                        break;
                }
            }
        } else {
            $respondent = $person->respondent()->create([
                'project_code'     => $projectCode,
                'current_status'   => RespondentStatus::SELECTED,
                'status_history'   => [RespondentStatus::SELECTED => date('Y-m-d H:i:s')],
                'meta_data'        => $respondentMetaData,
                'incentive_amount' => $respondentMetaData['usd_amount'] ?? 0,
            ]);
        }

        return $respondent;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForIpsos014Project(Person $person): array {
        // Check if is within the audience target group.
        $projectCode = 'ipsos_014';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::getConfigs($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;
        switch ($person->gender_code) {
            case 'm':
                $gender = "MALE";
                break;
            case 'w':
                $gender = "FEMALE";
                break;
            default:
                $gender = null;
        }

        // Country
        $requiredCountryId = 38;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        // Age range
        if ( ! $this->isBetweenAge($dateOfBirth, 18, 44)) {
            return [];
        }

        // Gender
        if ( ! in_array($gender, ['MALE', 'FEMALE', null])) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForIpsos011Project(Person $person): array {
        // Check if is within the audience target group.
        $projectCode = 'ipsos_011';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 34;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        if ( ! $this->isBetweenAge($dateOfBirth, 18, 90)) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForToluna001Project(Person $person): array {
        // Check if is within the audience target group.
        $projectCode = 'toluna_001';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 22;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        if ( ! $this->isBetweenAge($dateOfBirth, 18, 65)) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 2);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForAS001Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'as_001';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 51;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        if ( ! $this->isBetweenAge($dateOfBirth, 18, 90)) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForIpsos006Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'ipsos_006';
        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 51;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 16, 90)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForDynata004Wave4Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'dynata_004_wave_4';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 44;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 25, 49)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForDynata0061Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'dynata_006_1';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 25;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 25, 90)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForDynata0062Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'dynata_006_2';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 38;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 18, 90)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForDynata007Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'dynata_007';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 38;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        if ( ! $this->isBetweenAge($dateOfBirth, 18, 90)) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 2);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForPDI0031Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'pdi_003_1';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 22;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 18, 65)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForPDI0032Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'pdi_003_2';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 39;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 18, 65)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForMSI003Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'msi_003';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 19;
        if ($countryId !== $requiredCountryId) {
            return [];
        }

        if ( ! ($this->isBetweenAge($dateOfBirth, 15, 19)
            || $this->isBetweenAge($dateOfBirth, 40, 69)
        )) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 4);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForMSI004Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'msi_004';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 25;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 15, 69)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  Person  $person
     *
     * @return array
     */
    private function checkForMSI005Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'msi_005';

        if ( ! ProjectUtils::isLive($projectCode)) {
            return [];
        }

        if ( ! ProjectUtils::isLive($projectCode)['enabled_via_web_app']) {
            return [];
        }

        $countryId = (int) $person->country_id;
        $dateOfBirth = $person->date_of_birth;

        $requiredCountryId = 2;
        if ($countryId !== $requiredCountryId
            || ! $this->isBetweenAge($dateOfBirth, 16, 90)
        ) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 3);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    private function checkForDynata003Project(Person $person) {
        // Check if is within the audience target group.
        $projectCode = 'dynata_003';
        $projectTargets = Target::query()
            ->where('project_code', $projectCode)
            ->where('status', TargetStatus::OPEN)
            ->get();

        $personCountryId = (int) $person->country_id;
        $requiredCountryId = 44;
        if ($personCountryId !== $requiredCountryId) {
            return [];
        }

        // Check age ranges.
        $ageRanges = [];
        foreach ($projectTargets as $target) {
            if ($target['criteria'] === 'age_range') {
                $ageRanges[] = $target['value'];
            }
        }

        $personDateOfBirth = $person->date_of_birth;
        $withinAgeRange = false;
        foreach ($ageRanges as $ageRange) {
            $ranges = explode('-', $ageRange);
            if (count($ranges) !== 2) {
                return [];
            }

            if ($this->isBetweenAge($personDateOfBirth, $ranges[0], $ranges[1])) {
                $withinAgeRange = true;
            }
        }
        if ( ! $withinAgeRange) {
            return [];
        }

        $respondentMetaData = ProjectUtils::getIncentivePackage($projectCode, 1);

        $surveys = [];
        if ($respondent = $this->getRespondent($person, $projectCode, $respondentMetaData)) {
            $surveys = $this->generateInvitation($respondent, $respondentMetaData);
        }

        return $surveys;
    }

    /**
     * @param  mixed  $dateOfBirth
     * @param  string|int  $ageMinimal
     * @param  string|int  $ageMaximal
     *
     * @return bool
     */
    private function isBetweenAge($dateOfBirth, $ageMinimal, $ageMaximal) {
        $minimalDateOfBirth = Carbon::now()->subYears($ageMaximal);
        $maximalDateOfBirth = Carbon::now()->subYears($ageMinimal);

        $personBirthDate = Carbon::make($dateOfBirth);

        return $personBirthDate->isBetween($minimalDateOfBirth, $maximalDateOfBirth);
    }
}

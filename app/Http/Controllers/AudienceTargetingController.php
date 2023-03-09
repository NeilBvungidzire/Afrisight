<?php

namespace App\Http\Controllers;

use App\Alert\Facades\Alert;
use App\Constants\DataPointAttribute;
use App\Constants\Gender;
use App\Constants\RespondentStatus;
use App\Constants\TargetStatus;
use App\Country;
use App\Libraries\GeoIPData;
use App\Libraries\Project\ProjectUtils;
use App\Person;
use App\QuestionAnswer;
use App\Respondent;
use App\Target;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AudienceTargetingController extends Controller {

    public function checkQualification(string $uuid) {

        $respondent = $this->getRespondent($uuid);
        if ( ! $respondent) {
            return redirect()->route('home');
        }

        if (in_array($respondent->current_status, [
            RespondentStatus::TARGET_UNSUITABLE,
            RespondentStatus::CLOSED,
            RespondentStatus::COMPLETED,
            RespondentStatus::DISQUALIFIED,
            RespondentStatus::QUOTA_FULL,
            RespondentStatus::SCREEN_OUT,
        ])) {
            return redirect()->route('home');
        }

        $respondent->update([
            'current_status' => RespondentStatus::ENROLLING,
            'status_history' => array_merge($respondent->status_history, [
                RespondentStatus::ENROLLING => date('Y-m-d H:i:s'),
            ]),
        ]);

        // Try to find person based on respondent.
        if ( ! $person = $respondent->person) {
            return redirect()->route('home');
        }

        // Determine which targets are hit by this respondent.
        $targetHits = $this->getTargetHits($respondent->project_code, $person);

        $requiredTargetHits = $this->getRequiredTargetHits($respondent->project_code);
        if (empty($requiredTargetHits)) {
            return redirect()->route('home');
        }

        // Save hits into respondent record for history.
        $targetIds = array_keys($targetHits);
        $newRespondentStatus = (count($targetIds) === $requiredTargetHits || $respondent->is_test)
            ? RespondentStatus::TARGET_SUITABLE
            : RespondentStatus::TARGET_UNSUITABLE;

        $respondent->update([
            'target_hits'    => $targetIds,
            'current_status' => $newRespondentStatus,
            'status_history' => array_merge((array) $respondent->status_history, [
                $newRespondentStatus => date('Y-m-d H:i:s'),
            ]),
        ]);

        // Check if target track quota is not reached.
        if ($newRespondentStatus === RespondentStatus::TARGET_SUITABLE) {
            $this->checkQuotaQuota($targetIds, $respondent);
        }

        return redirect()->route('invitation.entry', ['uuid' => $uuid]);
    }

    /**
     * @param  string  $uuid
     *
     * @return RedirectResponse|View
     */
    public function questionnaire(string $uuid) {

        $respondent = $this->getRespondent($uuid);
        if ( ! $respondent) {
            return redirect()->route('home');
        }

        if (in_array($respondent->current_status, [
            RespondentStatus::TARGET_UNSUITABLE,
            RespondentStatus::CLOSED,
            RespondentStatus::COMPLETED,
            RespondentStatus::DISQUALIFIED,
            RespondentStatus::QUOTA_FULL,
            RespondentStatus::SCREEN_OUT,
        ])) {
            return redirect()->route('home');
        }

        $questionIds = ProjectUtils::getConfigs($respondent->project_code)['configs']['qualification_question_ids'] ?? null;
        if (empty($questionIds)) {
            return redirect()->route('home');
        }
        $questions = $this->getQuestions($questionIds);

        $respondent->update([
            'current_status' => RespondentStatus::ENROLLING,
            'status_history' => array_merge($respondent->status_history, [
                RespondentStatus::ENROLLING => date('Y-m-d H:i:s'),
            ]),
        ]);

        if ( ! ProjectUtils::isUserDeviceTypeAllowed($respondent->project_code)) {
            $allowedDevices = ProjectUtils::getAllowedDeviceTypes($respondent->project_code) ?? [];
            $list = [];
            foreach ($allowedDevices as $allowedDevice) {
                $list[] = __("screening.device_restriction.${allowedDevice}");
            }
            Alert::makeDanger(__('screening.device_restriction.alert', ['devices' => implode(', ', $list)]));
        }

        return view('audience_targeting.questionnaire', compact('questions', 'uuid'));
    }

    /**
     * @param  string  $uuid
     *
     * @return RedirectResponse
     */
    public function processAnswer(string $uuid) {

        // Check if member already participated and has an end result (positive/negative).
        $respondent = $this->getRespondent($uuid);
        if ($respondent && in_array($respondent->current_status, [
                RespondentStatus::TARGET_UNSUITABLE,
                RespondentStatus::CLOSED,
                RespondentStatus::COMPLETED,
                RespondentStatus::DISQUALIFIED,
                RespondentStatus::QUOTA_FULL,
                RespondentStatus::SCREEN_OUT,
            ])
        ) {
            return redirect()->route('home');
        }

        $questionIds = ProjectUtils::getConfigs($respondent->project_code)['configs']['qualification_question_ids'] ?? null;
        if (empty($questionIds)) {
            return redirect()->route('home');
        }
        $questions = $this->getQuestions($questionIds);

        // Validate passed data.
        $data = [];
        foreach (request()->input() as $code => $value) {
            if ($code === '_token') {
                continue;
            }

            $questionId = decrypt($code);
            if (isset($questions[$questionId]['options'])) {
                try {
                    if (is_array($value)) {
                        foreach ($value as $index => $item) {
                            $value[$index] = decrypt($item);
                        }
                    } else {
                        $value = decrypt($value);
                    }
                } catch (\Exception $exception) {
                    Log::error('Could not decrypt screening question during audience targeting.',
                        compact('code', 'value'));

                    return redirect()->route('home');
                }
            }
            $data[$questionId] = $value;
        }
        $highestQuestionId = max($questions->keys()->toArray());

        $validationRules = [];
        for ($i = 0; $i <= $highestQuestionId; $i++) {
            $validationRules[$i] = 'nullable'; // Is apparently checked if this element is present in this case.

            // Question exists, so needs validation rule.
            if ($question = $questions->get($i)) {
                if ($question['type'] === 'single_choice') {
                    $validationRules[$i] = [
                        'required',
                        'string',
                        Rule::in(data_get($questions, ($i . '.options.*.value'))),
                    ];
                }

                if ($question['type'] === 'multiple_choice') {
                    $validationRules[$i] = [
                        'required',
                        'array',
                    ];
                }
            }
        }

        Validator::make($data, $validationRules)->validate();

        // Try to find person based on respondent.
        if ( ! $person = $respondent->person) {
            return redirect()->route('home');
        }

        // Save respondent answers, if not already exists.
        $answers = [];
        foreach ($data as $questionId => $answer) {
            $answers[$questionId] = QuestionAnswer::updateOrCreate([
                'person_id'   => $person->id,
                'question_id' => $questionId,
            ], [
                'person_id'   => $person->id,
                'question_id' => $questionId,
                'answer'      => is_array($answer) ? implode('|', $answer) : $answer,
            ]);
        }

        $requiredTargetHits = $this->getRequiredTargetHits($respondent->project_code);
        if (empty($requiredTargetHits)) {
            return redirect()->route('home');
        }

        // Determine which targets are hit by this respondent.
        $targetHits = $this->getTargetHits($respondent->project_code, $person, $answers);

        // Save hits into respondent record for history.
        $targetIds = array_keys($targetHits);
        $newRespondentStatus = (count($targetIds) === $requiredTargetHits || $respondent->is_test)
            ? RespondentStatus::TARGET_SUITABLE
            : RespondentStatus::TARGET_UNSUITABLE;

        $respondent->update([
            'target_hits'    => $targetIds,
            'current_status' => $newRespondentStatus,
            'status_history' => array_merge((array) $respondent->status_history, [
                $newRespondentStatus => date('Y-m-d H:i:s'),
            ]),
        ]);

        // Check if target track quota is not reached.
        if ($newRespondentStatus === RespondentStatus::TARGET_SUITABLE) {
            $this->checkQuotaQuota($targetIds, $respondent);
        }

        return redirect()->route('invitation.entry', ['uuid' => $uuid]);
    }

    /**
     * @param  string  $projectCode
     * @param  Person  $person
     * @param  array|null  $answers
     *
     * @return array
     */
    private function getTargetHits(string $projectCode, Person $person, array $answers = null) {

        $hits = [];

        $targetsNeeded = ProjectUtils::getConfigs($projectCode)['targets'] ?? null;
        if ( ! $targetsNeeded) {
            return $hits;
        }
        $targetsNeeded = array_keys($targetsNeeded);

        // Check country.
        if (in_array('country', $targetsNeeded, true)) {

            // First check if already existing datapoint.
            $countryCode = DB::table('data_points')
                ->where('person_id', $person->id)
                ->where('attribute', DataPointAttribute::COUNTRY_CODE)
                ->pluck('value')
                ->first();

            // In case datapoint was not set, check by IP address.
            if ( ! $countryCode) {
                $countryCode = GeoIPData::lookupForSingle(last(request()->getClientIps()))[DataPointAttribute::COUNTRY_CODE] ?? null;
            }

            // In case country code couldn't be retrieved by IP address, use the country selected by respondent.
            if ( ! $countryCode) {
                $countryCode = Country::getCountryIso2Code($person->country_id);
            }

            $target = null;
            if ($countryCode) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'country')
                    ->where('value', $countryCode)
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Check gender.
        if (in_array('gender', $targetsNeeded)) {
            $genderCode = $person->gender_code ?? Gender::UNDEFINED;
            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'gender')
                ->where('value', 'like', '%' . $genderCode . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Check age.
        if (in_array('age_range', $targetsNeeded)
            && isset($person->date_of_birth)
            && $dateOfBirth = Carbon::make($person->date_of_birth)
        ) {
            $age = $dateOfBirth->age;
            Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'age_range')
                ->get()
                ->each(function ($target) use ($age, &$hits) {

                    $ranges = $this->explodeAgeRange($target->value);

                    if (($age >= $ranges['min']) && ($age <= $ranges['max'])) {
                        $hits[$target->id] = $target;
                    }
                });
        }

        // Check city.
        if (in_array('city', $targetsNeeded) && (isset($answers[20])
                || isset($answers[21])
                || isset($answers[40])
                || isset($answers[50])
                || isset($answers[51])
                || isset($answers[54])
                || isset($answers[55])
                || isset($answers[56])
                || isset($answers[57])
                || isset($answers[67])
                || isset($answers[70])
                || isset($answers[71])
                || isset($answers[76])
                || isset($answers[77])
                || isset($answers[80])
                || isset($answers[81])
                || isset($answers[82])
                || isset($answers[87])
                || isset($answers[88])
                || isset($answers[90])
                || isset($answers[91])
                || isset($answers[92])
                || isset($answers[93])
                || isset($answers[96])
                || isset($answers[102])
                || isset($answers[104])
                || isset($answers[106])
                || isset($answers[108])
                || isset($answers[110])
                || isset($answers[120])
                || isset($answers[128])
                || isset($answers[130])
                || isset($answers[131])
                || isset($answers[132])
                || isset($answers[136])
            )) {
            if (isset($answers[20])) {
                $searchValue = $answers[20]->answer;
            }
            if (isset($answers[21])) {
                $searchValue = $answers[21]->answer;
            }
            if (isset($answers[40])) {
                $searchValue = $answers[40]->answer;
            }
            if (isset($answers[50])) {
                $searchValue = $answers[50]->answer;
            }
            if (isset($answers[51])) {
                $searchValue = $answers[51]->answer;
            }
            if (isset($answers[54])) {
                $searchValue = $answers[54]->answer;
            }
            if (isset($answers[55])) {
                $searchValue = $answers[55]->answer;
            }
            if (isset($answers[56])) {
                $searchValue = $answers[56]->answer;
            }
            if (isset($answers[57])) {
                $searchValue = $answers[57]->answer;
            }
            if (isset($answers[67])) {
                $searchValue = $answers[67]->answer;
            }
            if (isset($answers[70])) {
                $searchValue = $answers[70]->answer;
            }
            if (isset($answers[71])) {
                $searchValue = $answers[71]->answer;
            }
            if (isset($answers[76])) {
                $searchValue = $answers[76]->answer;
            }
            if (isset($answers[77])) {
                $searchValue = $answers[77]->answer;
            }
            if (isset($answers[80])) {
                $searchValue = $answers[80]->answer;
            }
            if (isset($answers[81])) {
                $searchValue = $answers[81]->answer;
            }
            if (isset($answers[82])) {
                $searchValue = $answers[82]->answer;
            }
            if (isset($answers[87])) {
                $searchValue = $answers[87]->answer;
            }
            if (isset($answers[88])) {
                $searchValue = $answers[88]->answer;
            }
            if (isset($answers[90])) {
                $searchValue = $answers[90]->answer;
            }
            if (isset($answers[91])) {
                $searchValue = $answers[91]->answer;
            }
            if (isset($answers[92])) {
                $searchValue = $answers[92]->answer;
            }
            if (isset($answers[93])) {
                $searchValue = $answers[93]->answer;
            }
            if (isset($answers[96])) {
                $searchValue = $answers[96]->answer;
            }
            if (isset($answers[102])) {
                $searchValue = $answers[102]->answer;
            }
            if (isset($answers[104])) {
                $searchValue = $answers[104]->answer;
            }
            if (isset($answers[106])) {
                $searchValue = $answers[106]->answer;
            }
            if (isset($answers[108])) {
                $searchValue = $answers[108]->answer;
            }
            if (isset($answers[110])) {
                $searchValue = $answers[110]->answer;
            }
            if (isset($answers[120])) {
                $searchValue = $answers[120]->answer;
            }
            if (isset($answers[128])) {
                $searchValue = $answers[128]->answer;
            }
            if (isset($answers[130])) {
                $searchValue = $answers[130]->answer;
            }
            if (isset($answers[131])) {
                $searchValue = $answers[131]->answer;
            }
            if (isset($answers[132])) {
                $searchValue = $answers[132]->answer;
            }
            if (isset($answers[136])) {
                $searchValue = $answers[136]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'city')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Check state.
        if (in_array('state', $targetsNeeded) && (isset($answers[9])
                || isset($answers[10])
                || isset($answers[11])
                || isset($answers[12])
                || isset($answers[16])
                || isset($answers[19])
                || isset($answers[25])
                || isset($answers[26])
                || isset($answers[29])
                || isset($answers[52])
                || isset($answers[66])
                || isset($answers[68])
                || isset($answers[89])
                || isset($answers[95])
                || isset($answers[97])
                || isset($answers[98])
                || isset($answers[99])
                || isset($answers[122])
                || isset($answers[133])
            )) {
            if (isset($answers[9])) {
                $searchValue = $answers[9]->answer;
            }
            if (isset($answers[10])) {
                $searchValue = $answers[10]->answer;
            }
            if (isset($answers[11])) {
                $searchValue = $answers[11]->answer;
            }
            if (isset($answers[12])) {
                $searchValue = $answers[12]->answer;
            }
            if (isset($answers[16])) {
                $searchValue = $answers[16]->answer;
            }
            if (isset($answers[19])) {
                $searchValue = $answers[19]->answer;
            }
            if (isset($answers[25])) {
                $searchValue = $answers[25]->answer;
            }
            if (isset($answers[26])) {
                $searchValue = $answers[26]->answer;
            }
            if (isset($answers[29])) {
                $searchValue = $answers[29]->answer;
            }
            if (isset($answers[52])) {
                $searchValue = $answers[52]->answer;
            }
            if (isset($answers[66])) {
                $searchValue = $answers[66]->answer;
            }
            if (isset($answers[68])) {
                $searchValue = $answers[68]->answer;
            }
            if (isset($answers[89])) {
                $searchValue = $answers[89]->answer;
            }
            if (isset($answers[95])) {
                $searchValue = $answers[95]->answer;
            }
            if (isset($answers[97])) {
                $searchValue = $answers[97]->answer;
            }
            if (isset($answers[98])) {
                $searchValue = $answers[98]->answer;
            }
            if (isset($answers[99])) {
                $searchValue = $answers[99]->answer;
            }
            if (isset($answers[122])) {
                $searchValue = $answers[122]->answer;
            }
            if (isset($answers[133])) {
                $searchValue = $answers[133]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'state')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Geographic region code based on subdivision (state, county, district, etc.) code.
        if (in_array('subdivision_geo_region', $targetsNeeded) && (isset($answers[9])
                || isset($answers[11])
                || isset($answers[12])
                || isset($answers[19])
                || isset($answers[25])
                || isset($answers[68])
                || isset($answers[89])
            )) {
            $countryCode = (new Country)->getCountryCode($person->country_id);
            $subdivisionCode = null;
            if (isset($answers[9])) {
                $subdivisionCode = $answers[9]->answer;
            }
            if (isset($answers[11])) {
                $subdivisionCode = $answers[11]->answer;
            }
            if (isset($answers[12])) {
                $subdivisionCode = $answers[12]->answer;
            }
            if (isset($answers[19])) {
                $subdivisionCode = $answers[19]->answer;
            }
            if (isset($answers[25])) {
                $subdivisionCode = $answers[25]->answer;
            }
            if (isset($answers[68])) {
                $subdivisionCode = $answers[68]->answer;
            }
            if (isset($answers[89])) {
                $subdivisionCode = $answers[89]->answer;
            }

            $searchValue = $this->getGeoRegionSubdivisionBySubdivision($countryCode, $subdivisionCode);

            $target = null;
            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'subdivision_geo_region')
                    ->where('value', $searchValue)
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // SEC level by household income.
        if (in_array('sec', $targetsNeeded) && (isset($answers[14]))) {
            $householdIncome = $answers[14]->answer;
            $searchValue = $this->getSECLevelByHouseholdIncome('MA', $householdIncome);

            $target = null;
            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'sec')
                    ->where('value', $searchValue)
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // BBC channel consumers
        if (in_array('bbc_users', $targetsNeeded) && (isset($answers[60]))) {
            $answer = $answers[60]->answer;
            $searchValue = Str::contains($answer, 'YES') ? 'YES' : 'NO';

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'bbc_users')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        if (in_array('sec_1', $targetsNeeded) && (isset($answers[41])
                && isset($answers[42])
                && isset($answers[43])
                && isset($answers[44])
                && isset($answers[45])
                && isset($answers[46])
                && isset($answers[47])
                && isset($answers[48])
                && isset($answers[49])
            )) {
            $ownershipCategory = explode('|', $answers[41]->answer);
            $cookingCategory = explode('|', $answers[42]->answer);
            $toiletTypeCategory = $answers[43]->answer;
            $mainWaterSourceCategory = $answers[44]->answer;
            $educationCategory = $answers[45]->answer;
            $residentialAreaCategory = $answers[46]->answer;
            $typeHouseCategory = $answers[47]->answer;
            $occupationCategory = $answers[48]->answer;
            $lifestyleCategory = explode('|', $answers[49]->answer);

            $attributes = array_merge($ownershipCategory, $cookingCategory, $lifestyleCategory);
            $attributes[] = $toiletTypeCategory;
            $attributes[] = $mainWaterSourceCategory;
            $attributes[] = $educationCategory;
            $attributes[] = $residentialAreaCategory;
            $attributes[] = $typeHouseCategory;
            $attributes[] = $occupationCategory;

            $searchValue = $this->getSECLevelsRange($attributes);

            $target = null;
            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'sec_1')
                    ->where('value', 'like', '%' . $searchValue . '%')
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // LSM
        if (in_array('lsm', $targetsNeeded) && (isset($answers[38]))) {
            if ($level = $this->getLSMLevel(explode('|', $answers[38]->answer))) {
                $searchValue = 'LSM' . $level;
                if ($searchValue) {
                    $target = Target::query()
                        ->where('project_code', $projectCode)
                        ->where('status', TargetStatus::OPEN)
                        ->where('criteria', 'lsm')
                        ->where('value', 'like', '%' . $searchValue . '%')
                        ->first();
                }

                if ($target) {
                    $hits[$target->id] = $target;
                }
            }
        }

        // Custom geographic region.
        if (in_array('region', $targetsNeeded) && (isset($answers[13])
                || isset($answers[18])
                || isset($answers[32])
                || isset($answers[34])
                || isset($answers[36])
                || isset($answers[126])
            )) {
            if (isset($answers[13])) {
                $searchValue = $answers[13]->answer;
            }
            if (isset($answers[18])) {
                $searchValue = $answers[18]->answer;
            }
            if (isset($answers[32])) {
                $searchValue = $answers[32]->answer;
            }
            if (isset($answers[34])) {
                $searchValue = $answers[34]->answer;
            }
            if (isset($answers[36])) {
                $searchValue = $answers[36]->answer;
            }
            if (isset($answers[126])) {
                $searchValue = $answers[126]->answer;
            }

            $target = null;
            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'region')
                    ->where('value', 'like', '%' . $searchValue . '%')
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Check ethnicity.
        if (in_array('ethnicity', $targetsNeeded) && isset($answers[5])) {
            $searchValue = $answers[5]->answer;
            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'ethnicity')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Check ethnic groups.
        if (in_array('ethnic_group', $targetsNeeded) && isset($answers[107])) {
            $searchValue = $answers[107]->answer;

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'ethnic_group')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Personal income
        if (in_array('personal_income', $targetsNeeded) && isset($answers[2])) {
            $searchValue = $answers[2]->answer;

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'personal_income')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Personal monthly income
        if (in_array('monthly_personal_income_range', $targetsNeeded) && (
                isset($answers[2])
                || isset($answers[6])
                || isset($answers[7])
                || isset($answers[8])
                || isset($answers[15])
                || isset($answers[35])
                || isset($answers[37])
                || isset($answers[111])
                || isset($answers[112])
                || isset($answers[113])
                || isset($answers[114])
                || isset($answers[115])
            )
        ) {
            if (isset($answers[2])) {
                $searchValue = $answers[2]->answer;
            } elseif (isset($answers[6])) {
                $searchValue = $answers[6]->answer;
            } elseif (isset($answers[7])) {
                $searchValue = $answers[7]->answer;
            } elseif (isset($answers[8])) {
                $searchValue = $answers[8]->answer;
            } elseif (isset($answers[15])) {
                $searchValue = $answers[15]->answer;
            } elseif (isset($answers[35])) {
                $searchValue = $answers[35]->answer;
            } elseif (isset($answers[37])) {
                $searchValue = $answers[37]->answer;
            } elseif (isset($answers[111])) {
                $searchValue = $answers[111]->answer;
            } elseif (isset($answers[112])) {
                $searchValue = $answers[112]->answer;
            } elseif (isset($answers[113])) {
                $searchValue = $answers[113]->answer;
            } elseif (isset($answers[114])) {
                $searchValue = $answers[114]->answer;
            } elseif (isset($answers[115])) {
                $searchValue = $answers[115]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'monthly_personal_income_range')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Household monthly income
        if (in_array('monthly_household_income_range', $targetsNeeded) && (isset($answers[17])
                || isset($answers[27])
                || isset($answers[28])
                || isset($answers[30])
                || isset($answers[31])
                || isset($answers[33])
                || isset($answers[53])
                || isset($answers[61])
                || isset($answers[62])
                || isset($answers[65])
                || isset($answers[83])
                || isset($answers[100])
                || isset($answers[109])
                || isset($answers[123])
                || isset($answers[134])
            )) {
            if (isset($answers[17])) {
                $searchValue = $answers[17]->answer;
            }
            if (isset($answers[27])) {
                $searchValue = $answers[27]->answer;
            }
            if (isset($answers[28])) {
                $searchValue = $answers[28]->answer;
            }
            if (isset($answers[30])) {
                $searchValue = $answers[30]->answer;
            }
            if (isset($answers[31])) {
                $searchValue = $answers[31]->answer;
            }
            if (isset($answers[33])) {
                $searchValue = $answers[33]->answer;
            }
            if (isset($answers[53])) {
                $searchValue = $answers[53]->answer;
            }
            if (isset($answers[61])) {
                $searchValue = $answers[61]->answer;
            }
            if (isset($answers[62])) {
                $searchValue = $answers[62]->answer;
            }
            if (isset($answers[65])) {
                $searchValue = $answers[65]->answer;
            }
            if (isset($answers[83])) {
                $searchValue = $answers[83]->answer;
            }
            if (isset($answers[100])) {
                $searchValue = $answers[100]->answer;
            }
            if (isset($answers[109])) {
                $searchValue = $answers[109]->answer;
            }
            if (isset($answers[123])) {
                $searchValue = $answers[123]->answer;
            }
            if (isset($answers[134])) {
                $searchValue = $answers[134]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'monthly_household_income_range')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Household annual income
        if (in_array('annual_household_income_range', $targetsNeeded) && (isset($answers[101])
                || isset($answers[103])
                || isset($answers[127])
                || isset($answers[129])
            )) {
            if (isset($answers[101])) {
                $searchValue = $answers[101]->answer;
            }
            if (isset($answers[103])) {
                $searchValue = $answers[103]->answer;
            }
            if (isset($answers[127])) {
                $searchValue = $answers[127]->answer;
            }
            if (isset($answers[129])) {
                $searchValue = $answers[129]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'annual_household_income_range')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Monthly household income levels
        if (in_array('monthly_household_income_level',
                $targetsNeeded) && (isset($answers[59]) || isset($answers[61]))) {
            $answer = null;
            $searchValue = null;
            $list = [];

            if (isset($answers[59])) {
                $answer = $answers[59]->answer;
                $list = [
                    'LOW'    => [
                        'NO_INCOME',
                        'KES50.000-',
                        'KES50.000-KES74.999',
                        'KES75.000-KES99.999',
                        'KES100.000-KES124.999',
                    ],
                    'MEDIUM' => [
                        'KES125.000-KES149.999',
                        'KES150.000-KES174.999',
                        'KES175.000-KES199.999',
                        'KES200.000-KES249.999',
                        'KES250.000-KES299.999',
                    ],
                    'HIGH'   => [
                        'KES300.000-KES399.999',
                        'KES400.000-KES499.999',
                        'KES500.000+',
                    ],
                ];
            }

            if (isset($answers[61])) {
                $answer = $answers[61]->answer;
                $list = [
                    'LOW'    => [
                        'NO_INCOME',
                        'NGN50,000-',
                        'NGN50,000-NGN99,999',
                        'NGN100,000-NGN199,999',
                        'NGN200,000-NGN299,999',
                    ],
                    'MEDIUM' => [
                        'NGN300,000-NGN399,999',
                        'NGN400,000-NGN499,999',
                        'NGN500,000-NGN599,999',
                        'NGN600,000-NGN699,999',
                        'NGN700,000-NGN799,999',
                        'NGN800,000-NGN899,999',
                    ],
                    'HIGH'   => [
                        'NGN900,000-NGN999,999',
                        'NGN1,000,000-NGN1,249,999',
                        'NGN1,125,000-NGN1,499,999',
                        'NGN1,500,000-NGN1,749,999',
                        'NGN1,750,000-NGN1,999,999',
                        'NGN2,000,000-NGN2,499,999',
                        'NGN2,500,000-NGN2,999,999',
                        'NGN3,000,000-NGN3,499,999',
                        'NGN3,500,000-NGN3,999,999',
                        'NGN4,000,000-NGN4,999,999',
                        'NGN5,000,000+',
                    ],
                ];
            }

            foreach ($list as $level => $incomeRanges) {
                if (in_array($answer, $incomeRanges)) {
                    $searchValue = $level;
                    break;
                }
            }

            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'monthly_household_income_level')
                    ->where('value', 'like', '%' . $searchValue . '%')
                    ->first();

                if ($target) {
                    $hits[$target->id] = $target;
                }
            }
        }

        // Check earn salary.
        if (in_array('earn_salary', $targetsNeeded) && isset($answers[1])) {
            $searchValue = $answers[1]->answer;
            $earnSalaryValue = in_array($searchValue, [
                'full-time_work',
                'part-time_contract_work',
                'self-employed_own_a_business',
            ]) ? 'YES' : 'NO';

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'earn_salary')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Employment status
        if (in_array('employment_status', $targetsNeeded) && (isset($answers[1]) || isset($answers[74]))) {
            if (isset($answers[1])) {
                $searchValue = $answers[1]->answer;
            }
            if (isset($answers[74])) {
                $searchValue = $answers[74]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'employment_status')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Job position
        if (in_array('job_position', $targetsNeeded) && isset($answers[75])) {
            $searchValue = $answers[75]->answer;

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'job_position')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Education level
        if (in_array('education_level', $targetsNeeded) && isset($answers[22])) {
            $searchValue = $answers[22]->answer;

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'education_level')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Intercontinental flight.
        if (in_array('intercontinental_flights', $targetsNeeded) && (isset($answers[23]) && isset($answers[24]))) {
            $airMode = Str::contains($answers[23]->answer, 'air');
            $intercontinentFlight = $answers[24]->answer === 'yes';

            $searchValue = ($airMode && $intercontinentFlight) ? 'yes' : 'no';

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'intercontinental_flights')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Biased markets
        if (in_array('biased_markets', $targetsNeeded) && isset($answers[39])) {
            $searchValues = explode('|', $answers[39]->answer);
            $searchArray = [];
            foreach ($searchValues as $searchValue) {
                $searchArray[] = ['value', 'like', '%' . $searchValue . '%'];
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'biased_markets')
                ->where($searchArray)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Biased markets (v2)
        if (in_array('biased_markets', $targetsNeeded) && (isset($answers[58])
                || isset($answers[79])
                || isset($answers[85])
            )) {
            if (isset($answers[58])) {
                $searchValue = $answers[58]->answer;
            }
            if (isset($answers[79])) {
                $searchValue = $answers[79]->answer;
            }
            if (isset($answers[85])) {
                $searchValue = $answers[85]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'biased_markets')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Parents
        if (in_array('parent', $targetsNeeded) && isset($answers[63])) {
            $searchValue = $answers[63]->answer;

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'parent')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Parents with children between
        if (in_array('parent_children_age_range', $targetsNeeded) && (isset($answers[64])
                || isset($answers[69])
                || isset($answers[78])
                || isset($answers[86])
                || isset($answers[118])
            )) {
            if (isset($answers[64])) {
                $searchValue = $answers[64]->answer;
            }
            if (isset($answers[69])) {
                $searchValue = $answers[69]->answer;
            }
            if (isset($answers[78])) {
                $searchValue = $answers[78]->answer;
            }
            if (isset($answers[86])) {
                $searchValue = $answers[86]->answer;
            }
            if (isset($answers[118])) {
                $searchValue = $answers[118]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'parent_children_age_range')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Children guardian
        if (in_array('children_guardian', $targetsNeeded) && (isset($answers[105]))) {
            if (isset($answers[105])) {
                $searchValue = $answers[105]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'children_guardian')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Business / engineering / IT student
        if (in_array('business_engineering_it_student', $targetsNeeded)
            && (isset($answers[72], $answers[73]))
        ) {
            $isStudent = $answers[72]->answer === 'student';
            $isStudyingTargetStudy = in_array($answers[73]->answer, [
                'addis_ababa_aait',
                'addis_ababa_uni',
                'addis_ababa_science_technology',
                'bahir_dar_uni',
                'jimma_uni',
                'dire_dawa_uni',
            ], true);

            $searchValue = ($isStudent && $isStudyingTargetStudy) ? 'yes' : 'no';

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'business_engineering_it_student')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Online shopper at some online stores in the last 3 months
        if (in_array('online_shopper_last_3_months_at', $targetsNeeded)
            && (isset($answers[84]))
        ) {
            if (isset($answers[84])) {
                $searchValue = $answers[84]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'online_shopper_last_3_months_at')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Access to webcam
        if (in_array('webcam_access', $targetsNeeded)
            && (isset($answers[94]))
        ) {
            if (isset($answers[94])) {
                $searchValue = $answers[94]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'webcam_access')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Study situation
        if (in_array('current_study_situation', $targetsNeeded)
            && (isset($answers[116]))
        ) {
            if (isset($answers[116])) {
                $searchValue = $answers[116]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'current_study_situation')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Study country
        if (in_array('study_country', $targetsNeeded)
            && (isset($answers[117]))
        ) {
            if (isset($answers[117])) {
                $searchValue = $answers[117]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'study_country')
                ->where('value', $searchValue)
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // TV package
        if (in_array('tv_package', $targetsNeeded)
            && (isset($answers[119]))
        ) {
            if (isset($answers[119])) {
                $searchValue = $answers[119]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'tv_package')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Alcohol consumption
        if (in_array('alcohol_consumption', $targetsNeeded) && (isset($answers[121]))) {
            if (isset($answers[121])) {
                $searchValue = $answers[121]->answer;
            }

            $target = Target::query()
                ->where('project_code', $projectCode)
                ->where('status', TargetStatus::OPEN)
                ->where('criteria', 'alcohol_consumption')
                ->where('value', 'like', '%' . $searchValue . '%')
                ->first();

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Teabag types
        if (in_array('teabag_type', $targetsNeeded) && (isset($answers[124]) && isset($answers[125]))) {
            $teabagConsumer = $answers[124]->answer;
            $searchValue = null;
            if ($teabagConsumer === 'yes') {
                $searchValue = $answers[125]->answer;
            }

            $target = null;
            if ($searchValue) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'teabag_type')
                    ->where('value', 'like', '%' . $searchValue . '%')
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        // Messaging apps and usage
        if (in_array('messaging_apps_usage', $targetsNeeded) && (isset($answers[135]))) {
            $searchValues = explode('|', $answers[135]->answer);
            $searchArray = [];
            foreach ($searchValues as $searchValue) {
                $searchArray[] = ['value', 'like', '%' . $searchValue . '%'];
            }

            $target = null;
            if ($searchValues) {
                $target = Target::query()
                    ->where('project_code', $projectCode)
                    ->where('status', TargetStatus::OPEN)
                    ->where('criteria', 'messaging_apps_usage')
                    ->where(function ($query) use ($searchArray) {
                        foreach ($searchArray as $search) {
                            $query->orWhere($search[0], $search[1], $search[2]);
                        }
                    })
                    ->first();
            }

            if ($target) {
                $hits[$target->id] = $target;
            }
        }

        return $hits;
    }

    /**
     * @param  string  $range
     *
     * @return int[]
     */
    private function explodeAgeRange(string $range) {

        if (strpos($range, '+') !== false) {
            $explodedRange = explode('+', $range);
            $ranges = [
                'min' => (int) $explodedRange[0],
                'max' => 100,
            ];
        } elseif (strpos($range, '-') !== false) {
            $explodedRange = explode('-', $range);

            $ranges = [
                'min' => (int) $explodedRange[0],
                'max' => (int) $explodedRange[1],
            ];
        } else {
            $ranges = [
                'min' => (int) $range,
                'max' => (int) $range,
            ];
        }

        return $ranges;
    }

    /**
     * @param  array  $ids
     *
     * @return Collection
     */
    private function getQuestions(array $ids) {

        $questionsAvailable = [
            1   => [
                'id'       => 1,
                'type'     => 'single_choice',
                'question' => 'Which of the following best describes your employment status?',
                'options'  => $this->getEmploymentStatus(),
            ],
            2   => [
                'id'       => 2,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => $this->getPersonalIncomePerMonth('UG'),
            ],
            3   => [
                'id'       => 3,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => $this->getHouseholdIncomePerMonth('ZA'),
            ],
            5   => [
                'id'       => 5,
                'type'     => 'single_choice',
                'question' => 'How would you define yourself as it relates to your ethnicity?',
                'options'  => $this->getEthnicity(),
            ],
            6   => [
                'id'       => 6,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => $this->getPersonalIncomePerMonth('GH'),
            ],
            7   => [
                'id'       => 7,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => $this->getPersonalIncomePerMonth('RW'),
            ],
            8   => [
                'id'       => 8,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => $this->getPersonalIncomePerMonth('ZA'),
            ],
            9   => [
                'id'       => 9,
                'type'     => 'single_choice',
                'question' => 'Which county territory do you currently live in?',
                'options'  => $this->getSubdivisions('KE'),
            ],
            10  => [
                'id'       => 10,
                'type'     => 'single_choice',
                'question' => 'Which province territory do you currently live in?',
                'options'  => $this->getSubdivisions('ZA'),
            ],
            11  => [
                'id'       => 11,
                'type'     => 'single_choice',
                'question' => 'Which state/capital territory do you currently live in?',
                'options'  => $this->getSubdivisions('NG'),
            ],
            12  => [
                'id'       => 12,
                'type'     => 'single_choice',
                'question' => 'Which district/city territory do you currently live in?',
                'options'  => $this->getSubdivisions('UG'),
            ],
            13  => [
                'id'       => 13,
                'type'     => 'single_choice',
                'question' => 'Dans quelle région vivez-vous actuellement?', // Language: French
                'options'  => $this->getCities('MA'),
            ],
            14  => [
                'id'       => 14,
                'type'     => 'single_choice',
                // Language: French
                'question' => 'Quel choix dans le tableau suivant décrit au mieux le revenu total mensuel gagné par tous les membres de votre foyer?',
                'info'     => [
                    'Pensez à toutes les sources de revenu: salaires, pensions, revenus de locations immobilières, transferts de l’étranger, etc.',
                    'Cette question est posée uniquement à des fins de recherche.',
                ],
                'options'  => $this->getHouseholdIncomePerMonth('MA'),
            ],
            15  => [
                'id'       => 15,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => $this->getPersonalIncomePerMonth('ZW'),
            ],
            16  => [
                'id'       => 16,
                'type'     => 'single_choice',
                'question' => 'Which region territory do you currently live in?',
                'options'  => $this->getSubdivisions('GH'),
            ],
            17  => [
                'id'       => 17,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => $this->getHouseholdIncomePerMonth('NG'),
            ],
            18  => [
                'id'       => 18,
                'type'     => 'single_choice',
                'question' => 'Which region do you currently live in?',
                'options'  => $this->getCities('NG'),
            ],
            19  => [
                'id'       => 19,
                'type'     => 'single_choice',
                'question' => 'Which district/city territory do you currently live in?',
                'options'  => $this->getSubdivisions('NG'),
            ],
            // South Africa
            20  => [
                'id'       => 20,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Pretoria', 'label' => 'Pretoria'],
                    ['id' => 2, 'value' => 'Cape Town', 'label' => 'Cape Town'],
                    ['id' => 3, 'value' => 'Johannesburg', 'label' => 'Johannesburg'],
                    ['id' => 4, 'value' => 'Port Elizabeth', 'label' => 'Port Elizabeth'],
                    ['id' => 5, 'value' => 'East London', 'label' => 'East London'],
                    ['id' => 6, 'value' => 'Durban', 'label' => 'Durban'],
                    ['id' => 7, 'value' => 'Bloemfontein', 'label' => 'Bloemfontein'],
                    ['id' => 8, 'value' => 'Vaal Triangle/ South Rand', 'label' => 'Vaal Triangle/ South Rand'],
                    ['id' => 9, 'value' => 'East Rand', 'label' => 'East Rand'],
                    ['id' => 10, 'value' => 'West Rand', 'label' => 'West Rand'],
                    ['id' => 11, 'value' => 'Polokwane', 'label' => 'Polokwane'],
                    ['id' => 12, 'value' => 'Mbombela (Nelspruit)', 'label' => 'Mbombela (Nelspruit)'],
                    ['id' => 13, 'value' => 'Rustenburg', 'label' => 'Rustenburg'],
                    ['id' => 14, 'value' => 'Pietermaritzburg', 'label' => 'Pietermaritzburg'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // South Africa
            21  => [
                'id'       => 21,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Cape Town', 'label' => 'Cape Town'],
                    ['id' => 2, 'value' => 'Durban', 'label' => 'Durban'],
                    ['id' => 3, 'value' => 'East London', 'label' => 'East London'],
                    ['id' => 4, 'value' => 'Port Elizabeth', 'label' => 'Port Elizabeth'],
                    ['id' => 5, 'value' => 'East Rand', 'label' => 'East Rand'],
                    ['id' => 6, 'value' => 'Pretoria', 'label' => 'Pretoria'],
                    ['id' => 7, 'value' => 'Vaal Triangle/ South Rand', 'label' => 'Vaal Triangle/ South Rand'],
                    ['id' => 8, 'value' => 'West Rand', 'label' => 'West Rand'],
                    ['id' => 9, 'value' => 'Polokwane', 'label' => 'Polokwane'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            22  => [
                'id'       => 22,
                'type'     => 'single_choice',
                'question' => 'What is your level of education?',
                'options'  => $this->getEducationLevel(),
            ],
            // Transport modes
            23  => [
                'id'       => 23,
                'type'     => 'multiple_choice',
                'question' => 'Which of the following transport options did you use in the past 3 years?',
                'options'  => [
                    ['id' => 0, 'value' => 'none', 'label' => 'None'],
                    ['id' => 1, 'value' => 'road', 'label' => 'Car, bus or motorcycle.'],
                    ['id' => 2, 'value' => 'air', 'label' => 'Flight'],
                    ['id' => 3, 'value' => 'rail', 'label' => 'Train'],
                    ['id' => 4, 'value' => 'water', 'label' => 'Boat/Ship'],
                ],
            ],
            24  => [
                'id'       => 24,
                'type'     => 'single_choice',
                'question' => 'Did you have a flight outside of Africa in the past 3 years?',
                'options'  => [
                    ['id' => 0, 'value' => 'no', 'label' => 'No'],
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                ],
            ],
            25  => [
                'id'       => 25,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.districts.question'),
                'options'  => $this->getSubdivisions('CI'),
            ],
            26  => [
                'id'       => 26,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.regions.question'),
                'options'  => $this->getSubdivisions('SN'),
            ],
            // Nigeria
            27  => [
                'id'       => 27,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'NGN50.000-', 'label' => 'Less then NGN 50.000'],
                    ['id' => 3, 'value' => 'NGN50.000-NGN100.000', 'label' => 'Between NGN 50.000 and NGN 100.000'],
                    ['id' => 4, 'value' => 'NGN100.000-NGN800.000', 'label' => 'Between NGN 100.000 and NGN 800.000'],
                    ['id' => 5, 'value' => 'NGN800.000+', 'label' => 'More than NGN 800.000'],
                ],
            ],
            // Tanzania
            28  => [
                'id'       => 28,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'TZS105.000-', 'label' => 'Less then TSh 105.000'],
                    ['id' => 3, 'value' => 'TZS105.000-TZS210.000', 'label' => 'Between TSh 105.001 and TSh 210.000'],
                    ['id' => 4, 'value' => 'TZS210.000-TZS315.000', 'label' => 'Between TSh 210.000 and TSh 315.000'],
                    ['id' => 5, 'value' => 'TZS315.000-TZS420.000', 'label' => 'Between TSh 315.000 and TSh 420.000'],
                    ['id' => 6, 'value' => 'TZS420.000-TZS525.000', 'label' => 'Between TSh 420.000 and TSh 525.000'],
                    ['id' => 7, 'value' => 'TZS525.000-TZS630.000', 'label' => 'Between TSh 525.000 and TSh 630.000'],
                    ['id' => 8, 'value' => 'NGN630.000+', 'label' => 'More than TSh 630.000'],
                ],
            ],
            29  => [
                'id'       => 29,
                'type'     => 'single_choice',
                'question' => 'Which region territory do you currently live in?',
                'options'  => $this->getSubdivisions('TZ'),
            ],
            30  => [
                'id'       => 30,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'KES5.000-', 'label' => 'Less then Ksh 5.000'],
                    ['id' => 3, 'value' => 'KES5.000-KES10.000', 'label' => 'Between Ksh 5.000 and Ksh 10.000'],
                    ['id' => 4, 'value' => 'KES10.000-KES15.000', 'label' => 'Between Ksh 10.000 and Ksh 15.000'],
                    ['id' => 5, 'value' => 'KES15.000-KES20.000', 'label' => 'Between Ksh 15.000 and Ksh 20.000'],
                    ['id' => 6, 'value' => 'KES20.000-KES25.000', 'label' => 'Between Ksh 20.000 and Ksh 25.000'],
                    ['id' => 7, 'value' => 'KES25.000-KES30.000', 'label' => 'Between Ksh 25.000 and Ksh 30.000'],
                    ['id' => 8, 'value' => 'KES30.000+', 'label' => 'More than Ksh 30.000'],
                ],
            ],
            // Nigeria
            31  => [
                'id'       => 31,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'NGN40.000-', 'label' => 'Less then NGN 40.000'],
                    ['id' => 3, 'value' => 'NGN40.000-NGN200.000', 'label' => 'Between NGN 40.000 and NGN 200.000'],
                    ['id' => 4, 'value' => 'NGN200.000-NGN400.000', 'label' => 'Between NGN 200.000 and NGN 400.000'],
                    ['id' => 5, 'value' => 'NGN400.000+', 'label' => 'More than NGN 400.000'],
                ],
            ],
            // Morocco (old regions), French
            32  => [
                'id'       => 32,
                'type'     => 'single_choice',
                'question' => 'Dans quelle région territoire habitez-vous actuellement?',
                'options'  => [
                    ['id' => 1, 'value' => 'Fes-Boulemane', 'label' => 'Fès-Boulemane'],
                    ['id' => 2, 'value' => 'Grand Casablanca', 'label' => 'Grand Casablanca'],
                    ['id' => 3, 'value' => 'Marrakech-Tensift-Al-Haouz', 'label' => 'Marrakech-Tensift-Al-Haouz'],
                    ['id' => 4, 'value' => 'Meknes-Tafilalet', 'label' => 'Meknes-Tafilalet'],
                    ['id' => 5, 'value' => 'Rabat-Sale-Zemmour-Zaer', 'label' => 'Rabat-Salé-Zemmour-Zaer'],
                    ['id' => 6, 'value' => 'Tanger-Tetouan', 'label' => 'Tanger-Tetouan'],
                    ['id' => 0, 'value' => 'Other region', 'label' => 'Other region'],
                ],
            ],
            // Morocco, French
            33  => [
                'id'       => 33,
                'type'     => 'single_choice',
                'question' => 'Quel est le revenu mensuel actuel de votre ménage?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'Aucun revenu'],
                    ['id' => 2, 'value' => 'MAD2.000-', 'label' => 'Moins de 2.000 MAD'],
                    ['id' => 3, 'value' => 'MAD2.000-MAD6.000', 'label' => 'Entre 2.000 MAD et 6.000 MAD'],
                    ['id' => 4, 'value' => 'MAD6.000-MAD10.000', 'label' => 'Entre 6.000 MAD et 10.000 MAD'],
                    ['id' => 5, 'value' => 'MAD10.000-MAD30.000', 'label' => 'Entre 10.000 MAD et 30.000 MAD'],
                    ['id' => 6, 'value' => 'MAD30.000-MAD50.000', 'label' => 'Entre 30.000 MAD et 50.000 MAD'],
                    ['id' => 7, 'value' => 'MAD50.000+', 'label' => 'Plus de 50.000 MAD'],
                ],
            ],
            // Nigeria regions
            34  => [
                'id'       => 34,
                'type'     => 'single_choice',
                'question' => 'Which region do you currently live in?',
                'options'  => [
                    ['id' => 1, 'value' => 'Lagos', 'label' => 'Lagos'],
                    ['id' => 0, 'value' => 'Other region', 'label' => 'Other region'],
                ],
            ],
            // Nigeria personal monthly income
            35  => [
                'id'       => 35,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'NGN40.000-', 'label' => 'Less then NGN 40.000'],
                    ['id' => 3, 'value' => 'NGN40.000-NGN200.000', 'label' => 'Between NGN 40.000 and NGN 200.000'],
                    ['id' => 4, 'value' => 'NGN200.000-NGN400.000', 'label' => 'Between NGN 200.000 and NGN 400.000'],
                    ['id' => 4, 'value' => 'NGN400.000-NGN500.000', 'label' => 'Between NGN 400.000 and NGN 500.000'],
                    ['id' => 5, 'value' => 'NGN500.000+', 'label' => 'More than NGN 500.000'],
                ],
            ],
            // Kenya regions
            36  => [
                'id'       => 36,
                'type'     => 'single_choice',
                'question' => 'Which region do you currently live in?',
                'options'  => [
                    ['id' => 1, 'value' => 'Nairobi / Nairobi metro', 'label' => 'Nairobi / Nairobi metro'],
                    ['id' => 0, 'value' => 'Other region', 'label' => 'Other region'],
                ],
            ],
            // Kenya personal monthly income
            37  => [
                'id'       => 37,
                'type'     => 'single_choice',
                'question' => 'What is your current personal income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'KES5.000-', 'label' => 'Less then Ksh 5.000'],
                    ['id' => 3, 'value' => 'KES5.000-KES10.000', 'label' => 'Between Ksh 5.000 and Ksh 10.000'],
                    ['id' => 4, 'value' => 'KES10.000-KES15.000', 'label' => 'Between Ksh 10.000 and Ksh 15.000'],
                    ['id' => 5, 'value' => 'KES15.000-KES20.000', 'label' => 'Between Ksh 15.000 and Ksh 20.000'],
                    ['id' => 6, 'value' => 'KES20.000-KES25.000', 'label' => 'Between Ksh 20.000 and Ksh 25.000'],
                    ['id' => 7, 'value' => 'KES25.000-KES30.000', 'label' => 'Between Ksh 25.000 and Ksh 30.000'],
                    ['id' => 8, 'value' => 'KES30.000-KES65.000', 'label' => 'Between Ksh 30.000 and Ksh 65.000'],
                    ['id' => 9, 'value' => 'KES65.000-KES100.000', 'label' => 'Between Ksh 65.000 and Ksh 100.000'],
                    ['id' => 10, 'value' => 'KES100.000+', 'label' => 'More than Ksh 100.000'],
                ],
            ],
            // South Africa, LSM
            38  => [
                'id'       => 38,
                'type'     => 'multiple_choice',
                'question' => 'Please select the statements that apply to your household situation.',
                'options'  => [
                    ['id' => 1, 'value' => 1, 'label' => 'Hot running water from a geyser'],
                    ['id' => 2, 'value' => 2, 'label' => 'Computer - Desktop/Laptop'],
                    ['id' => 3, 'value' => 3, 'label' => 'Electric stove'],
                    [
                        'id'    => 4, 'value' => 4,
                        'label' => 'No domestic workers or household helpers in household (this includes live-in and part-time domestics and gardeners)',
                    ],
                    ['id' => 5, 'value' => 5, 'label' => '0 or 1 radio set in household'],
                    ['id' => 6, 'value' => 6, 'label' => 'Flush toilet in/outside house'],
                    ['id' => 7, 'value' => 7, 'label' => 'Motor vehicle in household'],
                    ['id' => 8, 'value' => 8, 'label' => 'Washing machine'],
                    ['id' => 9, 'value' => 9, 'label' => 'Refrigerator or combined fridge/freezer'],
                    ['id' => 10, 'value' => 10, 'label' => 'Vacuum cleaner/floor polisher'],
                    ['id' => 11, 'value' => 11, 'label' => 'Pay TV (M-Net/DStv/TopTV) subscription'],
                    ['id' => 12, 'value' => 12, 'label' => 'Dishwashing machine'],
                    ['id' => 13, 'value' => 13, 'label' => '3 or more cellphones in household'],
                    ['id' => 14, 'value' => 14, 'label' => '2 cellphones in household'],
                    ['id' => 15, 'value' => 15, 'label' => 'Home security service'],
                    ['id' => 16, 'value' => 16, 'label' => 'Deep freezer - free standing'],
                    ['id' => 17, 'value' => 17, 'label' => 'Microwave oven'],
                    ['id' => 18, 'value' => 18, 'label' => 'Rural rest (excl. W Cape & Gauteng rural)'],
                    ['id' => 19, 'value' => 19, 'label' => 'House/cluster house/town house'],
                    ['id' => 20, 'value' => 20, 'label' => 'DVD player/Blu Ray Player'],
                    ['id' => 21, 'value' => 21, 'label' => 'Tumble dryer'],
                    ['id' => 22, 'value' => 22, 'label' => 'Home theatre system'],
                    ['id' => 23, 'value' => 23, 'label' => 'Home telephone (excl. cellphone)'],
                    ['id' => 24, 'value' => 24, 'label' => 'Swimming Pool'],
                    ['id' => 25, 'value' => 25, 'label' => 'Tap water in house/on plot'],
                    ['id' => 26, 'value' => 26, 'label' => 'Built-in kitchen sink'],
                    ['id' => 27, 'value' => 27, 'label' => 'TV set'],
                    ['id' => 28, 'value' => 28, 'label' => 'Air conditioner (excl. fans)'],
                    ['id' => 29, 'value' => 29, 'label' => 'Metropolitan dweller (250.000+)'],
                ],
            ],
            // Screen on biased respondents based on markets.
            39  => [
                'id'       => 39,
                'type'     => 'multiple_choice',
                'question' => __('screening.biased_markets.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'marketing', 'label' => __('screening.biased_markets.answer.marketing')],
                    [
                        'id'    => 2, 'value' => 'surveys_and_market_research',
                        'label' => __('screening.biased_markets.answer.surveys_and_market_research'),
                    ],
                    [
                        'id'    => 3, 'value' => 'media_or_internet',
                        'label' => __('screening.biased_markets.answer.media_or_internet'),
                    ],
                    [
                        'id'    => 4, 'value' => 'advertising_agencies',
                        'label' => __('screening.biased_markets.answer.advertising_agencies'),
                    ],
                    [
                        'id'    => 5, 'value' => 'public_relation_agencies',
                        'label' => __('screening.biased_markets.answer.public_relation_agencies'),
                    ],
                    ['id' => 0, 'value' => 'none', 'label' => __('screening.biased_markets.answer.none')],
                ],
            ],
            // Nigeria cities
            40  => [
                'id'       => 40,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Lagos', 'label' => 'Lagos'],
                    ['id' => 2, 'value' => 'Kano', 'label' => 'Kano'],
                    ['id' => 3, 'value' => 'Abuja', 'label' => 'Abuja'],
                    ['id' => 4, 'value' => 'Ibadan', 'label' => 'Ibadan'],
                    ['id' => 5, 'value' => 'Enugu', 'label' => 'Enugu'],
                    ['id' => 6, 'value' => 'PHC', 'label' => 'PHC / Port Harcourt'],
                    ['id' => 7, 'value' => 'Kaduna', 'label' => 'Kaduna'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // START: SEC option 1
            // Category: Ownership
            41  => [
                'id'       => 41,
                'type'     => 'multiple_choice',
                'question' => __('screening.sec_option_1.question.ownership'),
                'options'  => [
                    ['id' => 1, 'value' => '1', 'label' => __('screening.sec_option_1.attributes.1.label')],
                    ['id' => 2, 'value' => '2', 'label' => __('screening.sec_option_1.attributes.2.label')],
                    ['id' => 3, 'value' => '3', 'label' => __('screening.sec_option_1.attributes.3.label')],
                    ['id' => 4, 'value' => '4', 'label' => __('screening.sec_option_1.attributes.4.label')],
                    ['id' => 5, 'value' => '5', 'label' => __('screening.sec_option_1.attributes.5.label')],
                    ['id' => 6, 'value' => '6', 'label' => __('screening.sec_option_1.attributes.6.label')],
                    ['id' => 7, 'value' => '7', 'label' => __('screening.sec_option_1.attributes.7.label')],
                    ['id' => 8, 'value' => '8', 'label' => __('screening.sec_option_1.attributes.8.label')],
                    ['id' => 9, 'value' => '9', 'label' => __('screening.sec_option_1.attributes.9.label')],
                    ['id' => 10, 'value' => '10', 'label' => __('screening.sec_option_1.attributes.10.label')],
                    ['id' => 11, 'value' => '11', 'label' => __('screening.sec_option_1.attributes.11.label')],
                    ['id' => 12, 'value' => '12', 'label' => __('screening.sec_option_1.attributes.12.label')],
                    ['id' => 13, 'value' => '13', 'label' => __('screening.sec_option_1.attributes.13.label')],
                    ['id' => 14, 'value' => '14', 'label' => __('screening.sec_option_1.attributes.14.label')],
                    ['id' => 15, 'value' => '15', 'label' => __('screening.sec_option_1.attributes.15.label')],
                    ['id' => 16, 'value' => '16', 'label' => __('screening.sec_option_1.attributes.16.label')],
                    ['id' => 17, 'value' => '17', 'label' => __('screening.sec_option_1.attributes.17.label')],
                    ['id' => 18, 'value' => '18', 'label' => __('screening.sec_option_1.attributes.18.label')],
                    ['id' => 19, 'value' => '19', 'label' => __('screening.sec_option_1.attributes.19.label')],
                    ['id' => 20, 'value' => '20', 'label' => __('screening.sec_option_1.attributes.20.label')],
                ],
            ],
            // Category: Cooking
            42  => [
                'id'       => 42,
                'type'     => 'multiple_choice',
                'question' => __('screening.sec_option_1.question.cooking'),
                'options'  => [
                    ['id' => 1, 'value' => '21', 'label' => __('screening.sec_option_1.attributes.21.label')],
                    ['id' => 2, 'value' => '22', 'label' => __('screening.sec_option_1.attributes.22.label')],
                    ['id' => 3, 'value' => '23', 'label' => __('screening.sec_option_1.attributes.23.label')],
                ],
            ],
            // Category: Toilet Type
            43  => [
                'id'       => 43,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.toilet_type'),
                'options'  => [
                    ['id' => 1, 'value' => '24', 'label' => __('screening.sec_option_1.attributes.24.label')],
                    ['id' => 2, 'value' => '25', 'label' => __('screening.sec_option_1.attributes.25.label')],
                    ['id' => 3, 'value' => '26', 'label' => __('screening.sec_option_1.attributes.26.label')],
                ],
            ],
            // Category: Main Water Source
            44  => [
                'id'       => 44,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.main_water_source'),
                'options'  => [
                    ['id' => 1, 'value' => '27', 'label' => __('screening.sec_option_1.attributes.27.label')],
                    ['id' => 2, 'value' => '28', 'label' => __('screening.sec_option_1.attributes.28.label')],
                    ['id' => 3, 'value' => '29', 'label' => __('screening.sec_option_1.attributes.29.label')],
                    ['id' => 4, 'value' => '30', 'label' => __('screening.sec_option_1.attributes.30.label')],
                    ['id' => 5, 'value' => '31', 'label' => __('screening.sec_option_1.attributes.31.label')],
                ],
            ],
            // Category: Education of household head
            45  => [
                'id'       => 45,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.education_household_head'),
                'options'  => [
                    ['id' => 1, 'value' => '32', 'label' => __('screening.sec_option_1.attributes.32.label')],
                    ['id' => 2, 'value' => '33', 'label' => __('screening.sec_option_1.attributes.33.label')],
                    ['id' => 3, 'value' => '34', 'label' => __('screening.sec_option_1.attributes.34.label')],
                    ['id' => 4, 'value' => '35', 'label' => __('screening.sec_option_1.attributes.35.label')],
                    ['id' => 5, 'value' => '36', 'label' => __('screening.sec_option_1.attributes.36.label')],
                    ['id' => 6, 'value' => '37', 'label' => __('screening.sec_option_1.attributes.37.label')],
                    ['id' => 7, 'value' => '38', 'label' => __('screening.sec_option_1.attributes.38.label')],
                    ['id' => 8, 'value' => '39', 'label' => __('screening.sec_option_1.attributes.39.label')],
                    ['id' => 9, 'value' => '40', 'label' => __('screening.sec_option_1.attributes.40.label')],
                ],
            ],
            // Category: Residential Area
            46  => [
                'id'       => 46,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.residential_area'),
                'options'  => [
                    ['id' => 1, 'value' => '41', 'label' => __('screening.sec_option_1.attributes.41.label')],
                    ['id' => 2, 'value' => '42', 'label' => __('screening.sec_option_1.attributes.42.label')],
                    ['id' => 3, 'value' => '43', 'label' => __('screening.sec_option_1.attributes.43.label')],
                ],
            ],
            // Category: Type Of House
            47  => [
                'id'       => 47,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.type_house'),
                'options'  => [
                    ['id' => 1, 'value' => '44', 'label' => __('screening.sec_option_1.attributes.44.label')],
                    ['id' => 2, 'value' => '45', 'label' => __('screening.sec_option_1.attributes.45.label')],
                    ['id' => 3, 'value' => '46', 'label' => __('screening.sec_option_1.attributes.46.label')],
                    ['id' => 4, 'value' => '47', 'label' => __('screening.sec_option_1.attributes.47.label')],
                    ['id' => 5, 'value' => '48', 'label' => __('screening.sec_option_1.attributes.48.label')],
                    ['id' => 6, 'value' => '49', 'label' => __('screening.sec_option_1.attributes.49.label')],
                    ['id' => 7, 'value' => '50', 'label' => __('screening.sec_option_1.attributes.50.label')],
                ],
            ],
            // Category: Occupation
            48  => [
                'id'       => 48,
                'type'     => 'single_choice',
                'question' => __('screening.sec_option_1.question.occupation'),
                'options'  => [
                    ['id' => 1, 'value' => '51', 'label' => __('screening.sec_option_1.attributes.51.label')],
                    ['id' => 2, 'value' => '52', 'label' => __('screening.sec_option_1.attributes.52.label')],
                    ['id' => 3, 'value' => '53', 'label' => __('screening.sec_option_1.attributes.53.label')],
                    ['id' => 4, 'value' => '54', 'label' => __('screening.sec_option_1.attributes.54.label')],
                    ['id' => 5, 'value' => '55', 'label' => __('screening.sec_option_1.attributes.55.label')],
                    ['id' => 6, 'value' => '56', 'label' => __('screening.sec_option_1.attributes.56.label')],
                    ['id' => 7, 'value' => '57', 'label' => __('screening.sec_option_1.attributes.57.label')],
                    ['id' => 8, 'value' => '58', 'label' => __('screening.sec_option_1.attributes.58.label')],
                    ['id' => 9, 'value' => '59', 'label' => __('screening.sec_option_1.attributes.59.label')],
                ],
            ],
            // Category: Lifestyle
            49  => [
                'id'       => 49,
                'type'     => 'multiple_choice',
                'question' => __('screening.sec_option_1.question.lifestyle'),
                'options'  => [
                    ['id' => 1, 'value' => '60', 'label' => __('screening.sec_option_1.attributes.60.label')],
                    ['id' => 2, 'value' => '61', 'label' => __('screening.sec_option_1.attributes.61.label')],
                    ['id' => 3, 'value' => '62', 'label' => __('screening.sec_option_1.attributes.62.label')],
                    ['id' => 4, 'value' => '63', 'label' => __('screening.sec_option_1.attributes.63.label')],
                    ['id' => 5, 'value' => '64', 'label' => __('screening.sec_option_1.attributes.64.label')],
                    ['id' => 6, 'value' => '65', 'label' => __('screening.sec_option_1.attributes.65.label')],
                ],
            ],
            // END: SEC option 1
            // Kenya cities
            50  => [
                'id'       => 50,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Nairobi', 'label' => 'Nairobi'],
                    ['id' => 2, 'value' => 'Mombasa', 'label' => 'Mombasa'],
                    ['id' => 3, 'value' => 'Kisumu', 'label' => 'Kisumu'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Angola cities
            51  => [
                'id'       => 51,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Luanda', 'label' => 'Luanda'],
                    ['id' => 2, 'value' => 'Benguela', 'label' => 'Benguela'],
                    ['id' => 3, 'value' => 'Lobito', 'label' => 'Lobito'],
                    ['id' => 4, 'value' => 'Huambo', 'label' => 'Huambo'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            52  => [
                'id'       => 52,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.regions.question'),
                'options'  => $this->getSubdivisions('CM'),
            ],
            53  => [
                'id'       => 53,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'R0-R3.999', 'label' => 'ZAR 0 (No income) to ZAR 3.999'],
                    ['id' => 2, 'value' => 'R4.000-R4.999', 'label' => 'ZAR 4.000 to ZAR 4.999'],
                    ['id' => 3, 'value' => 'R5.000-R9.999', 'label' => 'ZAR 5.000 to ZAR 9.999'],
                    ['id' => 4, 'value' => 'R10.000-R19.999', 'label' => 'ZAR 10.000 to ZAR 19.999'],
                    ['id' => 5, 'value' => 'R20.000+', 'label' => 'ZAR 20.000 or more'],
                ],
            ],
            // Egypt cities
            54  => [
                'id'       => 54,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Greater Cairo', 'label' => 'Greater Cairo'],
                    ['id' => 2, 'value' => 'Alexandria', 'label' => 'Alexandria'],
                    ['id' => 3, 'value' => 'Tanta', 'label' => 'Tanta'],
                    ['id' => 4, 'value' => 'Mansoura', 'label' => 'Mansoura'],
                    ['id' => 5, 'value' => 'Menia', 'label' => 'Menia'],
                    ['id' => 6, 'value' => 'Assuit', 'label' => 'Assuit'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            55  => [
                'id'       => 55,
                'type'     => 'single_choice',
                'question' => __('screening.What region or city do you live in?'),
                'options'  => [
                    ['id' => 1, 'value' => 'Béni Mellal', 'label' => 'Béni Mellal'],
                    ['id' => 2, 'value' => 'Fès', 'label' => 'Fès'],
                    ['id' => 3, 'value' => 'Grand Casablanca', 'label' => 'Grand Casablanca'],
                    ['id' => 4, 'value' => 'Marrakech', 'label' => 'Marrakech'],
                    ['id' => 5, 'value' => 'Oujda', 'label' => 'Oujda'],
                    ['id' => 6, 'value' => 'Rabat-Salé', 'label' => 'Rabat-Salé'],
                    ['id' => 7, 'value' => 'Souss-Massa', 'label' => 'Souss-Massa'],
                    ['id' => 8, 'value' => 'Tanger', 'label' => 'Tanger'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.Other region / city')],
                ],
            ],
            // Kenya, cities
            56  => [
                'id'       => 56,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Nairobi', 'label' => 'Nairobi'],
                    ['id' => 2, 'value' => 'Eldoret', 'label' => 'Eldoret'],
                    ['id' => 3, 'value' => 'Kitengela', 'label' => 'Kitengela'],
                    ['id' => 4, 'value' => 'Kisumu', 'label' => 'Kisumu'],
                    ['id' => 5, 'value' => 'Mombasa', 'label' => 'Mombasa'],
                    ['id' => 6, 'value' => 'Nakuru', 'label' => 'Nakuru'],
                    ['id' => 7, 'value' => 'Ngong - Rongai - Karen', 'label' => 'Ngong - Rongai - Karen'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Ivory Coast, cities
            57  => [
                'id'       => 57,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Abidjan Nord', 'label' => 'Abidjan Nord'],
                    ['id' => 2, 'value' => 'Abidjan Sud', 'label' => 'Abidjan Sud'],
                    ['id' => 3, 'value' => 'Grand Bassam', 'label' => 'Grand Bassam'],
                    ['id' => 4, 'value' => 'Yamoussoukro', 'label' => 'Yamoussoukro'],
                    ['id' => 5, 'value' => 'Yopougon', 'label' => 'Yopougon'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Screen on biased respondents based on markets.
            58  => [
                'id'       => 58,
                'type'     => 'single_choice',
                'question' => 'Do you or any of your immediate household, work for any of the following types of companies?',
                'options'  => [
                    [
                        'id'    => 1, 'value' => 'yes',
                        'label' => 'Marketing or market research company/department within a company',
                    ],
                    ['id' => 2, 'value' => 'yes', 'label' => 'Advertising agency or department within a company'],
                    ['id' => 3, 'value' => 'yes', 'label' => 'Public relations agency or department within a company'],
                    ['id' => 4, 'value' => 'yes', 'label' => 'Web design agency or department within a company'],
                    ['id' => 5, 'value' => 'yes', 'label' => 'Pack design agency or department within a company'],
                    [
                        'id'    => 6, 'value' => 'yes',
                        'label' => 'Media (e.g. Newspaper/Magazine/TV/Radio) company or department within a company',
                    ],
                    [
                        'id'    => 7, 'value' => 'yes',
                        'label' => 'Manufacturer/Wholesaler/Retailer/Distributor/Promoter/Ambassador of beverage products or anyone involved in beverage events (alcohol or other)',
                    ],
                    ['id' => 8, 'value' => 'yes', 'label' => 'Retailers who sell alcohol or bottle stores'],
                    ['id' => 9, 'value' => 'yes', 'label' => 'Packaging company'],
                    ['id' => 10, 'value' => 'yes', 'label' => 'Event management/planning'],
                    ['id' => 11, 'value' => 'no', 'label' => 'Don\'t know'],
                    ['id' => 12, 'value' => 'no', 'label' => 'None of these'],
                ],
            ],
            // Kenya, Monthly household income
            59  => [
                'id'       => 59,
                'type'     => 'single_choice',
                'question' => 'What is your current combined monthly household income (before tax and other deductions)?',
                'options'  => [
                    ['id' => 1, 'value' => 'KES50.000-', 'label' => 'Less than 50,000 KES'],
                    ['id' => 2, 'value' => 'KES50.000-KES74.999', 'label' => 'Between 50,000 KES and 74,999 KES'],
                    ['id' => 3, 'value' => 'KES75.000-KES99.999', 'label' => 'Between 75,000 KES and 99,999 KES'],
                    ['id' => 4, 'value' => 'KES100.000-KES124.999', 'label' => 'Between 100,000 KES and 124,999 KES'],
                    ['id' => 5, 'value' => 'KES125.000-KES149.999', 'label' => 'Between 125,000 KES and 149,999 KES'],
                    ['id' => 6, 'value' => 'KES150.000-KES174.999', 'label' => 'Between 150,000 KES and 174,999 KES'],
                    ['id' => 7, 'value' => 'KES175.000-KES199.999', 'label' => 'Between 175,000 KES and 199,999 KES'],
                    ['id' => 8, 'value' => 'KES200.000-KES249.999', 'label' => 'Between 200,000 KES and 249,999 KES'],
                    ['id' => 9, 'value' => 'KES250.000-KES299.999', 'label' => 'Between 250,000 KES and 299,999 KES'],
                    ['id' => 10, 'value' => 'KES300.000-KES399.999', 'label' => 'Between 300,000 KES and 399,999 KES'],
                    ['id' => 11, 'value' => 'KES400.000-KES499.999', 'label' => 'Between 400,000 KES and 499,999 KES'],
                    ['id' => 12, 'value' => 'KES500.000+', 'label' => 'More than 500,000 KES'],
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ],
            ],
            60  => [
                'id'       => 60,
                'type'     => 'multiple_choice',
                'question' => 'Which of the following brands that provide news have you used in the past month (i.e. thinking back four weeks from today)?',
                'options'  => [
                    ['id' => 1, 'value' => 'YES', 'label' => 'BBC'],
                    ['id' => 2, 'value' => 'NO', 'label' => 'CNN'],
                    ['id' => 3, 'value' => 'NO', 'label' => 'Sky News'],
                    [
                        'id'    => 4, 'value' => 'NO',
                        'label' => 'China Global Television Network (CGTN) / China Radio International (CRI)',
                    ],
                    ['id' => 5, 'value' => 'NO', 'label' => 'Russia Today (RT)'],
                    ['id' => 6, 'value' => 'NO', 'label' => 'Al Jazeera'],
                    ['id' => 7, 'value' => 'NO', 'label' => 'YouTube'],
                    ['id' => 8, 'value' => 'NO', 'label' => 'Facebook'],
                    ['id' => 0, 'value' => 'NO', 'label' => 'None of these brands'],
                ],
            ],
            // Nigeria, Monthly household income
            61  => [
                'id'       => 61,
                'type'     => 'single_choice',
                'question' => 'What is your current combined monthly household income (before tax and other deductions)?',
                'options'  => [
                    ['id' => 1, 'value' => 'NGN50,000-', 'label' => 'Less than 50,000 NGN'],
                    ['id' => 2, 'value' => 'NGN50,000-NGN99,999', 'label' => 'Between 50,000 NGN and 99,999 NGN'],
                    ['id' => 3, 'value' => 'NGN100,000-NGN199,999', 'label' => 'Between 100,000 NGN and 199,999 NGN'],
                    ['id' => 4, 'value' => 'NGN200,000-NGN299,999', 'label' => 'Between 200,000 NGN and 299,999 NGN'],
                    ['id' => 5, 'value' => 'NGN300,000-NGN399,999', 'label' => 'Between 300,000 NGN and 399,999 NGN'],
                    ['id' => 6, 'value' => 'NGN400,000-NGN499,999', 'label' => 'Between 400,000 NGN and 499,999 NGN'],
                    ['id' => 7, 'value' => 'NGN500,000-NGN599,999', 'label' => 'Between 500,000 NGN and 599,999 NGN'],
                    ['id' => 8, 'value' => 'NGN600,000-NGN699,999', 'label' => 'Between 600,000 NGN and 699,999 NGN'],
                    ['id' => 9, 'value' => 'NGN700,000-NGN799,999', 'label' => 'Between 700,000 NGN and 799,999 NGN'],
                    ['id' => 10, 'value' => 'NGN800,000-NGN899,999', 'label' => 'Between 800,000 NGN and 899,999 NGN'],
                    ['id' => 11, 'value' => 'NGN900,000-NGN999,999', 'label' => 'Between 900,000 NGN and 999,999 NGN'],
                    [
                        'id'    => 12, 'value' => 'NGN1,000,000-NGN1,249,999',
                        'label' => 'Between 1,000,000 NGN and 1,249,999 NGN',
                    ],
                    [
                        'id'    => 13, 'value' => 'NGN1,125,000-NGN1,499,999',
                        'label' => 'Between 1,125,000 NGN and 1,499,999 NGN',
                    ],
                    [
                        'id'    => 14, 'value' => 'NGN1,500,000-NGN1,749,999',
                        'label' => 'Between 1,500,000 NGN and 1,749,999 NGN',
                    ],
                    [
                        'id'    => 15, 'value' => 'NGN1,750,000-NGN1,999,999',
                        'label' => 'Between 1,750,000 NGN and 1,999,999 NGN',
                    ],
                    [
                        'id'    => 16, 'value' => 'NGN2,000,000-NGN2,499,999',
                        'label' => 'Between 2,000,000 NGN and 2,499,999 NGN',
                    ],
                    [
                        'id'    => 17, 'value' => 'NGN2,500,000-NGN2,999,999',
                        'label' => 'Between 2,500,000 NGN and 2,999,999 NGN',
                    ],
                    [
                        'id'    => 18, 'value' => 'NGN3,000,000-NGN3,499,999',
                        'label' => 'Between 3,000,000 NGN and 3,499,999 NGN',
                    ],
                    [
                        'id'    => 19, 'value' => 'NGN3,500,000-NGN3,999,999',
                        'label' => 'Between 3,500,000 NGN and 3,999,999 NGN',
                    ],
                    [
                        'id'    => 20, 'value' => 'NGN4,000,000-NGN4,999,999',
                        'label' => 'Between 4,000,000 NGN and 4,999,999 NGN',
                    ],
                    ['id' => 21, 'value' => 'NGN5,000,000+', 'label' => 'More than 5,000,000 NGN'],
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ],
            ],
            62  => [
                'id'       => 62,
                'type'     => 'single_choice',
                'question' => 'What is your current combined monthly household income (before tax and other deductions)?',
                'info'     => [
                    'NB: household income includes all money earned by you and the person(s) who live(s) with you.',
                ],
                'options'  => [
                    ['id' => 1, 'value' => 'NGN50.000-', 'label' => 'Less than 50.000 NGN'],
                    ['id' => 2, 'value' => 'NGN50.000-NGN129.999', 'label' => 'Between 50.000 NGN and 129.999 NGN'],
                    ['id' => 3, 'value' => 'NGN130.000-NGN199.999', 'label' => 'Between 130.000 NGN and 199.999 NGN'],
                    ['id' => 4, 'value' => 'NGN200.000-NGN249.999', 'label' => 'Between 200.000 NGN and 249.999 NGN'],
                    ['id' => 5, 'value' => 'NGN250.000-NGN299.999', 'label' => 'Between 250.000 NGN and 299.999 NGN'],
                    ['id' => 6, 'value' => 'NGN300.000-NGN349.999', 'label' => 'Between 300.000 NGN and 349.999 NGN'],
                    ['id' => 7, 'value' => 'NGN350.000-NGN399.999', 'label' => 'Between 350.000 NGN and 399.999 NGN'],
                    ['id' => 8, 'value' => 'NGN400.000-NGN449.999', 'label' => 'Between 400.000 NGN and 449.999 NGN'],
                    ['id' => 9, 'value' => 'NGN450.000-NGN499.999', 'label' => 'Between 450.000 NGN and 499.999 NGN'],
                    ['id' => 10, 'value' => 'NGN500.000-NGN800.000', 'label' => 'Between 500.000 NGN and 800.000 NGN'],
                    ['id' => 11, 'value' => 'NGN800.000+', 'label' => 'More than 800.000 NGN'],
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ],
            ],
            63  => [
                'id'       => 63,
                'type'     => 'single_choice',
                'question' => 'Do you have children?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                    ['id' => 2, 'value' => 'no', 'label' => 'No'],
                ],
            ],
            64  => [
                'id'       => 64,
                'type'     => 'single_choice',
                'question' => 'Do you have children aged between 6 to 17 years old?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                    ['id' => 2, 'value' => 'no', 'label' => 'No'],
                ],
            ],
            // Nigeria, Monthly household income
            65  => [
                'id'       => 65,
                'type'     => 'single_choice',
                'question' => 'What is your current combined monthly household income (before tax and other deductions)?',
                'options'  => [
                    ['id' => 1, 'value' => 'NGN400,000-', 'label' => 'Less than 400,000 NGN'],
                    ['id' => 2, 'value' => 'NGN400,000-NGN749,999', 'label' => 'Between 400,000 NGN and 749,999 NGN'],
                    [
                        'id'    => 3, 'value' => 'NGN750,000-NGN1,249,999',
                        'label' => 'Between 750,000 NGN and 1,249,999 NGN',
                    ],
                    [
                        'id'    => 4, 'value' => 'NGN1,250,000-NGN1,749,999',
                        'label' => 'Between 1,250,000 NGN and 1,749,999 NGN',
                    ],
                    [
                        'id'    => 5, 'value' => 'NGN1,750,000-NGN2,749,999',
                        'label' => 'Between 1,750,00 NGN and 2,749,999 NGN',
                    ],
                    [
                        'id'    => 6, 'value' => 'NGN2,750,000-NGN3,499,999',
                        'label' => 'Between 2,750,000 NGN and 3,499,999 NGN',
                    ],
                    ['id' => 7, 'value' => 'NGN3,500,000+', 'label' => 'More than 3,500,000 NGN'],
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ],
            ],
            66  => [
                'id'       => 66,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.province_capital.question'),
                'options'  => $this->getSubdivisions('MZ'),
            ],
            // Nigeria cities
            67  => [
                'id'       => 67,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Lagos', 'label' => 'Lagos'],
                    ['id' => 2, 'value' => 'Kano', 'label' => 'Kano'],
                    ['id' => 3, 'value' => 'Ibadan', 'label' => 'Ibadan'],
                    ['id' => 4, 'value' => 'Kaduna', 'label' => 'Kaduna'],
                    ['id' => 5, 'value' => 'PHC', 'label' => 'PHC / Port Harcourt'],
                    ['id' => 6, 'value' => 'Benin City', 'label' => 'Benin City'],
                    ['id' => 7, 'value' => 'Maiduguri', 'label' => 'Maiduguri'],
                    ['id' => 8, 'value' => 'Zaria', 'label' => 'Zaria'],
                    ['id' => 9, 'value' => 'Aba', 'label' => 'Aba'],
                    ['id' => 0, 'value' => 'Other', 'label' => __('screening.city.other.label')],
                ],
            ],
            68  => [
                'id'       => 68,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.province_prefecture.question'),
                'options'  => $this->getSubdivisions('MA'),
            ],
            69  => [
                'id'       => 69,
                'type'     => 'single_choice',
                'question' => 'Do you have children aged between 14 to 17 years old?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                    ['id' => 2, 'value' => 'no', 'label' => 'No'],
                ],
            ],
            // Cameroon, city
            70  => [
                'id'       => 70,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Duala', 'label' => 'Duola'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            // Senegal, city
            71  => [
                'id'       => 71,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Dakar', 'label' => 'Dakar'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            72  => [
                'id'       => 72,
                'type'     => 'single_choice',
                'question' => 'Which below alternative best describes your current situation?',
                'options'  => [
                    [
                        'id'    => 1, 'value' => 'student',
                        'label' => 'I am a student (even if you have a part-time job while studying).',
                    ],
                    ['id' => 2, 'value' => 'professional', 'label' => 'I work /  I am a professional.'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            73  => [
                'id'       => 73,
                'type'     => 'single_choice',
                'question' => 'Which educational institution do you attend?',
                'options'  => [
                    ['id' => 1, 'value' => 'addis_ababa_aait', 'label' => 'Addis Ababa : AAIT'],
                    ['id' => 2, 'value' => 'addis_ababa_uni', 'label' => 'Addis Ababa University : SOC'],
                    [
                        'id'    => 3, 'value' => 'addis_ababa_science_technology',
                        'label' => 'Addis Ababa Science and Technology',
                    ],
                    ['id' => 4, 'value' => 'bahir_dar_uni', 'label' => 'Bahir Dar University'],
                    ['id' => 5, 'value' => 'jimma_uni', 'label' => 'Jimma University'],
                    ['id' => 6, 'value' => 'dire_dawa_uni', 'label' => 'Dire Dawa University'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            74  => [
                'id'       => 74,
                'type'     => 'single_choice',
                'question' => 'Which of the following best describes your employment status?',
                'options'  => $this->getEmploymentStatus(2),
            ],
            75  => [
                'id'       => 75,
                'type'     => 'single_choice',
                'question' => 'Which of the following best describes your job position?',
                'options'  => [
                    ['id' => 1, 'value' => 'business-owner_founder', 'label' => 'Business Owner/Founder'],
                    ['id' => 2, 'value' => 'c-suite', 'label' => 'C-suite'],
                    ['id' => 3, 'value' => 'senior_management', 'label' => 'Senior management'],
                    ['id' => 4, 'value' => 'head_department', 'label' => 'Head of Department'],
                    ['id' => 5, 'value' => 'middle_management', 'label' => 'Middle Management'],
                    ['id' => 6, 'value' => 'junior_management', 'label' => 'Junior Management'],
                    ['id' => 7, 'value' => 'team_member', 'label' => 'Team member'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            // Zambia, city
            76  => [
                'id'       => 76,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Lusaka', 'label' => 'Lusaka'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            // Angola, city
            77  => [
                'id'       => 77,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Luanda', 'label' => 'Luanda'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            78  => [
                'id'       => 78,
                'type'     => 'single_choice',
                'question' => 'Do you have children aged between 1 to 12 years old?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                    ['id' => 2, 'value' => 'no', 'label' => 'No'],
                ],
            ],
            // Screen on biased respondents based on markets.
            79  => [
                'id'       => 79,
                'type'     => 'single_choice',
                'question' => 'Do you work for any of the following types of companies?',
                'options'  => [
                    ['id' => 1, 'value' => 'advertising', 'label' => 'Advertising company.'],
                    [
                        'id'    => 2, 'value' => 'market-research_marketing',
                        'label' => 'Marketing or Market Research company.',
                    ],
                    ['id' => 3, 'value' => 'public_relations', 'label' => 'Public Relations company.'],
                    [
                        'id'    => 4, 'value' => 'journalism_tv_radio_media',
                        'label' => 'Journalism, TV, Radio or Media company.',
                    ],
                    ['id' => 5, 'value' => 'toy_manufacturers', 'label' => 'Toy Manufacturers company.'],
                    ['id' => 6, 'value' => 'no', 'label' => 'Don\'t know'],
                    ['id' => 0, 'value' => 'no', 'label' => 'None of these'],
                ],
            ],
            // Nigeria cities
            80  => [
                'id'       => 80,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'lagos-island', 'label' => 'Lagos island (Victoria Island, Ikoyi)'],
                    ['id' => 2, 'value' => 'lekki-phase-1', 'label' => 'Lekki phase 1'],
                    ['id' => 3, 'value' => 'lekki-peninsula-2', 'label' => 'Lekki peninsula II'],
                    [
                        'id'    => 4, 'value' => 'surulele|yaba|iganmu|malu|kosofe|ikeja|somushu|mushin',
                        'label' => 'Surulele, Yaba, Iganmu, Malu, Kosofe, Ikeja, Somushu, Mushin',
                    ],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Kenya cities
            81  => [
                'id'       => 81,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'nairobi', 'label' => 'Nairobi'],
                    [
                        'id'    => 2, 'value' => 'eldoret|kitengela|kisumu|mombasa|nakuru|ngong_rongai_karen',
                        'label' => 'Eldoret, Kitengela, Kisumu, Mombasa, Nakuru, Ngong - Rongai - Karen',
                    ],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Kenya cities
            82  => [
                'id'       => 82,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'Douala', 'label' => 'Douala'],
                    ['id' => 2, 'value' => 'Yaounde', 'label' => 'Yaounde'],
                    ['id' => 3, 'value' => 'Garoua', 'label' => 'Garoua'],
                    ['id' => 4, 'value' => 'Kousseri', 'label' => 'Kousseri'],
                    ['id' => 5, 'value' => 'Bamenda', 'label' => 'Bamenda'],
                    ['id' => 6, 'value' => 'Maroua', 'label' => 'Maroua'],
                    ['id' => 7, 'value' => 'Bafoussam', 'label' => 'Bafoussam'],
                    ['id' => 8, 'value' => 'Mokolo', 'label' => 'Mokolo'],
                    ['id' => 9, 'value' => 'Ngaoundere', 'label' => 'Ngaoundere'],
                    ['id' => 10, 'value' => 'Bertoua', 'label' => 'Bertoua'],
                    ['id' => 11, 'value' => 'Edea', 'label' => 'Edea'],
                    ['id' => 12, 'value' => 'Loum', 'label' => 'Loum'],
                    ['id' => 13, 'value' => 'Limbe', 'label' => 'Limbe'],
                    ['id' => 14, 'value' => 'Buea', 'label' => 'Buea'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Nigeria
            83  => [
                'id'       => 83,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'NGN100.000-', 'label' => 'Less then NGN 100.000'],
                    ['id' => 3, 'value' => 'NGN100.000-NGN199.999', 'label' => 'Between NGN 100.000 and NGN 199.999'],
                    ['id' => 4, 'value' => 'NGN200.000-NGN499.999', 'label' => 'Between NGN 200.000 and NGN 499.999'],
                    ['id' => 5, 'value' => 'NGN500.000+', 'label' => 'More than NGN 500.000'],
                ],
            ],
            // Online shopper last 3 months
            84  => [
                'id'       => 84,
                'type'     => 'single_choice',
                'question' => 'Have you shopped at one of the following online stores in the last 3 months?',
                'options'  => [
                    ['id' => 1, 'value' => 'amazon', 'label' => 'Yes, Amazon.com'],
                    ['id' => 2, 'value' => 'jumia', 'label' => 'Yes, Jumia.com.ng'],
                    ['id' => 3, 'value' => 'konga', 'label' => 'Yes, Konga.com'],
                    ['id' => 4, 'value' => 'ali_express', 'label' => 'Yes, AliExpress.com'],
                    ['id' => 5, 'value' => 'no', 'label' => 'No'],
                    ['id' => 6, 'value' => 'unknown', 'label' => 'Don\'t know'],
                ],
            ],
            // Screen on biased respondents based on markets.
            85  => [
                'id'       => 85,
                'type'     => 'single_choice',
                'question' => 'Do you or any of your immediate household, work for any of the following types of companies?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Software & Information Technology (IT) Services'],
                    ['id' => 2, 'value' => 'yes', 'label' => 'Advertising'],
                    ['id' => 3, 'value' => 'yes', 'label' => 'Agriculture'],
                    ['id' => 4, 'value' => 'yes', 'label' => 'Market research or data analytics'],
                    ['id' => 5, 'value' => 'yes', 'label' => 'Manufacturing'],
                    ['id' => 6, 'value' => 'yes', 'label' => 'Online Retail'],
                    ['id' => 7, 'value' => 'yes', 'label' => 'Entertainment'],
                    ['id' => 8, 'value' => 'unknown', 'label' => 'Prefer not to answer'],
                    ['id' => 9, 'value' => 'unknown', 'label' => 'Don\'t know'],
                    ['id' => 10, 'value' => 'no', 'label' => 'None of these'],
                ],
            ],
            86  => [
                'id'       => 86,
                'type'     => 'single_choice',
                'question' => __('screening.parent_children_age_range.question.1'),
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => __('screening.general.yes')],
                    ['id' => 2, 'value' => 'no', 'label' => __('screening.general.no')],
                ],
            ],
            // Ghana cities
            87  => [
                'id'       => 87,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'accra', 'label' => 'Accra'],
                    ['id' => 2, 'value' => 'kumasi', 'label' => 'Kumasi'],
                    ['id' => 3, 'value' => 'sekondi-takoradi', 'label' => 'Sekondi-Takoradi'],
                    ['id' => 4, 'value' => 'sunyani', 'label' => 'Sunyani'],
                    ['id' => 5, 'value' => 'tamale', 'label' => 'Tamale'],
                    ['id' => 6, 'value' => 'obusai', 'label' => 'Obusai'],
                    ['id' => 7, 'value' => 'cape-coast', 'label' => 'Cape Coast'],
                    ['id' => 8, 'value' => 'koforidua', 'label' => 'Koforidua'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Nigeria cities
            88  => [
                'id'       => 88,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'lagos', 'label' => 'Lagos'],
                    ['id' => 2, 'value' => 'kano', 'label' => 'Kano'],
                    ['id' => 3, 'value' => 'abuja', 'label' => 'Abuja'],
                    ['id' => 4, 'value' => 'abuja', 'label' => 'Abuja'],
                    ['id' => 5, 'value' => 'ibadan', 'label' => 'Ibadan'],
                    ['id' => 6, 'value' => 'enugu', 'label' => 'Enugu'],
                    ['id' => 7, 'value' => 'phc', 'label' => 'PHC (Port Harcourt)'],
                    ['id' => 8, 'value' => 'kaduna', 'label' => 'Kaduna'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Malawi subdivisions
            89  => [
                'id'       => 89,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.province_prefecture.question'),
                'options'  => $this->getSubdivisions('MW'),
            ],
            // Algeria cities
            90  => [
                'id'       => 90,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'algiers', 'label' => 'L\'Algérois'],
                    ['id' => 2, 'value' => 'kabylia', 'label' => 'Kabylie'],
                    ['id' => 3, 'value' => 'oran', 'label' => 'L\'Oranie'],
                    ['id' => 4, 'value' => 'constantine', 'label' => 'Constantinois'],
                    ['id' => 5, 'value' => 'hautes-plaines', 'label' => 'Hauts Plateaux'],
                    ['id' => 6, 'value' => 'aures', 'label' => 'Les Aurés'],
                    ['id' => 7, 'value' => 'sahara', 'label' => 'Sahara'],
                    ['id' => 8, 'value' => 'tizi-ouzou', 'label' => 'Tizi Ouzou'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Tunisia cities
            91  => [
                'id'       => 91,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'tunis', 'label' => 'Tunis'],
                    ['id' => 2, 'value' => 'sfax', 'label' => 'Sfax'],
                    ['id' => 3, 'value' => 'sousse', 'label' => 'Sousse'],
                    ['id' => 4, 'value' => 'ettadhamen', 'label' => 'Ettadhamen'],
                    ['id' => 5, 'value' => 'kairouan', 'label' => 'Kairouan'],
                    ['id' => 6, 'value' => 'gabes', 'label' => 'Gabès'],
                    ['id' => 7, 'value' => 'bizerte', 'label' => 'Bizerte'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Morocco cities
            92  => [
                'id'       => 92,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'beni-mellal', 'label' => 'Béni Mellal'],
                    ['id' => 2, 'value' => 'fes', 'label' => 'Fès'],
                    ['id' => 3, 'value' => 'grand-casablanca', 'label' => 'Grand Casablanca'],
                    ['id' => 4, 'value' => 'marrakech', 'label' => 'Marrakech'],
                    ['id' => 5, 'value' => 'oujda', 'label' => 'Oujda'],
                    ['id' => 6, 'value' => 'rabat-sale', 'label' => 'Rabat-Salé'],
                    ['id' => 7, 'value' => 'souss-massa', 'label' => 'Souss-Massa'],
                    ['id' => 8, 'value' => 'tanger', 'label' => 'Tanger'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Egypt cities
            93  => [
                'id'       => 93,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'greater-cairo', 'label' => 'Greater Cairo'],
                    ['id' => 2, 'value' => 'alexandria', 'label' => 'Alexandria'],
                    ['id' => 3, 'value' => 'tanta', 'label' => 'Tanta'],
                    ['id' => 4, 'value' => 'mansoura', 'label' => 'Mansoura'],
                    ['id' => 5, 'value' => 'menia', 'label' => 'Menia'],
                    ['id' => 6, 'value' => 'asyut', 'label' => 'Asyut'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Access to webcam
            94  => [
                'id'       => 94,
                'type'     => 'single_choice',
                'question' => __('screening.general.webcam_access.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => __('screening.general.yes')],
                    ['id' => 2, 'value' => 'no', 'label' => __('screening.general.no')],
                ],
            ],
            // Gabon subdivisions
            95  => [
                'id'       => 95,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.regions.question'),
                'options'  => $this->getSubdivisions('GA'),
            ],
            // Ivory Coast cities
            96  => [
                'id'       => 96,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'abidjan', 'label' => 'Abidjan'],
                    ['id' => 2, 'value' => 'abodo', 'label' => 'Abodo'],
                    ['id' => 3, 'value' => 'bouake', 'label' => 'Bouaké'],
                    ['id' => 4, 'value' => 'daloa', 'label' => 'Daloa'],
                    ['id' => 5, 'value' => 'san-pedro', 'label' => 'San-Pédro'],
                    ['id' => 6, 'value' => 'yamoussoukro', 'label' => 'Yamoussoukro'],
                    ['id' => 7, 'value' => 'korhogo', 'label' => 'Korhogo'],
                    ['id' => 8, 'value' => 'man', 'label' => 'Man'],
                    ['id' => 9, 'value' => 'divo', 'label' => 'Divo'],
                    ['id' => 10, 'value' => 'gagnoa', 'label' => 'Gagnoa'],
                    ['id' => 11, 'value' => 'abengourou', 'label' => 'Abengourou'],
                    ['id' => 12, 'value' => 'anyama', 'label' => 'Anyama'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Angola subdivisions
            97  => [
                'id'       => 97,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.province_capital.question'),
                'options'  => $this->getSubdivisions('AO'),
            ],
            // Egypt subdivisions
            98  => [
                'id'       => 98,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.governorate.question'),
                'options'  => $this->getSubdivisions('EG'),
            ],
            // Ethiopia subdivisions
            99  => [
                'id'       => 99,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.state.question'),
                'options'  => $this->getSubdivisions('ET'),
            ],
            // Nigeria
            100 => [
                'id'       => 100,
                'type'     => 'single_choice',
                'question' => 'What is your current household income per month?',
                'options'  => [
                    ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 2, 'value' => 'NGN200.000-', 'label' => 'Less then NGN 200.000'],
                    ['id' => 3, 'value' => 'NGN200.000+', 'label' => 'More than NGN 200.000'],
                ],
            ],
            // Ghana annual income ranges
            101 => [
                'id'       => 101,
                'type'     => 'single_choice',
                'question' => __('screening.annual_household_income_range.question'),
                'options'  => [
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 1, 'value' => 'GHS40.000-', 'label' => 'Less then GHS 40.000'],
                    ['id' => 1, 'value' => 'GHS40.001-GHS50.000', 'label' => 'Between GHS 40.001 and GHS 50.000'],
                    ['id' => 2, 'value' => 'GHS50.001-GHS60.000', 'label' => 'Between GHS 50.001 and GHS 60.000'],
                    ['id' => 3, 'value' => 'GHS60.001-GHS70.000', 'label' => 'Between GHS 60.001 and GHS 70.000'],
                    ['id' => 4, 'value' => 'GHS70.001-GHS80.000', 'label' => 'Between GHS 70.001 and GHS 80.000'],
                    ['id' => 5, 'value' => 'GHS80.001-GHS100.000', 'label' => 'Between GHS 80.001 and GHS 100.000'],
                    ['id' => 6, 'value' => 'GHS100.001-GHS120.000', 'label' => 'Between GHS 100.001 and GHS 120.000'],
                    ['id' => 7, 'value' => 'GHS120.001-GHS140.000', 'label' => 'Between GHS 120.001 and GHS 140.000'],
                    ['id' => 8, 'value' => 'GHS140.001+', 'label' => 'More than GHS 140.000'],
                ],
            ],
            // Ghana cities
            102 => [
                'id'       => 102,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'accra', 'label' => 'Accra'],
                    ['id' => 2, 'value' => 'kumasi', 'label' => 'Kumasi'],
                    ['id' => 3, 'value' => 'takoradi', 'label' => 'Takoradi'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Tanzania annual income ranges
            103 => [
                'id'       => 103,
                'type'     => 'single_choice',
                'question' => __('screening.annual_household_income_range.question'),
                'options'  => [
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => 'No income'],
                    ['id' => 1, 'value' => 'TZS18M-', 'label' => 'Less then TZS 18.000.000 (18M)'],
                    [
                        'id'    => 2, 'value' => 'TZS18M-TZS20M',
                        'label' => 'Between TZS 18.000.000 (18M) and TZS 20.000.000 (20M)',
                    ],
                    [
                        'id'    => 3, 'value' => 'TZS20M-TZS22M',
                        'label' => 'Between TZS 20.000.000 (20M) and TZS 22.000.000 (22M)',
                    ],
                    [
                        'id'    => 4, 'value' => 'TZS22M-TZS24M',
                        'label' => 'Between TZS 22.000.000 (22M) and TZS 24.000.000 (24M)',
                    ],
                    [
                        'id'    => 5, 'value' => 'TZS24M-TZS26M',
                        'label' => 'Between TZS 24.000.000 (24M) and TZS 26.000.000 (26M)',
                    ],
                    [
                        'id'    => 6, 'value' => 'TZS26M-TZS30M',
                        'label' => 'Between TZS 26.000.000 (26M) and TZS 30.000.000 (30M)',
                    ],
                    [
                        'id'    => 7, 'value' => 'TZS30M-TZS35M',
                        'label' => 'Between TZS 30.000.000 (30M) and TZS 35.000.000 (35M)',
                    ],
                    ['id' => 8, 'value' => 'TZS30M+', 'label' => 'More then TZS 35.000.000 (35M)'],
                ],
            ],
            // Ghana cities
            104 => [
                'id'       => 104,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'abidjan-nord', 'label' => 'Abidjan Nord'],
                    ['id' => 2, 'value' => 'abidjan-sud', 'label' => 'Abidjan Sud'],
                    [
                        'id'    => 3, 'value' => 'grand-bassam|yamoussoukro|yopougon',
                        'label' => 'Grand Bassam, Yamoussoukro or Yopougon',
                    ],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Children quardian
            105 => [
                'id'       => 105,
                'type'     => 'single_choice',
                'question' => __('screening.children_quardian.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => __('screening.general.yes')],
                    ['id' => 2, 'value' => 'no', 'label' => __('screening.general.no')],
                ],
            ],
            // South Africa cities
            106 => [
                'id'       => 106,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'gqeberha_port-elizabeth', 'label' => 'Gqeberha (Port Elizabeth)'],
                    ['id' => 2, 'value' => 'cape-town', 'label' => 'Cape Town'],
                    ['id' => 3, 'value' => 'johannesburg', 'label' => 'Johannesburg'],
                    ['id' => 4, 'value' => 'durban', 'label' => 'Durban'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Nigeria tribes
            107 => [
                'id'       => 107,
                'type'     => 'single_choice',
                'question' => 'How would you describe your ethnicity?',
                'options'  => [
                    ['id' => 1, 'value' => 'hausa', 'label' => 'Hausa'],
                    ['id' => 2, 'value' => 'yoruba', 'label' => 'Yoruba'],
                    ['id' => 3, 'value' => 'ijaw', 'label' => 'Ijaw'],
                    ['id' => 4, 'value' => 'igbo', 'label' => 'Igbo'],
                    ['id' => 5, 'value' => 'ibibio', 'label' => 'Ibibio'],
                    ['id' => 6, 'value' => 'tiv', 'label' => 'Tiv'],
                    ['id' => 7, 'value' => 'fulani', 'label' => 'Fulani'],
                    ['id' => 8, 'value' => 'kanuri', 'label' => 'Kanuri'],
                    ['id' => 9, 'value' => 'white', 'label' => 'White'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            // Uganda cities
            108 => [
                'id'       => 108,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'dakar', 'label' => 'Dakar'],
                    ['id' => 2, 'value' => 'diourbel', 'label' => 'Diourbel'],
                    ['id' => 3, 'value' => 'fatick', 'label' => 'Fatick'],
                    ['id' => 4, 'value' => 'kaffrine', 'label' => 'Kaffrine'],
                    ['id' => 5, 'value' => 'kaolack', 'label' => 'Kaolack'],
                    ['id' => 6, 'value' => 'kedougou', 'label' => 'Kédougou'],
                    ['id' => 7, 'value' => 'kolda', 'label' => 'Kolda'],
                    ['id' => 8, 'value' => 'louga', 'label' => 'Louga'],
                    ['id' => 9, 'value' => 'matam', 'label' => 'Matam'],
                    ['id' => 10, 'value' => 'saint-louis', 'label' => 'Saint-Louis'],
                    ['id' => 11, 'value' => 'sedhiou', 'label' => 'Sédhiou'],
                    ['id' => 12, 'value' => 'tambacounda', 'label' => 'Tambacounda'],
                    ['id' => 13, 'value' => 'thies', 'label' => 'Thiès'],
                    ['id' => 14, 'value' => 'ziguinchor', 'label' => 'Ziguinchor'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Nigeria
            109 => [
                'id'       => 109,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_household_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'XOF50.000-', 'label' => 'Less then XOF 50.000',
                    ], [
                        'id'    => 2,
                        'value' => 'XOF50.000-XOF200.000',
                        'label' => 'Between XOF 50.000 and XOF 200.000',
                    ], [
                        'id'    => 3,
                        'value' => 'XOF200.000-XOF400.000',
                        'label' => 'Between XOF 200.000 and XOF 400.000',
                    ], [
                        'id'    => 4,
                        'value' => 'XOF400.000-XOF900.000',
                        'label' => 'Between XOF 400.000 and XOF 900.000',
                    ], [
                        'id'    => 5,
                        'value' => 'XOF900.000+',
                        'label' => 'More than XOF 900.000',
                    ],
                ],
            ],
            // Nigeria cities
            110 => [
                'id'       => 110,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'lagos', 'label' => 'Lagos'],
                    ['id' => 2, 'value' => 'ibadan', 'label' => 'Ibadan'],
                    ['id' => 3, 'value' => 'abuja', 'label' => 'Abuja'],
                    ['id' => 4, 'value' => 'benin', 'label' => 'Benin City'],
                    ['id' => 5, 'value' => 'port-harcourt', 'label' => 'Port Harcourt'],
                    ['id' => 6, 'value' => 'aba', 'label' => 'Aba'],
                    ['id' => 7, 'value' => 'ilorin', 'label' => 'Ilorin'],
                    ['id' => 8, 'value' => 'jos', 'label' => 'Jos'],
                    ['id' => 9, 'value' => 'kaduna', 'label' => 'Kaduna'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Zambia monthly personal income
            111 => [
                'id'       => 111,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_personal_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'ZMW2.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'ZMW',
                            'local_amount'   => '2.000',
                        ]),
                    ], [
                        'id'    => 2,
                        'value' => 'ZMW2.000-ZMW7.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'ZMW',
                            'min_local_amount' => '2.000',
                            'max_local_amount' => '7.999',
                        ]),
                    ], [
                        'id'    => 3,
                        'value' => 'ZMW8.000-ZMW14.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'ZMW',
                            'min_local_amount' => '8.000',
                            'max_local_amount' => '14.999',
                        ]),
                    ], [
                        'id'    => 4,
                        'value' => 'ZMW15.000-ZMW39.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'ZMW',
                            'min_local_amount' => '15.000',
                            'max_local_amount' => '39.999',
                        ]),
                    ], [
                        'id'    => 5,
                        'value' => 'ZMW40.000-ZMW79.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'ZMW',
                            'min_local_amount' => '40.000',
                            'max_local_amount' => '79.999',
                        ]),
                    ], [
                        'id'    => 6,
                        'value' => 'ZMW80.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'ZMW',
                            'local_amount'   => '80.000',
                        ]),
                    ],
                ],
            ],
            // Ghana monthly personal income
            112 => [
                'id'       => 112,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_personal_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'GHS500-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'GHS',
                            'local_amount'   => '500',
                        ]),
                    ], [
                        'id'    => 2,
                        'value' => 'GHS500-GHS1.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '500',
                            'max_local_amount' => '1.000',
                        ]),
                    ], [
                        'id'    => 3,
                        'value' => 'GHS1.000-GHS2.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '1.000',
                            'max_local_amount' => '2.000',
                        ]),
                    ], [
                        'id'    => 4,
                        'value' => 'GHS2.000-GHS3.500',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '2.000',
                            'max_local_amount' => '3.500',
                        ]),
                    ], [
                        'id'    => 5,
                        'value' => 'GHS3.500-GHS4.500',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '3.500',
                            'max_local_amount' => '4.500',
                        ]),
                    ], [
                        'id'    => 6,
                        'value' => 'GHS4.500-GHS6.500',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '4.500',
                            'max_local_amount' => '6.500',
                        ]),
                    ], [
                        'id'    => 7,
                        'value' => 'GHS6.500-GHS8.500',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '6.500',
                            'max_local_amount' => '8.500',
                        ]),
                    ], [
                        'id'    => 8,
                        'value' => 'GHS8.500-GHS10.500',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '8.500',
                            'max_local_amount' => '10.500',
                        ]),
                    ], [
                        'id'    => 9,
                        'value' => 'GHS10.500-GHS16.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'GHS',
                            'min_local_amount' => '10.500',
                            'max_local_amount' => '16.000',
                        ]),
                    ], [
                        'id'    => 10,
                        'value' => 'GHS16.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'GHS',
                            'local_amount'   => '16.000',
                        ]),
                    ],
                ],
            ],
            // Botswana monthly personal income
            113 => [
                'id'       => 113,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_personal_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'BWP2.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'BWP',
                            'local_amount'   => '2.000',
                        ]),
                    ], [
                        'id'    => 2,
                        'value' => 'BWP2.000-BWP5.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'BWP',
                            'min_local_amount' => '2.000',
                            'max_local_amount' => '5.999',
                        ]),
                    ], [
                        'id'    => 3,
                        'value' => 'BWP6.000-BWP10.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'BWP',
                            'min_local_amount' => '6.000',
                            'max_local_amount' => '10.999',
                        ]),
                    ], [
                        'id'    => 4,
                        'value' => 'BWP11.000-BWP19.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'BWP',
                            'min_local_amount' => '11.000',
                            'max_local_amount' => '19.999',
                        ]),
                    ], [
                        'id'    => 5,
                        'value' => 'BWP20.000-BWP29.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'BWP',
                            'min_local_amount' => '20.000',
                            'max_local_amount' => '29.999',
                        ]),
                    ], [
                        'id'    => 6,
                        'value' => 'BWP30.000-BWP59.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'BWP',
                            'min_local_amount' => '30.000',
                            'max_local_amount' => '59.999',
                        ]),
                    ], [
                        'id'    => 7,
                        'value' => 'BWP60.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'BWP',
                            'local_amount'   => '60.000',
                        ]),
                    ],
                ],
            ],
            // Mozambique monthly personal income
            114 => [
                'id'       => 114,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_personal_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'MZN8.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'MZN',
                            'local_amount'   => '8.000',
                        ]),
                    ], [
                        'id'    => 2,
                        'value' => 'MZN8.000-MZN19.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'MZN',
                            'min_local_amount' => '8.000',
                            'max_local_amount' => '19.999',
                        ]),
                    ], [
                        'id'    => 3,
                        'value' => 'MZN20.000-MZN49.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'MZN',
                            'min_local_amount' => '20.000',
                            'max_local_amount' => '49.999',
                        ]),
                    ], [
                        'id'    => 4,
                        'value' => 'MZN50.000-MZN99.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'MZN',
                            'min_local_amount' => '50.000',
                            'max_local_amount' => '99.999',
                        ]),
                    ], [
                        'id'    => 5,
                        'value' => 'MZN100.000-MZN149.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'MZN',
                            'min_local_amount' => '100.000',
                            'max_local_amount' => '149.999',
                        ]),
                    ], [
                        'id'    => 6,
                        'value' => 'MZN150.000-MZN299.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'MZN',
                            'min_local_amount' => '150.000',
                            'max_local_amount' => '299.999',
                        ]),
                    ], [
                        'id'    => 7,
                        'value' => 'MZN300.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'MZN',
                            'local_amount'   => '300.000',
                        ]),
                    ],
                ],
            ],
            // Namibia monthly personal income
            115 => [
                'id'       => 115,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_personal_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'NAD2.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'NAD',
                            'local_amount'   => '2.000',
                        ]),
                    ], [
                        'id'    => 2,
                        'value' => 'NAD2.000-NAD9.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NAD',
                            'min_local_amount' => '2.000',
                            'max_local_amount' => '9.999',
                        ]),
                    ], [
                        'id'    => 3,
                        'value' => 'NAD10.000-NAD14.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NAD',
                            'min_local_amount' => '10.000',
                            'max_local_amount' => '14.999',
                        ]),
                    ], [
                        'id'    => 4,
                        'value' => 'NAD15.000-19.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NAD',
                            'min_local_amount' => '15.000',
                            'max_local_amount' => '19.999',
                        ]),
                    ], [
                        'id'    => 5,
                        'value' => 'NAD20.000-NAD29.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NAD',
                            'min_local_amount' => '20.000',
                            'max_local_amount' => '29.999',
                        ]),
                    ], [
                        'id'    => 6,
                        'value' => 'NAD30.000-NAD59.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NAD',
                            'min_local_amount' => '30.000',
                            'max_local_amount' => '59.999',
                        ]),
                    ], [
                        'id'    => 7,
                        'value' => 'NAD60.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'NAD',
                            'local_amount'   => '60.000',
                        ]),
                    ],
                ],
            ],

            // Study in Australia
            116 => [
                'id'       => 116,
                'type'     => 'single_choice',
                'question' => 'Which of the following best describes your current situation?',
                'info'     => [
                    'Study includes studying at undergraduate, postgraduate or HDR level either on campus, online or a hybrid of both on a part-time or full-time basis.',
                ],
                'options'  => [
                    [
                        'id'    => 1, 'value' => 'australia_student',
                        'label' => 'I am currently a student studying at an Australian university and I would consider further study at an Australian university at some time in the future',
                    ],
                    [
                        'id'    => 2, 'value' => 'looking_study_oversee_now',
                        'label' => 'I am actively looking to study at an overseas university in the next 12 months',
                    ],
                    [
                        'id'    => 3, 'value' => 'consider_study_oversee_future',
                        'label' => 'I would consider studying at an overseas university at some time in the future',
                    ],
                    ['id' => 0, 'value' => 'none', 'label' => 'None of the above'],
                ],
            ],

            // Study country
            117 => [
                'id'       => 117,
                'type'     => 'single_choice',
                'question' => 'Would you consider Australia as one of the countries you go to study in?',
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => 'Yes'],
                    ['id' => 0, 'value' => 'no', 'label' => 'No'],
                ],
            ],
            118 => [
                'id'       => 118,
                'type'     => 'single_choice',
                'question' => __('screening.parent_children_age_range.question.2'),
                'options'  => [
                    ['id' => 1, 'value' => 'yes', 'label' => __('screening.general.yes')],
                    ['id' => 2, 'value' => 'no', 'label' => __('screening.general.no')],
                ],
            ],
            119 => [
                'id'       => 119,
                'type'     => 'single_choice',
                'question' => 'Which television package do you watch or have?',
                'options'  => [
                    ['id' => 1, 'value' => 'dstv', 'label' => 'DSTV'],
                    ['id' => 2, 'value' => 'netflix', 'label' => 'Netflix'],
                    ['id' => 3, 'value' => 'free_air_channels', 'label' => 'Free Air Channels'],
                    ['id' => 4, 'value' => 'other', 'label' => 'Other'],
                    ['id' => 0, 'value' => 'none', 'label' => 'None'],
                ],
            ],
            120 => [
                'id'       => 120,
                'type'     => 'single_choice',
                'question' => 'Which city do you currently live in?',
                'options'  => [
                    ['id' => 1, 'value' => 'lagos', 'label' => 'Lagos'],
                    ['id' => 2, 'value' => 'abuja', 'label' => 'Abuja'],
                    ['id' => 3, 'value' => 'onitsha', 'label' => 'Onitsha'],
                    ['id' => 4, 'value' => 'ibadan', 'label' => 'Ibadan'],
                    ['id' => 5, 'value' => 'port harcourt', 'label' => 'Port Harcourt'],
                    ['id' => 6, 'value' => 'benin city', 'label' => 'Benin city'],
                    ['id' => 7, 'value' => 'aba', 'label' => 'Aba'],
                    ['id' => 8, 'value' => 'ilorin', 'label' => 'Ilorin'],
                    ['id' => 9, 'value' => 'enugu', 'label' => 'Enugu'],
                    ['id' => 10, 'value' => 'abeokuta', 'label' => 'Abeokuta'],
                    ['id' => 11, 'value' => 'warri', 'label' => 'Warri'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            121 => [
                'id'       => 121,
                'type'     => 'single_choice',
                'question' => 'Which types of alcohol have you consumed in the past 4 weeks?',
                'options'  => [
                    ['id' => 1, 'value' => 'beer', 'label' => 'Beer (incl. flavoured beer)'],
                    ['id' => 2, 'value' => 'cider', 'label' => 'Cider'],
                    [
                        'id'    => 3, 'value' => 'flavoured_alcoholic_beverages',
                        'label' => 'Ready-to-drink flavoured alcoholic beverages (coolers)',
                    ],
                    ['id' => 4, 'value' => 'spirits', 'label' => 'Spirits like brandy, whiskey, gin, etc.'],
                    ['id' => 5, 'value' => 'wine_champagne', 'label' => 'Wine or Champagne'],
                    ['id' => 0, 'value' => 'other', 'label' => 'Other'],
                ],
            ],
            // Ethiopia subdivisions
            122 => [
                'id'       => 122,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.district_city_town.question'),
                'options'  => $this->getSubdivisions('BW'),
            ],
            // Nigeria
            123 => [
                'id'       => 123,
                'type'     => 'single_choice',
                'question' => __('screening.monthly_household_income_range.question'),
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'NO_INCOME',
                        'label' => __('screening.general.no_income'),
                    ], [
                        'id'    => 1,
                        'value' => 'NGN200.000-', 'label' => 'Less then NGN 200.000',
                    ], [
                        'id'    => 2,
                        'value' => 'NGN200.000+',
                        'label' => 'More than NGN 200.000',
                    ],
                ],
            ],
            // Tea consumption
            124 => [
                'id'       => 124,
                'type'     => 'single_choice',
                'question' => 'Do you consume tea using teabags?',
                'options'  => [
                    [
                        'id'    => 0,
                        'value' => 'yes',
                        'label' => __('screening.general.yes'),
                    ], [
                        'id'    => 1,
                        'value' => 'no',
                        'label' => __('screening.general.no'),
                    ],
                ],
            ],
            // Type tabag consumption
            125 => [
                'id'       => 125,
                'type'     => 'single_choice',
                'question' => 'Which type of the following teabags do you use?',
                'options'  => [
                    [
                        'id'    => 1,
                        'value' => 'rooibos_tb',
                        'label' => 'Rooibos teabags',
                    ], [
                        'id'    => 2,
                        'value' => 'black_tb',
                        'label' => 'Black teabags',
                    ], [
                        'id'    => 3,
                        'value' => 'herbal_tb',
                        'label' => 'Herbal teabags',
                    ], [
                        'id'    => 4,
                        'value' => 'fruit_tb',
                        'label' => 'Fruit teabags',
                    ], [
                        'id'    => 0,
                        'value' => 'other',
                        'label' => 'Other',
                    ],
                ],
            ],
            // Type tabag consumption
            126 => [
                'id'       => 126,
                'type'     => 'single_choice',
                'question' => 'Where are you living?',
                'options'  => [
                    [
                        'id'    => 1,
                        'value' => 'ashanti',
                        'label' => 'Ashanti',
                    ], [
                        'id'    => 2,
                        'value' => 'brong_ahafo',
                        'label' => 'Brong Ahafo',
                    ], [
                        'id'    => 3,
                        'value' => 'central',
                        'label' => 'Central',
                    ], [
                        'id'    => 4,
                        'value' => 'eastern',
                        'label' => 'Eastern',
                    ], [
                        'id'    => 5,
                        'value' => 'greater_accra',
                        'label' => 'Greater Accra',
                    ], [
                        'id'    => 6,
                        'value' => 'northern',
                        'label' => 'Northern',
                    ], [
                        'id'    => 7,
                        'value' => 'upper_east',
                        'label' => 'Upper East',
                    ], [
                        'id'    => 8,
                        'value' => 'upper_west',
                        'label' => 'Upper West',
                    ], [
                        'id'    => 9,
                        'value' => 'volta',
                        'label' => 'Volta',
                    ], [
                        'id'    => 10,
                        'value' => 'western',
                        'label' => 'Western',
                    ], [
                        'id'    => 0,
                        'value' => 'other',
                        'label' => 'Other',
                    ],
                ],
            ],
            // Cameroon annual income ranges
            127 => [
                'id'       => 127,
                'type'     => 'single_choice',
                'question' => __('screening.annual_household_income_range.question'),
                'options'  => [
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => __('screening.general.no_income')],
                    [
                        'id'    => 1, 'value' => 'XAF60.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'XAF',
                            'local_amount'   => '60.000',
                        ]),
                    ], [
                        'id'    => 2, 'value' => 'XAF60.000-XAF175.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '60.000',
                            'max_local_amount' => '175.000',
                        ]),
                    ], [
                        'id'    => 3, 'value' => 'XAF175.000-XAF290.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '175.000',
                            'max_local_amount' => '290.000',
                        ]),
                    ], [
                        'id'    => 4, 'value' => 'XAF290.000-XAF400.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '290.000',
                            'max_local_amount' => '400.000',
                        ]),
                    ], [
                        'id'    => 5, 'value' => 'XAF400.000-XAF600.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '400.000',
                            'max_local_amount' => '600.000',
                        ]),
                    ], [
                        'id'    => 6, 'value' => 'XAF600.000-XAF800.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '600.000',
                            'max_local_amount' => '800.000',
                        ]),
                    ], [
                        'id'    => 7, 'value' => 'XAF800.000-XAF1.000.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '800.000',
                            'max_local_amount' => '1.000.000',
                        ]),
                    ], [
                        'id'    => 8, 'value' => 'XAF1.000.000-XAF1.200.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '1.000.000',
                            'max_local_amount' => '1.200.000',
                        ]),
                    ], [
                        'id'    => 9, 'value' => 'XAF1.200.000-XAF1.400.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'XAF',
                            'min_local_amount' => '1.200.000',
                            'max_local_amount' => '1.400.000',
                        ]),
                    ], [
                        'id'    => 10, 'value' => 'XAF1.400.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'XAF',
                            'local_amount'   => '1.400.000',
                        ]),
                    ],
                ],
            ],
            // Cameroon cities
            128 => [
                'id'       => 128,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'boufassam', 'label' => 'Boufassam'],
                    ['id' => 2, 'value' => 'bamenda', 'label' => 'Bamenda'],
                    ['id' => 3, 'value' => 'bertoua', 'label' => 'Bertoua'],
                    ['id' => 4, 'value' => 'buea', 'label' => 'Buea'],
                    ['id' => 5, 'value' => 'douala', 'label' => 'Douala'],
                    ['id' => 6, 'value' => 'dschang', 'label' => 'Dschang'],
                    ['id' => 7, 'value' => 'garoua', 'label' => 'Garoua'],
                    ['id' => 8, 'value' => 'kribi', 'label' => 'Kribi'],
                    ['id' => 9, 'value' => 'limbe', 'label' => 'Limbe'],
                    ['id' => 10, 'value' => 'loum', 'label' => 'Loum'],
                    ['id' => 11, 'value' => 'maroua', 'label' => 'Maroua'],
                    ['id' => 12, 'value' => 'ngaoundere', 'label' => 'Ngaoundere'],
                    ['id' => 13, 'value' => 'nkonsamba', 'label' => 'Nkonsamba'],
                    ['id' => 14, 'value' => 'yaoundre', 'label' => 'Yaoundré'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Kenya annual household income ranges
            129 => [
                'id'       => 129,
                'type'     => 'single_choice',
                'question' => __('screening.annual_household_income_range.question'),
                'options'  => [
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => __('screening.general.no_income')],
                    [
                        'id'    => 1, 'value' => 'KES12.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'KES',
                            'local_amount'   => '12.000',
                        ]),
                    ], [
                        'id'    => 2, 'value' => 'KES12.000-KES69.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'KES',
                            'min_local_amount' => '12.000',
                            'max_local_amount' => '70.000',
                        ]),
                    ], [
                        'id'    => 3, 'value' => 'KES70.000-KES149.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'KES',
                            'min_local_amount' => '70.000',
                            'max_local_amount' => '150.000',
                        ]),
                    ], [
                        'id'    => 4, 'value' => 'KES150.000-KES299.999',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'KES',
                            'min_local_amount' => '150.000',
                            'max_local_amount' => '300.000',
                        ]),
                    ], [
                        'id'    => 10, 'value' => 'KES300.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'KES',
                            'local_amount'   => '300.000',
                        ]),
                    ],
                ],
            ],
            // Kenya cities
            130 => [
                'id'       => 130,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'mombasa', 'label' => 'Mombasa'],
                    ['id' => 2, 'value' => 'kitengela_ajiado_county', 'label' => 'Kitengela (Kajiado county)'],
                    ['id' => 3, 'value' => 'kilifi', 'label' => 'Kilifi'],
                    ['id' => 4, 'value' => 'meru', 'label' => 'Meru'],
                    ['id' => 5, 'value' => 'machakos', 'label' => 'Machakos'],
                    ['id' => 6, 'value' => 'nyeri', 'label' => 'Nyeri'],
                    ['id' => 7, 'value' => 'kiambu', 'label' => 'Kiambu'],
                    ['id' => 8, 'value' => 'uasin-gishu', 'label' => 'Uasin Gishu'],
                    ['id' => 9, 'value' => 'nakuru', 'label' => 'Nakuru'],
                    ['id' => 10, 'value' => 'bomet', 'label' => 'Bomet'],
                    ['id' => 11, 'value' => 'kisumu', 'label' => 'Kisumu'],
                    ['id' => 12, 'value' => 'rongai_ajiado_county', 'label' => 'Rongai   (Kajiado county)'],
                    ['id' => 13, 'value' => 'nairobi', 'label' => 'Nairobi'],
                    ['id' => 14, 'value' => 'garissa', 'label' => 'Garissa'],
                    ['id' => 15, 'value' => 'kakamega', 'label' => 'Kakamega'],
                    ['id' => 16, 'value' => 'trans-nzoia', 'label' => 'Trans Nzoia'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Kenya cities
            131 => [
                'id'       => 131,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'fes-boulemane', 'label' => 'Fès-Boulemane'],
                    ['id' => 1, 'value' => 'grand-casablanca', 'label' => 'Grand Casablanca'],
                    ['id' => 1, 'value' => 'marrakech-tensift-al-haouz', 'label' => 'Marrakech-Tensift-Al-Haouz'],
                    ['id' => 1, 'value' => 'meknes-tafilalet', 'label' => 'Meknes-Tafilalet'],
                    ['id' => 1, 'value' => 'rabat-sale-zemmour-zaer', 'label' => 'Rabat-Salé-Zemmour-Zaer'],
                    ['id' => 1, 'value' => 'tanger-tetouan', 'label' => 'Tanger-Tetouan'],
                    ['id' => 1, 'value' => 'oriental', 'label' => 'Oriental'],
                    ['id' => 1, 'value' => 'souss-massa-draa', 'label' => 'Souss-Massa-Draa'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Morocco cities
            132 => [
                'id'       => 132,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'casablanca', 'label' => 'Casablanca'],
                    ['id' => 2, 'value' => 'rabat', 'label' => 'Rabat'],
                    ['id' => 3, 'value' => 'tangiers/tetouan', 'label' => 'Tangiers / Tetouan'],
                    ['id' => 4, 'value' => 'fez/meknes', 'label' => 'Fez / Meknes'],
                    ['id' => 5, 'value' => 'marrakech', 'label' => 'Marrakech'],
                    ['id' => 6, 'value' => 'agadir', 'label' => 'Agadir'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
            // Rwanda subdivisions
            133 => [
                'id'       => 133,
                'type'     => 'single_choice',
                'question' => __('screening.subdivisions.province_capital.question'),
                'options'  => $this->getSubdivisions('RW'),
            ],
            // Nigeria annual household income ranges
            134 => [
                'id'       => 134,
                'type'     => 'single_choice',
                'question' => __('screening.typical_monthly_household_income_range.question'),
                'options'  => [
                    ['id' => 0, 'value' => 'NO_INCOME', 'label' => __('screening.general.no_income')],
                    [
                        'id'    => 1, 'value' => 'NGN40.000-',
                        'label' => __('screening.general.income_less_than', [
                            'local_currency' => 'NGN',
                            'local_amount'   => '40.000',
                        ]),
                    ], [
                        'id'    => 2, 'value' => 'NGN40.000-NGN200.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NGN',
                            'min_local_amount' => '40.000',
                            'max_local_amount' => '200.000',
                        ]),
                    ], [
                        'id'    => 3, 'value' => 'NGN200.000-NGN400.000',
                        'label' => __('screening.general.income_between', [
                            'local_currency'   => 'NGN',
                            'min_local_amount' => '200.000',
                            'max_local_amount' => '400.000',
                        ]),
                    ], [
                        'id'    => 4, 'value' => 'NGN400.000+',
                        'label' => __('screening.general.income_more_than', [
                            'local_currency' => 'NGN',
                            'local_amount'   => '400.000',
                        ]),
                    ],
                ],
            ],
            // Messaging apps and usage
            135 => [
                'id'       => 135,
                'type'     => 'multiple_choice',
                'question' => 'In the last 3 months, which of the following messaging apps have you used?',
                'options'  => [
                    ['id' => 1, 'value' => 'viber', 'label' => 'Viber'],
                    ['id' => 2, 'value' => 'instagram_direct_messaging', 'label' => 'Instagram Direct Messaging (DMs)'],
                    ['id' => 3, 'value' => 'discord', 'label' => 'Discord'],
                    ['id' => 4, 'value' => 'facebook_messenger', 'label' => 'Facebook Messenger'],
                    ['id' => 5, 'value' => 'whatsapp', 'label' => 'WhatsApp'],
                    ['id' => 6, 'value' => 'signal', 'label' => 'Signal'],
                    ['id' => 7, 'value' => 'snapchat', 'label' => 'Snapchat'],
                    ['id' => 8, 'value' => 'telegram', 'label' => 'Telegram'],
                    ['id' => 9, 'value' => 'line', 'label' => 'Line'],
                    ['id' => 10, 'value' => 'other', 'label' => __('screening.general.other')],
                    ['id' => 11, 'value' => 'none', 'label' => __('screening.messaging_app.none_used_past_month')],
                ],
            ],
            // Ghana cities
            136  => [
                'id'       => 136,
                'type'     => 'single_choice',
                'question' => __('screening.city.question'),
                'options'  => [
                    ['id' => 1, 'value' => 'accra', 'label' => 'Accra'],
                    ['id' => 2, 'value' => 'kumasi', 'label' => 'Kumasi (Ashanti Region)'],
                    ['id' => 3, 'value' => 'takoradi', 'label' => 'Takoradi (Western Region)'],
                    ['id' => 4, 'value' => 'tamale', 'label' => 'Tamale (Capital of Northern Region)'],
                    ['id' => 5, 'value' => 'koforidua', 'label' => 'Koforidua (capital of Eastern Region)'],
                    ['id' => 0, 'value' => 'other', 'label' => __('screening.city.other.label')],
                ],
            ],
        ];

        $requiredQuestions = [];
        foreach ($ids as $id) {
            if ( ! array_key_exists($id, $questionsAvailable)) {
                return null;
            }
            $requiredQuestions[] = $questionsAvailable[$id];
        }

        return collect($requiredQuestions)->map(function (array $question) {

            $question['code'] = encrypt($question['id']);

            if ( ! isset($question['options'])) {
                return $question;
            }

            foreach ($question['options'] as $key => $option) {
                $question['options'][$key]['code'] = encrypt($option['value']);
            }

            return $question;
        })->keyBy('id');
    }

    private function getEducationLevel(): array {

        return [
            ['id' => 0, 'value' => 'none', 'label' => 'None complete'],
            ['id' => 1, 'value' => 'primary-school', 'label' => 'Primary School'],
            ['id' => 2, 'value' => 'secondary-school', 'label' => 'Secondary School'],
            ['id' => 3, 'value' => 'high-school', 'label' => 'High School'],
            ['id' => 4, 'value' => 'tertiary/technical-college', 'label' => 'Tertiary/Technical College'],
            ['id' => 5, 'value' => 'university/higher-education', 'label' => 'University/Higher Education'],
            ['id' => 6, 'value' => 'postgraduate-education', 'label' => 'Postgraduate Education'],
        ];
    }

    private function getPersonalIncomePerMonth(string $countryCode) {

        $incomeLevelsPerCountry = [
            'UG' => [
                ['id' => 1, 'value' => 'UGX0-749.999', 'label' => 'Less than UGX 750.000'],
                ['id' => 2, 'value' => 'UGX750.000-999.999', 'label' => 'Between UGX 750.000 and UGX 999.999'],
                ['id' => 3, 'value' => 'UGX1.000.000-1.499.999', 'label' => 'Between UGX 1.000.000 and 1.499.999'],
                ['id' => 4, 'value' => 'UGX1.500.000-2.999.999', 'label' => 'Between UGX 1.500.000 and 2.999.999'],
                ['id' => 5, 'value' => 'UGX3.000.000+', 'label' => 'UGX 3.000.000 or more'],
            ],
            'GH' => [
                ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ['id' => 2, 'value' => 'GHS500-', 'label' => 'Less than GHS 500'],
                ['id' => 3, 'value' => 'GHS500-1.000', 'label' => 'Between GHS 500 and 1.000'],
                ['id' => 4, 'value' => 'GHS1.001-2.000', 'label' => 'Between GHS 1.001 and 2.000'],
                ['id' => 5, 'value' => 'GHS2.001-4.000', 'label' => 'Between GHS 2.001 and 4.000'],
                ['id' => 6, 'value' => 'GHS4.001-10.000', 'label' => 'Between GHS 4.001 and 10.000'],
                ['id' => 7, 'value' => 'GHS10.000+', 'label' => 'More than GHS 10.000'],
            ],
            'RW' => [
                ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ['id' => 2, 'value' => 'RWF300.000-', 'label' => 'Less than RWF 300.000'],
                ['id' => 3, 'value' => 'RWF300.001-600.000', 'label' => 'Between RWF 300.001 and 600.000'],
                ['id' => 4, 'value' => 'RWF600.001-1.000.000', 'label' => 'Between RWF 600.001 and 1.000.000'],
                ['id' => 5, 'value' => 'RWF1.000.000+', 'label' => 'More than RWF 1.000.000'],
            ],
            'ZA' => [
                ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ['id' => 2, 'value' => 'ZAR10.000-', 'label' => 'Less than ZAR 10.000'],
                ['id' => 3, 'value' => 'ZAR10.000-29.999', 'label' => 'Between ZAR 10.000 and 29.999'],
                ['id' => 4, 'value' => 'ZAR30.000+', 'label' => 'More than ZAR 30.000'],
            ],
            'ZW' => [
                ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ['id' => 2, 'value' => 'USD500-', 'label' => 'Less than USD 500'],
                ['id' => 3, 'value' => 'USD500-1.000', 'label' => 'Between USD 500 and 1.000'],
                ['id' => 4, 'value' => 'USD1.000+', 'label' => 'More than USD 1.000'],
            ],
        ];

        return $incomeLevelsPerCountry[$countryCode] ?? [];
    }

    private function getHouseholdIncomePerMonth(string $countryCode) {

        $list = [
            'ZA' => [
                ['id' => 1, 'value' => 'R0-R2.999', 'label' => 'ZAR 0 to ZAR 2.999'],
                ['id' => 2, 'value' => 'R3.000-R4.999', 'label' => 'ZAR 3.000 to ZAR 4.999'],
                ['id' => 3, 'value' => 'R5.000-R9.999', 'label' => 'ZAR 5.000 to ZAR 9.999'],
                ['id' => 4, 'value' => 'R10.000-R18.999', 'label' => 'ZAR 10.000 to ZAR 18.999'],
                ['id' => 5, 'value' => 'R19.000-R24.999', 'label' => 'ZAR 19.000 to ZAR 24.999'],
                ['id' => 6, 'value' => 'R25.000-R59.999', 'label' => 'ZAR 25.000 to ZAR 59.999'],
                ['id' => 7, 'value' => 'R60.000+', 'label' => 'ZAR 60.000 or more'],
            ],
            'MA' => [
                // Language: French!
                ['id' => 1, 'value' => 'MAD2.000-', 'label' => 'Inférieur à 2,000 MAD'],
                ['id' => 1, 'value' => 'MAD2.001-4.000', 'label' => '2,001-4,000 MAD'],
                ['id' => 1, 'value' => 'MAD4.001-6.000', 'label' => '4,001-6,000 MAD'],
                ['id' => 1, 'value' => 'MAD6.001-10.000', 'label' => '6,001-10,000 MAD'],
                ['id' => 1, 'value' => 'MAD10.001-20.000', 'label' => '10,001-20,000 MAD'],
                ['id' => 1, 'value' => 'MAD20.001-30.000', 'label' => '20,001-30,000 MAD'],
                ['id' => 1, 'value' => 'MAD30.001-40.000', 'label' => '30,001-40,000 MAD'],
                ['id' => 1, 'value' => 'MAD40.001-50.000', 'label' => '40,001-50,000 MAD'],
                ['id' => 1, 'value' => 'MAD50.000+', 'label' => 'Supérieur à 50,000 MAD'],
            ],
            'NG' => [
                ['id' => 1, 'value' => 'NO_INCOME', 'label' => 'No income'],
                ['id' => 2, 'value' => 'NGN40.000-', 'label' => 'Less then NGN 40.000'],
                ['id' => 3, 'value' => 'NGN40.001-NGN100.000', 'label' => 'Between NGN 40.000 and NGN 100.000'],
                ['id' => 4, 'value' => 'NGN100.000+', 'label' => 'More than NGN 100.000'],
            ],
        ];

        return $list[$countryCode] ?? [];
    }

    private function getEthnicity(): array {

        return [
            ['id' => 1, 'value' => 'BLACK', 'label' => 'Black'],
            ['id' => 2, 'value' => 'WHITE', 'label' => 'White'],
            ['id' => 3, 'value' => 'COLOURED', 'label' => 'Colored'],
            ['id' => 4, 'value' => 'INDIAN', 'label' => 'Indian'],
            ['id' => 5, 'value' => 'ASIAN', 'label' => 'Asian'],
            ['id' => 0, 'value' => 'OTHER', 'label' => 'Other'],
        ];
    }

    private function getEmploymentStatus(int $listId = 1): array {

        $lists = [
            1 => [
                ['id' => 1, 'value' => 'student', 'label' => 'Student'],
                ['id' => 2, 'value' => 'full-time_work', 'label' => 'Full-time work (30 or more hours per week)'],
                ['id' => 3, 'value' => 'part-time_contract_work', 'label' => 'Part-time/Contract work'],
                ['id' => 4, 'value' => 'self-employed_own_a_business', 'label' => 'Self-employed/Own a business'],
                ['id' => 5, 'value' => 'retired', 'label' => 'Retired'],
                ['id' => 6, 'value' => 'homemaker_stay_at_home_parent', 'label' => 'Homemaker/Stay at Home Parent'],
                ['id' => 7, 'value' => 'unemployed', 'label' => 'Unemployed'],
            ],
            2 => [
                ['id' => 1, 'value' => 'full-time_work', 'label' => 'Full-time (30 or more hours per week)'],
                ['id' => 2, 'value' => 'part-time_work', 'label' => 'Part-time'],
                [
                    'id'    => 3, 'value' => 'contract_freelance_temporary_work',
                    'label' => 'Contract, Freelance or Temporary employee',
                ],
                ['id' => 4, 'value' => 'retired', 'label' => 'Retired'],
                ['id' => 5, 'value' => 'unemployed', 'label' => 'Unemployed'],
                ['id' => 0, 'value' => 'other', 'label' => 'None of the above'],
            ],
        ];

        return $lists[$listId] ?? [];
    }

    /**
     * @param  string  $countryCode
     * @param  string  $subdivisionCode
     *
     * @return string|null
     */
    private function getGeoRegionSubdivisionBySubdivision(string $countryCode, string $subdivisionCode): ?string {

        $list = [
            'UG' => [
                'UG-C' => [
                    'UG-117',
                    'UG-118',
                    'UG-119',
                    'UG-120',
                    'UG-121',
                    'UG-101',
                    'UG-122',
                    'UG-102',
                    'UG-126',
                    'UG-112',
                    'UG-103',
                    'UG-123',
                    'UG-125',
                    'UG-104',
                    'UG-124',
                    'UG-114',
                    'UG-105',
                    'UG-115',
                    'UG-106',
                    'UG-107',
                    'UG-108',
                    'UG-116',
                    'UG-109',
                    'UG-110',
                    'UG-111',
                    'UG-113',
                ],
                'UG-E' => [
                    'UG-216',
                    'UG-217',
                    'UG-218',
                    'UG-201',
                    'UG-235',
                    'UG-219',
                    'UG-220',
                    'UG-225',
                    'UG-202',
                    'UG-221',
                    'UG-233',
                    'UG-226',
                    'UG-203',
                    'UG-204',
                    'UG-213',
                    'UG-222',
                    'UG-205',
                    'UG-206',
                    'UG-236',
                    'UG-207',
                    'UG-227',
                    'UG-208',
                    'UG-228',
                    'UG-229',
                    'UG-223',
                    'UG-214',
                    'UG-209',
                    'UG-230',
                    'UG-234',
                    'UG-224',
                    'UG-231',
                    'UG-210',
                    'UG-232',
                    'UG-215',
                    'UG-211',
                    'UG-212',
                ],
                'UG-N' => [
                    'UG-314',
                    'UG-301',
                    'UG-322',
                    'UG-323',
                    'UG-315',
                    'UG-324',
                    'UG-316',
                    'UG-302',
                    'UG-303',
                    'UG-317',
                    'UG-304',
                    'UG-318',
                    'UG-305',
                    'UG-319',
                    'UG-325',
                    'UG-306',
                    'UG-333',
                    'UG-326',
                    'UG-307',
                    'UG-320',
                    'UG-308',
                    'UG-309',
                    'UG-334',
                    'UG-311',
                    'UG-327',
                    'UG-310',
                    'UG-328',
                    'UG-331',
                    'UG-329',
                    'UG-321',
                    'UG-312',
                    'UG-332',
                    'UG-313',
                    'UG-330',
                ],
                'UG-W' => [
                    'UG-420',
                    'UG-416',
                    'UG-401',
                    'UG-430',
                    'UG-402',
                    'UG-403',
                    'UG-417',
                    'UG-418',
                    'UG-404',
                    'UG-405',
                    'UG-427',
                    'UG-428',
                    'UG-413',
                    'UG-414',
                    'UG-406',
                    'UG-407',
                    'UG-432',
                    'UG-419',
                    'UG-421',
                    'UG-408',
                    'UG-422',
                    'UG-415',
                    'UG-409',
                    'UG-410',
                    'UG-423',
                    'UG-424',
                    'UG-411',
                    'UG-429',
                    'UG-425',
                    'UG-431',
                    'UG-412',
                    'UG-426',
                ],
            ],
            'NG' => [
                'NG-North-Central' => [
                    'NG-BE',
                    'NG-KO',
                    'NG-KW',
                    'NG-NA',
                    'NG-NI',
                    'NG-PL',
                    'NG-FC',
                ],
                'NG-North-East'    => [
                    'NG-AD',
                    'NG-BA',
                    'NG-BO',
                    'NG-GO',
                    'NG-TA',
                    'NG-YO',
                ],
                'NG-North-West'    => [
                    'NG-JI',
                    'NG-KD',
                    'NG-KN',
                    'NG-KT',
                    'NG-KE',
                    'NG-SO',
                    'NG-ZA',
                ],
                'NG-South-East'    => [
                    'NG-AB',
                    'NG-AN',
                    'NG-EB',
                    'NG-EN',
                    'NG-IM',
                ],
                'NG-South-South'   => [
                    'NG-AK',
                    'NG-BY',
                    'NG-CR',
                    'NG-RI',
                    'NG-DE',
                    'NG-ED',
                ],
                'NG-South-West'    => [
                    'NG-EK',
                    'NG-LA',
                    'NG-OG',
                    'NG-ON',
                    'NG-OS',
                    'NG-OY',
                ],
            ],
            'CI' => [
                'CI-Central' => [
                    'CI-LC',
                    'CI-VB',
                    'CI-YM',
                ],
                'CI-East'    => [
                    'CI-CM',
                    'CI-SM',
                    'CI-ZZ',
                ],
                'CI-North'   => [
                    'CI-DN',
                    'CI-SV',
                ],
                'CI-South'   => [
                    'CI-AB',
                    'CI-BS',
                    'CI-GD',
                    'CI-LG',
                ],
                'CI-West'    => [
                    'CI-MG',
                    'CI-WR',
                ],
            ],
            'KE' => [
                'KE-Nairobi'       => [
                    'KE-30',
                ],
                'KE-Eastern'       => [
                    'KE-06',
                    'KE-09',
                    'KE-18',
                    'KE-22',
                    'KE-23',
                    'KE-25',
                    'KE-26',
                    'KE-41',
                ],
                'KE-Central'       => [
                    'KE-13',
                    'KE-15',
                    'KE-29',
                    'KE-35',
                    'KE-36',
                ],
                'KE-Rift Valley'   => [
                    'KE-01',
                    'KE-02',
                    'KE-05',
                    'KE-10',
                    'KE-12',
                    'KE-20',
                    'KE-31',
                    'KE-32',
                    'KE-33',
                    'KE-37',
                    'KE-42',
                    'KE-43',
                    'KE-44',
                    'KE-47',
                ],
                'KE-Coast'         => [
                    'KE-14',
                    'KE-19',
                    'KE-21',
                    'KE-28',
                    'KE-39',
                    'KE-40',
                ],
                'KE-North Eastern' => [
                    'KE-07',
                    'KE-24',
                    'KE-46',
                ],
                'KE-Nyanza'        => [
                    'KE-08',
                    'KE-16',
                    'KE-17',
                    'KE-27',
                    'KE-34',
                    'KE-38',
                ],
                'KE-Western'       => [
                    'KE-03',
                    'KE-04',
                    'KE-11',
                    'KE-45',
                ],
            ],
            'MA' => [
                'MA-01' => [
                    'MA-CHE',
                    'MA-FAH',
                    'MA-HOC',
                    'MA-LAR',
                    'MA-MDF',
                    'MA-OUZ',
                    'MA-TNG',
                    'MA-TET',
                ],
                'MA-02' => [
                    'MA-BER',
                    'MA-DRI',
                    'MA-FIG',
                    'MA-GUF',
                    'MA-JRA',
                    'MA-NAD',
                    'MA-OUJ',
                    'MA-TAI',
                ],
                'MA-03' => [
                    'MA-BOM',
                    'MA-FES',
                    'MA-HAJ',
                    'MA-IFR',
                    'MA-MEK',
                    'MA-MOU',
                    'MA-SEF',
                    'MA-TAO',
                    'MA-TAZ',
                ],
                'MA-04' => [
                    'MA-KEN',
                    'MA-KHE',
                    'MA-NOU',
                    'MA-RAB',
                    'MA-SAL',
                    'MA-SIK',
                    'MA-SIL',
                    'MA-SKH',
                ],
                'MA-05' => [
                    'MA-AZI',
                    'MA-BEM',
                    'MA-FQH',
                    'MA-KHN',
                    'MA-KHO',
                ],
                'MA-06' => [
                    'MA-BES',
                    'MA-BRR',
                    'MA-CAS',
                    'MA-CHT',
                    'MA-JDI',
                    'MA-MED',
                    'MA-MOH',
                    'MA-SET',
                    'MA-SIB',
                ],
                'MA-07' => [
                    'MA-CHI',
                    'MA-ESI',
                    'MA-HAO',
                    'MA-KES',
                    'MA-MAR',
                    'MA-REH',
                    'MA-SAF',
                    'MA-YUS',
                ],
                'MA-08' => [
                    'MA-ERR',
                    'MA-MID',
                    'MA-OUA',
                    'MA-TIN',
                    'MA-ZAG',
                ],
                'MA-09' => [
                    'MA-AGD',
                    'MA-INE',
                    'MA-TAR',
                    'MA-TAT',
                    'MA-TIZ',
                ],
                'MA-10' => [
                    'MA-ASZ',
                    'MA-GUE',
                    'MA-SIF',
                    'MA-TNT',
                ],
                'MA-11' => [
                    'MA-BOD',
                    'MA-ESM',
                    'MA-LAA',
                    'MA-TAF',
                ],
                'MA-12' => [
                    'MA-AOU',
                    'MA-OUD',
                ],
            ],
            'MW' => [
                'MW-C' => [
                    'MW-DE',
                    'MW-DO',
                    'MW-KS',
                    'MW-LI',
                    'MW-MC',
                    'MW-NK',
                    'MW-NU',
                    'MW-NI',
                    'MW-SA',
                ],
                'MW-N' => [
                    'MW-CT',
                    'MW-KR',
                    'MW-LK',
                    'MW-MZ',
                    'MW-NB',
                    'MW-RU',
                ],
                'MW-S' => [
                    'MW-BA',
                    'MW-BL',
                    'MW-CK',
                    'MW-CR',
                    'MW-MH',
                    'MW-MG',
                    'MW-MU',
                    'MW-MW',
                    'MW-NE',
                    'MW-NS',
                    'MW-PH',
                    'MW-TH',
                    'MW-ZO',
                ],
            ],
        ];

        if (isset($list[$countryCode])) {
            foreach ($list[$countryCode] as $geoRegionCode => $listSubdivision) {
                if (in_array($subdivisionCode, $listSubdivision)) {
                    return $geoRegionCode;
                }
            }
        }

        return null;
    }

    private function getSECLevelByHouseholdIncome(string $countryCode, string $householdIncome) {

        $list = [
            'MA' => [
                'A'  => [
                    'MAD40.001-50.000',
                    'MAD50.000+',
                ],
                'B'  => [
                    'MAD20.001-30.000',
                    'MAD30.001-40.000',
                ],
                'C1' => [
                    'MAD6.001-10.000',
                    'MAD10.001-20.000',
                ],
                'C2' => [
                    'MAD4.001-6.000',
                ],
                'D'  => [
                    'MAD2.001-4.000',
                ],
                'E'  => [
                    'MAD2.000-',
                ],
            ],
        ];

        if (isset($list[$countryCode])) {
            foreach ($list[$countryCode] as $secLevel => $listHouseholdIncome) {
                if (in_array($householdIncome, $listHouseholdIncome)) {
                    return $secLevel;
                }
            }
        }

        return null;
    }

    private function getSubdivisions(string $countryCode): array {

        $list = [
            'KE' => [
                ['id' => 1, 'value' => 'KE-01', 'label' => 'Baringo'],
                ['id' => 2, 'value' => 'KE-02', 'label' => 'Bomet'],
                ['id' => 3, 'value' => 'KE-03', 'label' => 'Bungoma'],
                ['id' => 4, 'value' => 'KE-04', 'label' => 'Busia'],
                ['id' => 5, 'value' => 'KE-05', 'label' => 'Elgeyo/Marakwet'],
                ['id' => 6, 'value' => 'KE-06', 'label' => 'Embu'],
                ['id' => 7, 'value' => 'KE-07', 'label' => 'Garissa'],
                ['id' => 8, 'value' => 'KE-08', 'label' => 'Homa Bay'],
                ['id' => 9, 'value' => 'KE-09', 'label' => 'Isiolo'],
                ['id' => 10, 'value' => 'KE-10', 'label' => 'Kajiado'],
                ['id' => 11, 'value' => 'KE-11', 'label' => 'Kakamega'],
                ['id' => 12, 'value' => 'KE-12', 'label' => 'Kericho'],
                ['id' => 13, 'value' => 'KE-13', 'label' => 'Kiambu'],
                ['id' => 14, 'value' => 'KE-14', 'label' => 'Kilifi'],
                ['id' => 15, 'value' => 'KE-15', 'label' => 'Kirinyaga'],
                ['id' => 16, 'value' => 'KE-16', 'label' => 'Kisii'],
                ['id' => 17, 'value' => 'KE-17', 'label' => 'Kisumu'],
                ['id' => 18, 'value' => 'KE-18', 'label' => 'Kitui'],
                ['id' => 19, 'value' => 'KE-19', 'label' => 'Kwale'],
                ['id' => 20, 'value' => 'KE-20', 'label' => 'Laikipia'],
                ['id' => 21, 'value' => 'KE-21', 'label' => 'Lamu'],
                ['id' => 22, 'value' => 'KE-22', 'label' => 'Machakos'],
                ['id' => 23, 'value' => 'KE-23', 'label' => 'Makueni'],
                ['id' => 24, 'value' => 'KE-24', 'label' => 'Mandera'],
                ['id' => 25, 'value' => 'KE-25', 'label' => 'Marsabit'],
                ['id' => 26, 'value' => 'KE-26', 'label' => 'Meru'],
                ['id' => 27, 'value' => 'KE-27', 'label' => 'Migori'],
                ['id' => 28, 'value' => 'KE-28', 'label' => 'Mombasa'],
                ['id' => 29, 'value' => 'KE-29', 'label' => 'Murang\'a'],
                ['id' => 30, 'value' => 'KE-30', 'label' => 'Nairobi City'],
                ['id' => 31, 'value' => 'KE-31', 'label' => 'Nakuru'],
                ['id' => 32, 'value' => 'KE-32', 'label' => 'Nandi'],
                ['id' => 33, 'value' => 'KE-33', 'label' => 'Narok'],
                ['id' => 34, 'value' => 'KE-34', 'label' => 'Nyamira'],
                ['id' => 35, 'value' => 'KE-35', 'label' => 'Nyandarua'],
                ['id' => 36, 'value' => 'KE-36', 'label' => 'Nyeri'],
                ['id' => 37, 'value' => 'KE-37', 'label' => 'Samburu'],
                ['id' => 38, 'value' => 'KE-38', 'label' => 'Siaya'],
                ['id' => 39, 'value' => 'KE-39', 'label' => 'Taita/Taveta'],
                ['id' => 40, 'value' => 'KE-40', 'label' => 'Tana River'],
                ['id' => 41, 'value' => 'KE-41', 'label' => 'Tharaka-Nithi'],
                ['id' => 42, 'value' => 'KE-42', 'label' => 'Trans Nzoia'],
                ['id' => 43, 'value' => 'KE-43', 'label' => 'Turkana'],
                ['id' => 44, 'value' => 'KE-44', 'label' => 'Uasin Gishu'],
                ['id' => 45, 'value' => 'KE-45', 'label' => 'Vihiga'],
                ['id' => 46, 'value' => 'KE-46', 'label' => 'Wajir'],
                ['id' => 47, 'value' => 'KE-47', 'label' => 'West Pokot'],
            ],
            'ZA' => [
                ['id' => 1, 'value' => 'ZA-EC', 'label' => 'Eastern Cape'],
                ['id' => 2, 'value' => 'ZA-FS', 'label' => 'Free State'],
                ['id' => 3, 'value' => 'ZA-GP', 'label' => 'Gauteng'],
                ['id' => 4, 'value' => 'ZA-KZN', 'label' => 'Kwazulu-Natal'],
                ['id' => 5, 'value' => 'ZA-LP', 'label' => 'Limpopo'],
                ['id' => 6, 'value' => 'ZA-MP', 'label' => 'Mpumalanga'],
                ['id' => 7, 'value' => 'ZA-NC', 'label' => 'Northern Cape'],
                ['id' => 8, 'value' => 'ZA-NW', 'label' => 'North-West'],
                ['id' => 9, 'value' => 'ZA-WC', 'label' => 'Western Cape'],
            ],
            'NG' => [
                ['id' => 1, 'value' => 'NG-FC', 'label' => 'Abuja Federal Capital Territory'],
                ['id' => 2, 'value' => 'NG-AB', 'label' => 'Abia'],
                ['id' => 3, 'value' => 'NG-AD', 'label' => 'Adamawa'],
                ['id' => 4, 'value' => 'NG-AK', 'label' => 'Akwa Ibom'],
                ['id' => 5, 'value' => 'NG-AN', 'label' => 'Anambra'],
                ['id' => 6, 'value' => 'NG-BA', 'label' => 'Bauchi'],
                ['id' => 7, 'value' => 'NG-BY', 'label' => 'Bayelsa'],
                ['id' => 8, 'value' => 'NG-BE', 'label' => 'Benue'],
                ['id' => 9, 'value' => 'NG-BO', 'label' => 'Borno'],
                ['id' => 10, 'value' => 'NG-CR', 'label' => 'Cross River'],
                ['id' => 11, 'value' => 'NG-DE', 'label' => 'Delta'],
                ['id' => 12, 'value' => 'NG-EB', 'label' => 'Ebonyi'],
                ['id' => 13, 'value' => 'NG-ED', 'label' => 'Edo'],
                ['id' => 14, 'value' => 'NG-EK', 'label' => 'Ekiti'],
                ['id' => 15, 'value' => 'NG-EN', 'label' => 'Enugu'],
                ['id' => 16, 'value' => 'NG-GO', 'label' => 'Gombe'],
                ['id' => 17, 'value' => 'NG-IM', 'label' => 'Imo'],
                ['id' => 18, 'value' => 'NG-JI', 'label' => 'Jigawa'],
                ['id' => 19, 'value' => 'NG-KD', 'label' => 'Kaduna'],
                ['id' => 20, 'value' => 'NG-KN', 'label' => 'Kano'],
                ['id' => 21, 'value' => 'NG-KT', 'label' => 'Katsina'],
                ['id' => 22, 'value' => 'NG-KE', 'label' => 'Kebbi'],
                ['id' => 23, 'value' => 'NG-KO', 'label' => 'Kogi'],
                ['id' => 24, 'value' => 'NG-KW', 'label' => 'Kwara'],
                ['id' => 25, 'value' => 'NG-LA', 'label' => 'Lagos'],
                ['id' => 26, 'value' => 'NG-NA', 'label' => 'Nasarawa'],
                ['id' => 27, 'value' => 'NG-NI', 'label' => 'Niger'],
                ['id' => 28, 'value' => 'NG-OG', 'label' => 'Ogun'],
                ['id' => 29, 'value' => 'NG-ON', 'label' => 'Ondo'],
                ['id' => 30, 'value' => 'NG-OS', 'label' => 'Osun'],
                ['id' => 31, 'value' => 'NG-OY', 'label' => 'Oyo'],
                ['id' => 32, 'value' => 'NG-PL', 'label' => 'Plateau'],
                ['id' => 33, 'value' => 'NG-RI', 'label' => 'Rivers'],
                ['id' => 34, 'value' => 'NG-SO', 'label' => 'Sokoto'],
                ['id' => 35, 'value' => 'NG-TA', 'label' => 'Taraba'],
                ['id' => 36, 'value' => 'NG-YO', 'label' => 'Yobe'],
                ['id' => 37, 'value' => 'NG-ZA', 'label' => 'Zamfara'],
            ],
            'UG' => [
                ['id' => 1, 'value' => 'UG-117', 'label' => 'Buikwe (district)'],
                ['id' => 2, 'value' => 'UG-118', 'label' => 'Bukomansibi (district)'],
                ['id' => 3, 'value' => 'UG-119', 'label' => 'Butambala (district)'],
                ['id' => 4, 'value' => 'UG-120', 'label' => 'Buvuma (district)'],
                ['id' => 5, 'value' => 'UG-121', 'label' => 'Gomba (district)'],
                ['id' => 6, 'value' => 'UG-101', 'label' => 'Kalangala (district)'],
                ['id' => 7, 'value' => 'UG-122', 'label' => 'Kalungu (district)'],
                ['id' => 8, 'value' => 'UG-102', 'label' => 'Kampala (city)'],
                ['id' => 9, 'value' => 'UG-126', 'label' => 'Kasanda (district)'],
                ['id' => 10, 'value' => 'UG-112', 'label' => 'Kayunga (district)'],
                ['id' => 11, 'value' => 'UG-103', 'label' => 'Kiboga (district)'],
                ['id' => 12, 'value' => 'UG-123', 'label' => 'Kyankwanzi (district)'],
                ['id' => 13, 'value' => 'UG-125', 'label' => 'Kyotera (district)'],
                ['id' => 14, 'value' => 'UG-104', 'label' => 'Luwero (district)'],
                ['id' => 15, 'value' => 'UG-124', 'label' => 'Lwengo (district)'],
                ['id' => 16, 'value' => 'UG-114', 'label' => 'Lyantonde (district)'],
                ['id' => 17, 'value' => 'UG-105', 'label' => 'Masaka (district)'],
                ['id' => 18, 'value' => 'UG-115', 'label' => 'Mityana (district)'],
                ['id' => 19, 'value' => 'UG-106', 'label' => 'Mpigi (district)'],
                ['id' => 20, 'value' => 'UG-107', 'label' => 'Mubende (district)'],
                ['id' => 21, 'value' => 'UG-108', 'label' => 'Mukono (district)'],
                ['id' => 22, 'value' => 'UG-116', 'label' => 'Nakaseke (district)'],
                ['id' => 23, 'value' => 'UG-109', 'label' => 'Nakasongola (district)'],
                ['id' => 24, 'value' => 'UG-110', 'label' => 'Rakai (district)'],
                ['id' => 25, 'value' => 'UG-111', 'label' => 'Sembabule (district)'],
                ['id' => 26, 'value' => 'UG-113', 'label' => 'Wakiso (district)'],
                ['id' => 27, 'value' => 'UG-216', 'label' => 'Amuria (district)'],
                ['id' => 28, 'value' => 'UG-217', 'label' => 'Budaka (district)'],
                ['id' => 29, 'value' => 'UG-218', 'label' => 'Bududa (district)'],
                ['id' => 30, 'value' => 'UG-201', 'label' => 'Bugiri (district)'],
                ['id' => 31, 'value' => 'UG-235', 'label' => 'Bugweri (district)'],
                ['id' => 32, 'value' => 'UG-219', 'label' => 'Bukedea (district)'],
                ['id' => 33, 'value' => 'UG-220', 'label' => 'Bukwa (district)'],
                ['id' => 34, 'value' => 'UG-225', 'label' => 'Bulambuli (district)'],
                ['id' => 35, 'value' => 'UG-202', 'label' => 'Busia (district)'],
                ['id' => 36, 'value' => 'UG-221', 'label' => 'Butaleja (district)'],
                ['id' => 37, 'value' => 'UG-233', 'label' => 'Butebo (district)'],
                ['id' => 38, 'value' => 'UG-226', 'label' => 'Buyende (district)'],
                ['id' => 39, 'value' => 'UG-203', 'label' => 'Iganga (district)'],
                ['id' => 40, 'value' => 'UG-204', 'label' => 'Jinja (district)'],
                ['id' => 41, 'value' => 'UG-213', 'label' => 'Kaberamaido (district)'],
                ['id' => 42, 'value' => 'UG-222', 'label' => 'Kaliro (district)'],
                ['id' => 43, 'value' => 'UG-205', 'label' => 'Kamuli (district)'],
                ['id' => 44, 'value' => 'UG-206', 'label' => 'Kapchorwa (district)'],
                ['id' => 45, 'value' => 'UG-236', 'label' => 'Kapelebyong (district)'],
                ['id' => 46, 'value' => 'UG-207', 'label' => 'Katakwi (district)'],
                ['id' => 47, 'value' => 'UG-227', 'label' => 'Kibuku (district)'],
                ['id' => 48, 'value' => 'UG-208', 'label' => 'Kumi (district)'],
                ['id' => 49, 'value' => 'UG-228', 'label' => 'Kween (district)'],
                ['id' => 50, 'value' => 'UG-229', 'label' => 'Luuka (district)'],
                ['id' => 51, 'value' => 'UG-223', 'label' => 'Manafwa (district)'],
                ['id' => 52, 'value' => 'UG-214', 'label' => 'Mayuge (district)'],
                ['id' => 53, 'value' => 'UG-209', 'label' => 'Mbale (district)'],
                ['id' => 54, 'value' => 'UG-230', 'label' => 'Namayingo (district)'],
                ['id' => 55, 'value' => 'UG-234', 'label' => 'Namisindwa (district)'],
                ['id' => 56, 'value' => 'UG-224', 'label' => 'Namutumba (district)'],
                ['id' => 57, 'value' => 'UG-231', 'label' => 'Ngora (district)'],
                ['id' => 58, 'value' => 'UG-210', 'label' => 'Pallisa (district)'],
                ['id' => 59, 'value' => 'UG-232', 'label' => 'Serere (district)'],
                ['id' => 60, 'value' => 'UG-215', 'label' => 'Sironko (district)'],
                ['id' => 61, 'value' => 'UG-211', 'label' => 'Soroti (district)'],
                ['id' => 62, 'value' => 'UG-212', 'label' => 'Tororo (district)'],
                ['id' => 63, 'value' => 'UG-314', 'label' => 'Abim (district)'],
                ['id' => 64, 'value' => 'UG-301', 'label' => 'Adjumani (district)'],
                ['id' => 65, 'value' => 'UG-322', 'label' => 'Agago (district)'],
                ['id' => 66, 'value' => 'UG-323', 'label' => 'Alebtong (district)'],
                ['id' => 67, 'value' => 'UG-315', 'label' => 'Amolatar (district)'],
                ['id' => 68, 'value' => 'UG-324', 'label' => 'Amudat (district)'],
                ['id' => 69, 'value' => 'UG-316', 'label' => 'Amuru (district)'],
                ['id' => 70, 'value' => 'UG-302', 'label' => 'Apac (district)'],
                ['id' => 71, 'value' => 'UG-303', 'label' => 'Arua (district)'],
                ['id' => 72, 'value' => 'UG-317', 'label' => 'Dokolo (district)'],
                ['id' => 73, 'value' => 'UG-304', 'label' => 'Gulu (district)'],
                ['id' => 74, 'value' => 'UG-318', 'label' => 'Kaabong (district)'],
                ['id' => 75, 'value' => 'UG-305', 'label' => 'Kitgum (district)'],
                ['id' => 76, 'value' => 'UG-319', 'label' => 'Koboko (district)'],
                ['id' => 77, 'value' => 'UG-325', 'label' => 'Kole (district)'],
                ['id' => 78, 'value' => 'UG-306', 'label' => 'Kotido (district)'],
                ['id' => 79, 'value' => 'UG-333', 'label' => 'Kwania (district)'],
                ['id' => 80, 'value' => 'UG-326', 'label' => 'Lamwo (district)'],
                ['id' => 81, 'value' => 'UG-307', 'label' => 'Lira (district)'],
                ['id' => 82, 'value' => 'UG-320', 'label' => 'Maracha (district)'],
                ['id' => 83, 'value' => 'UG-308', 'label' => 'Moroto (district)'],
                ['id' => 84, 'value' => 'UG-309', 'label' => 'Moyo (district)'],
                ['id' => 85, 'value' => 'UG-334', 'label' => 'Nabilatuk (district)'],
                ['id' => 86, 'value' => 'UG-311', 'label' => 'Nakapiripirit (district)'],
                ['id' => 87, 'value' => 'UG-327', 'label' => 'Napak (district)'],
                ['id' => 88, 'value' => 'UG-310', 'label' => 'Nebbi (district)'],
                ['id' => 89, 'value' => 'UG-328', 'label' => 'Nwoya (district)'],
                ['id' => 90, 'value' => 'UG-331', 'label' => 'Omoro (district)'],
                ['id' => 91, 'value' => 'UG-329', 'label' => 'Otuke (district)'],
                ['id' => 92, 'value' => 'UG-321', 'label' => 'Oyam (district)'],
                ['id' => 93, 'value' => 'UG-312', 'label' => 'Pader (district)'],
                ['id' => 94, 'value' => 'UG-332', 'label' => 'Pakwach (district)'],
                ['id' => 95, 'value' => 'UG-313', 'label' => 'Yumbe (district)'],
                ['id' => 96, 'value' => 'UG-330', 'label' => 'Zombo (district)'],
                ['id' => 97, 'value' => 'UG-420', 'label' => 'Buhweju (district)'],
                ['id' => 98, 'value' => 'UG-416', 'label' => 'Buliisa (district)'],
                ['id' => 99, 'value' => 'UG-401', 'label' => 'Bundibugyo (district)'],
                ['id' => 100, 'value' => 'UG-430', 'label' => 'Bunyangabu (district)'],
                ['id' => 101, 'value' => 'UG-402', 'label' => 'Bushenyi (district)'],
                ['id' => 102, 'value' => 'UG-403', 'label' => 'Hoima (district)'],
                ['id' => 103, 'value' => 'UG-417', 'label' => 'Ibanda (district)'],
                ['id' => 104, 'value' => 'UG-418', 'label' => 'Isingiro (district)'],
                ['id' => 105, 'value' => 'UG-404', 'label' => 'Kabale (district)'],
                ['id' => 106, 'value' => 'UG-405', 'label' => 'Kabarole (district)'],
                ['id' => 107, 'value' => 'UG-427', 'label' => 'Kagadi (district)'],
                ['id' => 108, 'value' => 'UG-428', 'label' => 'Kakumiro (district)'],
                ['id' => 109, 'value' => 'UG-413', 'label' => 'Kamwenge (district)'],
                ['id' => 110, 'value' => 'UG-414', 'label' => 'Kanungu (district)'],
                ['id' => 111, 'value' => 'UG-406', 'label' => 'Kasese (district)'],
                ['id' => 112, 'value' => 'UG-407', 'label' => 'Kibaale (district)'],
                ['id' => 113, 'value' => 'UG-432', 'label' => 'Kikuube (district)'],
                ['id' => 114, 'value' => 'UG-419', 'label' => 'Kiruhura (district)'],
                ['id' => 115, 'value' => 'UG-421', 'label' => 'Kiryandongo (district)'],
                ['id' => 116, 'value' => 'UG-408', 'label' => 'Kisoro (district)'],
                ['id' => 117, 'value' => 'UG-422', 'label' => 'Kyegegwa (district)'],
                ['id' => 118, 'value' => 'UG-415', 'label' => 'Kyenjojo (district)'],
                ['id' => 119, 'value' => 'UG-409', 'label' => 'Masindi (district)'],
                ['id' => 120, 'value' => 'UG-410', 'label' => 'Mbarara (district)'],
                ['id' => 121, 'value' => 'UG-423', 'label' => 'Mitooma (district)'],
                ['id' => 122, 'value' => 'UG-424', 'label' => 'Ntoroko (district)'],
                ['id' => 123, 'value' => 'UG-411', 'label' => 'Ntungamo (district)'],
                ['id' => 124, 'value' => 'UG-429', 'label' => 'Rubanda (district)'],
                ['id' => 125, 'value' => 'UG-425', 'label' => 'Rubirizi (district)'],
                ['id' => 126, 'value' => 'UG-431', 'label' => 'Rukiga (district)'],
                ['id' => 127, 'value' => 'UG-412', 'label' => 'Rukungiri (district)'],
                ['id' => 128, 'value' => 'UG-426', 'label' => 'Sheema (district)'],
            ],
            'GH' => [
                ['id' => 1, 'value' => 'GH-AF', 'label' => 'Ahafo'],
                ['id' => 2, 'value' => 'GH-AH', 'label' => 'Ashanti'],
                ['id' => 3, 'value' => 'GH-BO', 'label' => 'Bono'],
                ['id' => 4, 'value' => 'GH-BE', 'label' => 'Bono East'],
                ['id' => 5, 'value' => 'GH-CP', 'label' => 'Central'],
                ['id' => 6, 'value' => 'GH-EP', 'label' => 'Eastern'],
                ['id' => 7, 'value' => 'GH-AA', 'label' => 'Greater Accra'],
                ['id' => 8, 'value' => 'GH-NE', 'label' => 'North East'],
                ['id' => 9, 'value' => 'GH-NP', 'label' => 'Northern'],
                ['id' => 10, 'value' => 'GH-OT', 'label' => 'Oti'],
                ['id' => 11, 'value' => 'GH-SV', 'label' => 'Savannah'],
                ['id' => 12, 'value' => 'GH-UE', 'label' => 'Upper East'],
                ['id' => 13, 'value' => 'GH-UW', 'label' => 'Upper West'],
                ['id' => 14, 'value' => 'GH-TV', 'label' => 'Volta'],
                ['id' => 15, 'value' => 'GH-WP', 'label' => 'Western'],
                ['id' => 16, 'value' => 'GH-WN', 'label' => 'Western North'],
            ],
            'CI' => [
                ['id' => 1, 'value' => 'CI-AB', 'label' => 'Abidjan'],
                ['id' => 2, 'value' => 'CI-BS', 'label' => 'Bas-Sassandra'],
                ['id' => 3, 'value' => 'CI-CM', 'label' => 'Comoé'],
                ['id' => 4, 'value' => 'CI-DN', 'label' => 'Denguélé'],
                ['id' => 5, 'value' => 'CI-GD', 'label' => 'Gôh-Djiboua'],
                ['id' => 6, 'value' => 'CI-LC', 'label' => 'Lacs'],
                ['id' => 7, 'value' => 'CI-LG', 'label' => 'Lagunes'],
                ['id' => 8, 'value' => 'CI-MG', 'label' => 'Montagnes'],
                ['id' => 9, 'value' => 'CI-SM', 'label' => 'Sassandra-Marahoué'],
                ['id' => 10, 'value' => 'CI-SV', 'label' => 'Savanes'],
                ['id' => 11, 'value' => 'CI-VB', 'label' => 'Vallée du Bandama'],
                ['id' => 12, 'value' => 'CI-WR', 'label' => 'Woroba'],
                ['id' => 13, 'value' => 'CI-YM', 'label' => 'Yamoussoukro'],
                ['id' => 14, 'value' => 'CI-ZZ', 'label' => 'Zanzan'],
            ],
            'SN' => [
                ['id' => 1, 'value' => 'SN-DK', 'label' => 'Dakar'],
                ['id' => 2, 'value' => 'SN-DB', 'label' => 'Diourbel'],
                ['id' => 3, 'value' => 'SN-FK', 'label' => 'Fatick'],
                ['id' => 4, 'value' => 'SN-KA', 'label' => 'Kaffrine'],
                ['id' => 5, 'value' => 'SN-KL', 'label' => 'Kaolack'],
                ['id' => 6, 'value' => 'SN-KE', 'label' => 'Kédougou'],
                ['id' => 7, 'value' => 'SN-KD', 'label' => 'Kolda'],
                ['id' => 8, 'value' => 'SN-LG', 'label' => 'Louga'],
                ['id' => 9, 'value' => 'SN-MT', 'label' => 'Matam'],
                ['id' => 10, 'value' => 'SN-SL', 'label' => 'Saint-Louis'],
                ['id' => 11, 'value' => 'SN-SE', 'label' => 'Sédhiou'],
                ['id' => 12, 'value' => 'SN-TC', 'label' => 'Tambacounda'],
                ['id' => 13, 'value' => 'SN-TH', 'label' => 'Thiès'],
                ['id' => 14, 'value' => 'SN-ZG', 'label' => 'Ziguinchor'],
            ],
            'TZ' => [
                ['id' => 1, 'value' => 'TZ-01', 'label' => 'Arusha'],
                ['id' => 2, 'value' => 'TZ-02', 'label' => 'Dar es Salaam'],
                ['id' => 3, 'value' => 'TZ-03', 'label' => 'Dodoma'],
                ['id' => 4, 'value' => 'TZ-27', 'label' => 'Geita'],
                ['id' => 5, 'value' => 'TZ-04', 'label' => 'Iringa'],
                ['id' => 6, 'value' => 'TZ-05', 'label' => 'Kagera'],
                ['id' => 7, 'value' => 'TZ-28', 'label' => 'Katavi'],
                ['id' => 8, 'value' => 'TZ-08', 'label' => 'Kigoma'],
                ['id' => 9, 'value' => 'TZ-09', 'label' => 'Kilimanjaro'],
                ['id' => 10, 'value' => 'TZ-12', 'label' => 'Lindi'],
                ['id' => 11, 'value' => 'TZ-26', 'label' => 'Manyara'],
                ['id' => 12, 'value' => 'TZ-13', 'label' => 'Mara'],
                ['id' => 13, 'value' => 'TZ-14', 'label' => 'Mbeya'],
                ['id' => 14, 'value' => 'TZ-15', 'label' => 'Zanzibar West (Mjini Magharibi)'],
                ['id' => 15, 'value' => 'TZ-16', 'label' => 'Morogoro'],
                ['id' => 16, 'value' => 'TZ-17', 'label' => 'Mtwara'],
                ['id' => 17, 'value' => 'TZ-18', 'label' => 'Mwanza'],
                ['id' => 18, 'value' => 'TZ-29', 'label' => 'Njombe'],
                ['id' => 19, 'value' => 'TZ-06', 'label' => 'Pemba North'],
                ['id' => 20, 'value' => 'TZ-10', 'label' => 'Pemba South'],
                ['id' => 21, 'value' => 'TZ-19', 'label' => 'Coast (Pwani)'],
                ['id' => 22, 'value' => 'TZ-20', 'label' => 'Rukwa'],
                ['id' => 23, 'value' => 'TZ-21', 'label' => 'Ruvuma'],
                ['id' => 24, 'value' => 'TZ-22', 'label' => 'Shinyanga'],
                ['id' => 25, 'value' => 'TZ-30', 'label' => 'Simiyu'],
                ['id' => 26, 'value' => 'TZ-23', 'label' => 'Singida'],
                ['id' => 27, 'value' => 'TZ-31', 'label' => 'Songwe'],
                ['id' => 28, 'value' => 'TZ-24', 'label' => 'Tabora'],
                ['id' => 29, 'value' => 'TZ-25', 'label' => 'Tanga'],
                ['id' => 30, 'value' => 'TZ-07', 'label' => 'Zanzibar North (Unguja North)'],
                ['id' => 31, 'value' => 'TZ-11', 'label' => 'Zanzibar South (Unguja South)'],
            ],
            'MA' => [
                ['id' => 1, 'value' => 'MA-AGD', 'label' => 'Agadir-Ida-Ou-Tanane'],
                ['id' => 2, 'value' => 'MA-AOU', 'label' => 'Aousserd (EH)'],
                ['id' => 3, 'value' => 'MA-ASZ', 'label' => 'Assa-Zag (EH-partial)'],
                ['id' => 4, 'value' => 'MA-AZI', 'label' => 'Azilal'],
                ['id' => 5, 'value' => 'MA-BEM', 'label' => 'Béni Mellal'],
                ['id' => 6, 'value' => 'MA-BES', 'label' => 'Benslimane'],
                ['id' => 7, 'value' => 'MA-BER', 'label' => 'Berkane'],
                ['id' => 8, 'value' => 'MA-BRR', 'label' => 'Berrechid'],
                ['id' => 9, 'value' => 'MA-BOD', 'label' => 'Boujdour (EH)'],
                ['id' => 10, 'value' => 'MA-BOM', 'label' => 'Boulemane'],
                ['id' => 11, 'value' => 'MA-CAS', 'label' => 'Casablanca (Dar el Beïda)'],
                ['id' => 12, 'value' => 'MA-CHE', 'label' => 'Chefchaouen'],
                ['id' => 13, 'value' => 'MA-CHI', 'label' => 'Chichaoua'],
                ['id' => 14, 'value' => 'MA-CHT', 'label' => 'Chtouka-Ait Baha'],
                ['id' => 15, 'value' => 'MA-DRI', 'label' => 'Driouch'],
                ['id' => 16, 'value' => 'MA-ERR', 'label' => 'Errachidia'],
                ['id' => 17, 'value' => 'MA-ESM', 'label' => 'Es-Semara (EH-partial)'],
                ['id' => 18, 'value' => 'MA-ESI', 'label' => 'Essaouira'],
                ['id' => 19, 'value' => 'MA-FAH', 'label' => 'Fahs-Anjra'],
                ['id' => 20, 'value' => 'MA-FES', 'label' => 'Fès'],
                ['id' => 21, 'value' => 'MA-FIG', 'label' => 'Figuig'],
                ['id' => 22, 'value' => 'MA-FQH', 'label' => 'Fquih Ben Salah'],
                ['id' => 23, 'value' => 'MA-GUE', 'label' => 'Guelmim'],
                ['id' => 24, 'value' => 'MA-GUF', 'label' => 'Guercif'],
                ['id' => 25, 'value' => 'MA-HAJ', 'label' => 'El Hajeb'],
                ['id' => 26, 'value' => 'MA-HAO', 'label' => 'Al Haouz'],
                ['id' => 27, 'value' => 'MA-HOC', 'label' => 'Al Hoceïma'],
                ['id' => 28, 'value' => 'MA-IFR', 'label' => 'Ifrane'],
                ['id' => 29, 'value' => 'MA-INE', 'label' => 'Inezgane-Ait Melloul'],
                ['id' => 30, 'value' => 'MA-JDI', 'label' => 'El Jadida'],
                ['id' => 31, 'value' => 'MA-JRA', 'label' => 'Jerada'],
                ['id' => 32, 'value' => 'MA-KES', 'label' => 'El Kelâa des Sraghna'],
                ['id' => 33, 'value' => 'MA-KEN', 'label' => 'Kénitra'],
                ['id' => 34, 'value' => 'MA-KHE', 'label' => 'Khémisset'],
                ['id' => 35, 'value' => 'MA-KHN', 'label' => 'Khénifra'],
                ['id' => 36, 'value' => 'MA-KHO', 'label' => 'Khouribga'],
                ['id' => 37, 'value' => 'MA-LAA', 'label' => 'Laâyoune (EH)'],
                ['id' => 38, 'value' => 'MA-LAR', 'label' => 'Larache'],
                ['id' => 39, 'value' => 'MA-MAR', 'label' => 'Marrakech'],
                ['id' => 40, 'value' => 'MA-MDF', 'label' => 'M’diq-Fnideq'],
                ['id' => 41, 'value' => 'MA-MED', 'label' => 'Médiouna'],
                ['id' => 42, 'value' => 'MA-MEK', 'label' => 'Meknès'],
                ['id' => 43, 'value' => 'MA-MID', 'label' => 'Midelt'],
                ['id' => 44, 'value' => 'MA-MOH', 'label' => 'Mohammadia'],
                ['id' => 45, 'value' => 'MA-MOU', 'label' => 'Moulay Yacoub'],
                ['id' => 46, 'value' => 'MA-NAD', 'label' => 'Nador'],
                ['id' => 47, 'value' => 'MA-NOU', 'label' => 'Nouaceur'],
                ['id' => 48, 'value' => 'MA-OUA', 'label' => 'Ouarzazate'],
                ['id' => 49, 'value' => 'MA-OUD', 'label' => 'Oued Ed-Dahab (EH)'],
                ['id' => 50, 'value' => 'MA-OUZ', 'label' => 'Ouezzane'],
                ['id' => 51, 'value' => 'MA-OUJ', 'label' => 'Oujda-Angad'],
                ['id' => 52, 'value' => 'MA-RAB', 'label' => 'Rabat'],
                ['id' => 53, 'value' => 'MA-REH', 'label' => 'Rehamna'],
                ['id' => 54, 'value' => 'MA-SAF', 'label' => 'Safi'],
                ['id' => 55, 'value' => 'MA-SAL', 'label' => 'Salé'],
                ['id' => 56, 'value' => 'MA-SEF', 'label' => 'Sefrou'],
                ['id' => 57, 'value' => 'MA-SET', 'label' => 'Settat'],
                ['id' => 58, 'value' => 'MA-SIB', 'label' => 'Sidi Bennour'],
                ['id' => 59, 'value' => 'MA-SIF', 'label' => 'Sidi Ifni'],
                ['id' => 60, 'value' => 'MA-SIK', 'label' => 'Sidi Kacem'],
                ['id' => 61, 'value' => 'MA-SIL', 'label' => 'Sidi Slimane'],
                ['id' => 62, 'value' => 'MA-SKH', 'label' => 'Skhirate-Témara'],
                ['id' => 63, 'value' => 'MA-TNT', 'label' => 'Tan-Tan (EH-partial)'],
                ['id' => 64, 'value' => 'MA-TNG', 'label' => 'Tanger-Assilah'],
                ['id' => 65, 'value' => 'MA-TAO', 'label' => 'Taounate'],
                ['id' => 66, 'value' => 'MA-TAI', 'label' => 'Taourirt'],
                ['id' => 67, 'value' => 'MA-TAF', 'label' => 'Tarfaya (EH-partial)'],
                ['id' => 68, 'value' => 'MA-TAR', 'label' => 'Taroudannt'],
                ['id' => 69, 'value' => 'MA-TAT', 'label' => 'Tata'],
                ['id' => 70, 'value' => 'MA-TAZ', 'label' => 'Taza'],
                ['id' => 71, 'value' => 'MA-TET', 'label' => 'Tétouan'],
                ['id' => 72, 'value' => 'MA-TIN', 'label' => 'Tinghir'],
                ['id' => 73, 'value' => 'MA-TIZ', 'label' => 'Tiznit'],
                ['id' => 74, 'value' => 'MA-YUS', 'label' => 'Youssoufia'],
                ['id' => 75, 'value' => 'MA-ZAG', 'label' => 'Zagora'],
            ],
            'CM' => [
                ['id' => 1, 'value' => 'CM-AD', 'label' => __('screening.subdivision.CM-AD.label')],
                ['id' => 2, 'value' => 'CM-CE', 'label' => __('screening.subdivision.CM-CE.label')],
                ['id' => 3, 'value' => 'CM-ES', 'label' => __('screening.subdivision.CM-ES.label')],
                ['id' => 4, 'value' => 'CM-EN', 'label' => __('screening.subdivision.CM-EN.label')],
                ['id' => 5, 'value' => 'CM-LT', 'label' => __('screening.subdivision.CM-LT.label')],
                ['id' => 6, 'value' => 'CM-NO', 'label' => __('screening.subdivision.CM-NO.label')],
                ['id' => 7, 'value' => 'CM-NW', 'label' => __('screening.subdivision.CM-NW.label')],
                ['id' => 8, 'value' => 'CM-SU', 'label' => __('screening.subdivision.CM-SU.label')],
                ['id' => 9, 'value' => 'CM-SW', 'label' => __('screening.subdivision.CM-SW.label')],
                ['id' => 10, 'value' => 'CM-OU', 'label' => __('screening.subdivision.CM-OU.label')],
            ],
            'MW' => [
                ['id' => 1, 'value' => 'MW-BA', 'label' => 'Balaka'],
                ['id' => 2, 'value' => 'MW-BL', 'label' => 'Blantyre'],
                ['id' => 3, 'value' => 'MW-CK', 'label' => 'Chikwawa'],
                ['id' => 4, 'value' => 'MW-CR', 'label' => 'Chiradzulu'],
                ['id' => 5, 'value' => 'MW-CT', 'label' => 'Chitipa'],
                ['id' => 6, 'value' => 'MW-DE', 'label' => 'Dedza'],
                ['id' => 7, 'value' => 'MW-DO', 'label' => 'Dowa'],
                ['id' => 8, 'value' => 'MW-KR', 'label' => 'Karonga'],
                ['id' => 9, 'value' => 'MW-KS', 'label' => 'Kasungu'],
                ['id' => 10, 'value' => 'MW-LK', 'label' => 'Likoma'],
                ['id' => 11, 'value' => 'MW-LI', 'label' => 'Lilongwe'],
                ['id' => 12, 'value' => 'MW-MH', 'label' => 'Machinga'],
                ['id' => 13, 'value' => 'MW-MG', 'label' => 'Mangochi'],
                ['id' => 14, 'value' => 'MW-MC', 'label' => 'Mchinji'],
                ['id' => 15, 'value' => 'MW-MU', 'label' => 'Mulanje'],
                ['id' => 16, 'value' => 'MW-MW', 'label' => 'Mwanza'],
                ['id' => 17, 'value' => 'MW-MZ', 'label' => 'Mzimba'],
                ['id' => 18, 'value' => 'MW-NE', 'label' => 'Neno'],
                ['id' => 19, 'value' => 'MW-NB', 'label' => 'Nkhata Bay'],
                ['id' => 20, 'value' => 'MW-NK', 'label' => 'Nkhotakota'],
                ['id' => 21, 'value' => 'MW-NS', 'label' => 'Nsanje'],
                ['id' => 22, 'value' => 'MW-NU', 'label' => 'Ntcheu'],
                ['id' => 23, 'value' => 'MW-NI', 'label' => 'Ntchisi'],
                ['id' => 24, 'value' => 'MW-PH', 'label' => 'Phalombe'],
                ['id' => 25, 'value' => 'MW-RU', 'label' => 'Rumphi'],
                ['id' => 26, 'value' => 'MW-SA', 'label' => 'Salima'],
                ['id' => 27, 'value' => 'MW-TH', 'label' => 'Thyolo'],
                ['id' => 28, 'value' => 'MW-ZO', 'label' => 'Zomba'],
            ],
            'GA' => [
                ['id' => 1, 'value' => 'GA-1', 'label' => 'Estuaire'],
                ['id' => 2, 'value' => 'GA-2', 'label' => 'Haut-Ogooué'],
                ['id' => 3, 'value' => 'GA-3', 'label' => 'Moyen-Ogooué'],
                ['id' => 4, 'value' => 'GA-4', 'label' => 'Ngounié'],
                ['id' => 5, 'value' => 'GA-5', 'label' => 'Nyanga'],
                ['id' => 6, 'value' => 'GA-6', 'label' => 'Ogooué-Ivindo'],
                ['id' => 7, 'value' => 'GA-7', 'label' => 'Ogooué-Lolo'],
                ['id' => 8, 'value' => 'GA-8', 'label' => 'Ogooué-Maritime'],
                ['id' => 9, 'value' => 'GA-9', 'label' => 'Woleu-Ntem'],
            ],
            'AO' => [
                ['id' => 1, 'value' => 'AO-BGO', 'label' => 'Bengo'],
                ['id' => 2, 'value' => 'AO-BGU', 'label' => 'Benguela'],
                ['id' => 3, 'value' => 'AO-BIE', 'label' => 'Bié'],
                ['id' => 4, 'value' => 'AO-CAB', 'label' => 'Cabinda'],
                ['id' => 5, 'value' => 'AO-CNN', 'label' => 'Cunene'],
                ['id' => 6, 'value' => 'AO-HUA', 'label' => 'Huambo'],
                ['id' => 7, 'value' => 'AO-HUI', 'label' => 'Huíla'],
                ['id' => 8, 'value' => 'AO-CCU', 'label' => 'Cuando Cubango (Kuando Kubango)'],
                ['id' => 9, 'value' => 'AO-CNO', 'label' => 'Cuanza-Norte (Kwanza Norte)'],
                ['id' => 10, 'value' => 'AO-CUS', 'label' => 'Cuanza-Sul (Kwanza Sul)'],
                ['id' => 11, 'value' => 'AO-LUA', 'label' => 'Luanda'],
                ['id' => 12, 'value' => 'AO-LNO', 'label' => 'Lunda-Norte'],
                ['id' => 13, 'value' => 'AO-LSU', 'label' => 'Lunda-Sul'],
                ['id' => 14, 'value' => 'AO-MAL', 'label' => 'Malange'],
                ['id' => 15, 'value' => 'AO-MOX', 'label' => 'Moxico'],
                ['id' => 16, 'value' => 'AO-NAM', 'label' => 'Namibe'],
                ['id' => 17, 'value' => 'AO-UIG', 'label' => 'Uíge'],
                ['id' => 18, 'value' => 'AO-ZAI', 'label' => 'Zaire'],
            ],
            'EG' => [
                ['id' => 1, 'value' => 'EG-DK', 'label' => 'Dakahlia'],
                ['id' => 2, 'value' => 'EG-BA', 'label' => 'Red Sea'],
                ['id' => 3, 'value' => 'EG-BH', 'label' => 'Beheira'],
                ['id' => 4, 'value' => 'EG-FYM', 'label' => 'Faiyum'],
                ['id' => 5, 'value' => 'EG-GH', 'label' => 'Gharbia'],
                ['id' => 6, 'value' => 'EG-ALX', 'label' => 'Alexandria'],
                ['id' => 7, 'value' => 'EG-IS', 'label' => 'Ismailia'],
                ['id' => 8, 'value' => 'EG-GZ', 'label' => 'Giza'],
                ['id' => 9, 'value' => 'EG-MNF', 'label' => 'Monufia'],
                ['id' => 10, 'value' => 'EG-MN', 'label' => 'Minya'],
                ['id' => 11, 'value' => 'EG-C', 'label' => 'Cairo'],
                ['id' => 12, 'value' => 'EG-KB', 'label' => 'Qalyubia'],
                ['id' => 13, 'value' => 'EG-LX', 'label' => 'Luxor'],
                ['id' => 14, 'value' => 'EG-WAD', 'label' => 'New Valley'],
                ['id' => 15, 'value' => 'EG-SUZ', 'label' => 'Suez'],
                ['id' => 16, 'value' => 'EG-SHR', 'label' => 'Al Sharqia'],
                ['id' => 17, 'value' => 'EG-ASN', 'label' => 'Aswan'],
                ['id' => 18, 'value' => 'EG-AST', 'label' => 'Asyut'],
                ['id' => 19, 'value' => 'EG-BNS', 'label' => 'Beni Suef'],
                ['id' => 20, 'value' => 'EG-PTS', 'label' => 'Port Said'],
                ['id' => 21, 'value' => 'EG-DT', 'label' => 'Damietta'],
                ['id' => 22, 'value' => 'EG-JS', 'label' => 'South Sinai'],
                ['id' => 23, 'value' => 'EG-KFS', 'label' => 'Kafr el-Sheikh'],
                ['id' => 24, 'value' => 'EG-MT', 'label' => 'Matrouh'],
                ['id' => 25, 'value' => 'EG-KN', 'label' => 'Qena'],
                ['id' => 26, 'value' => 'EG-SIN', 'label' => 'North Sinai'],
                ['id' => 27, 'value' => 'EG-SHG', 'label' => 'Sohag'],
            ],
            'ET' => [
                ['id' => 1, 'value' => 'ET-AA', 'label' => 'Addis Ababa'],
                ['id' => 2, 'value' => 'ET-DD', 'label' => 'Dire Dawa'],
                ['id' => 3, 'value' => 'ET-AF', 'label' => 'Afar'],
                ['id' => 4, 'value' => 'ET-AM', 'label' => 'Amara'],
                ['id' => 5, 'value' => 'ET-BE', 'label' => 'Benshangul-Gumaz'],
                ['id' => 6, 'value' => 'ET-GA', 'label' => 'Gambela Peoples'],
                ['id' => 7, 'value' => 'ET-HA', 'label' => 'Harari People'],
                ['id' => 8, 'value' => 'ET-OR', 'label' => 'Oromia'],
                ['id' => 9, 'value' => 'ET-SI', 'label' => 'Sidama'],
                ['id' => 10, 'value' => 'ET-SO', 'label' => 'Somali'],
                ['id' => 11, 'value' => 'ET-TI', 'label' => 'Tigrai'],
                ['id' => 12, 'value' => 'ET-SN', 'label' => 'Southern Nations, Nationalities and Peoples'],
            ],
            'BW' => [
                ['id' => 1, 'value' => 'BW-CE', 'label' => 'Central'],
                ['id' => 2, 'value' => 'BW-CH', 'label' => 'Chobe'],
                ['id' => 3, 'value' => 'BW-FR', 'label' => 'Francistown'],
                ['id' => 4, 'value' => 'BW-GA', 'label' => 'Gaborone'],
                ['id' => 5, 'value' => 'BW-GH', 'label' => 'Ghanzi'],
                ['id' => 6, 'value' => 'BW-JW', 'label' => 'Jwaneng'],
                ['id' => 7, 'value' => 'BW-KG', 'label' => 'Kgalagadi'],
                ['id' => 8, 'value' => 'BW-KL', 'label' => 'Kgatleng'],
                ['id' => 9, 'value' => 'BW-KW', 'label' => 'Kweneng'],
                ['id' => 10, 'value' => 'BW-LO', 'label' => 'Lobatse'],
                ['id' => 11, 'value' => 'BW-NE', 'label' => 'North East'],
                ['id' => 12, 'value' => 'BW-NW', 'label' => 'North West'],
                ['id' => 13, 'value' => 'BW-SP', 'label' => 'Selibe Phikwe'],
                ['id' => 14, 'value' => 'BW-SE', 'label' => 'South East'],
                ['id' => 15, 'value' => 'BW-SO', 'label' => 'Southern'],
                ['id' => 16, 'value' => 'BW-ST', 'label' => 'Sowa Town'],
            ],
            'RW' => [
                ['id' => 1, 'value' => 'RW-01', 'label' => 'Kigali'],
                ['id' => 2, 'value' => 'RW-02', 'label' => 'Eastern'],
                ['id' => 3, 'value' => 'RW-03', 'label' => 'Northern'],
                ['id' => 4, 'value' => 'RW-04', 'label' => 'Western'],
                ['id' => 5, 'value' => 'RW-05', 'label' => 'Southern'],
            ],
        ];

        return $list[$countryCode] ?? [];
    }

    private function getCities(string $countryCode) {

        $list = [
            'MA' => [
                // IMPORTANT: label are in French language.
                ['id' => 1, 'value' => 'Tanger-Tetouan-Al Hoceima', 'label' => 'Tanger - Tétouan - Al Hoceima'],
                ['id' => 2, 'value' => 'Oriental', 'label' => 'L\'Oriental'],
                ['id' => 3, 'value' => 'Fes-Meknes', 'label' => 'Fès - Meknès'],
                ['id' => 4, 'value' => 'Rabat-Sale-Kenitra', 'label' => 'Rabat - Salé - Kénitra'],
                ['id' => 5, 'value' => 'Casablanca-Settat', 'label' => 'Casablanca- Settat'],
                ['id' => 6, 'value' => 'Marrakesh-Safi', 'label' => 'Marrakech - Safi'],
                ['id' => 7, 'value' => 'Souss-Massa', 'label' => 'Souss - Massa'],
                ['id' => 8, 'value' => 'other', 'label' => 'Autres'],
            ],
            'NG' => [
                ['id' => 1, 'value' => 'Lagos', 'label' => 'Lagos'],
                ['id' => 2, 'value' => 'Port Harcourt', 'label' => 'Port Harcourt'],
                ['id' => 3, 'value' => 'Abuja', 'label' => 'Abuja'],
                ['id' => 4, 'value' => 'Owerri', 'label' => 'Owerri'],
                ['id' => 5, 'value' => 'Benin/Sapele', 'label' => 'Benin/Sapele'],
                ['id' => 6, 'value' => 'Enugu', 'label' => 'Enugu'],
                ['id' => 7, 'value' => 'Kano', 'label' => 'Kano'],
                ['id' => 9999, 'value' => 'Other', 'label' => 'Other'],
                ['id' => 0, 'value' => 'Prefer not to say', 'label' => 'Prefer not to say'],
            ],
        ];

        return $list[$countryCode] ?? [];
    }

    /**
     * @param  string  $uuid
     *
     * @return Respondent|null
     */
    private function getRespondent(string $uuid) {

        // Check if exist
        /** @var Respondent|null $respondent * */
        $respondent = Respondent::query()
            ->where('uuid', $uuid)
            ->first();

        if ( ! $respondent) {
            Log::error('Could not find the respondent.', [
                'uuid' => $uuid,
            ]);

            return null;
        }

        return $respondent;
    }

    /**
     * @param  array  $targetIds
     * @param  Respondent  $respondent
     */
    private function checkQuotaQuota(array $targetIds, Respondent $respondent): void {

        if ($respondent->is_test) {
            return;
        }

        if (count(ProjectUtils::getOpenQuotas($respondent->project_code, $targetIds)) !== 0) {
            return;
        }

        $newRespondentStatus = RespondentStatus::QUOTA_FULL;
        $respondent->update([
            'current_status' => $newRespondentStatus,
            'status_history' => array_merge((array) $respondent->status_history, [
                $newRespondentStatus => date('Y-m-d H:i:s'),
            ]),
        ]);
    }

    /**
     * @param  string  $projectCode
     *
     * @return int
     */
    private function getRequiredTargetHits(string $projectCode): int {

        return ProjectUtils::getRequiredTargetHits($projectCode);
    }

    /**
     * @param  array  $attributes
     * @return int|null
     */
    private function getLSMLevel(array $attributes): ?int {

        $attributeWeight = [
            1  => 0.185224, // Hot running water from a geyser
            2  => 0.311118, // Computer - Desktop/Laptop
            3  => 0.16322, // Electric stove
            4  => -0.30133,
            // No domestic workers or household helpers in household (this includes live-in and part-time domestics and gardeners)
            5  => -0.245, // 0 or 1 radio set in household
            6  => 0.113306, // Flush toilet in/outside house
            7  => 0.16731, // Motor vehicle in household
            8  => 0.149009, // Washing machine
            9  => 0.134133, // Refrigerator or combined fridge/freezer
            10 => 0.164736, // Vacuum cleaner/floor polisher
            11 => 0.12736, // Pay TV (M-Net/DStv/TopTV) subscription
            12 => 0.212562, // Dishwashing machine
            13 => 0.184676, // 3 or more cellphones in household
            14 => 0.124007, // 2 cellphones in household
            15 => 0.151623, // Home security service
            16 => 0.116673, // Deep freezer - free standing
            17 => 0.126409, // Microwave oven
            18 => -0.12936, // Rural rest (excl. W Cape & Gauteng rural)
            19 => 0.113907, // House/cluster house/town house
            20 => 0.09607, // DVD player/Blu Ray Player
            21 => 0.166056, // Tumble dryer
            22 => 0.096072, // Home theatre system
            23 => 0.104531, // Home telephone (excl. cellphone)
            24 => 0.166031, // Swimming Pool
            25 => 0.123015, // Tap water in house/on plot
            26 => 0.132822, // Built-in kitchen sink
            27 => 0.120814, // TV set
            28 => 0.178044, // Air conditioner (excl. fans)
            29 => 0.079321, // Metropolitan dweller (250.000+)
        ];

        $levelWeightRanges = [
            1  => [-10, -1.39014],
            2  => [-1.390139, -1.242],
            3  => [-1.242001, -1.0118],
            4  => [-1.011801, -0.691],
            5  => [-0.691001, -0.278],
            6  => [-0.278001, 0.382],
            7  => [0.382001, 0.801],
            8  => [0.801001, 1.169],
            9  => [1.169001, 1.745],
            10 => [1.745001, 10],
        ];

        $hitAttributes = array_intersect_key($attributeWeight, array_flip($attributes));
        if (empty($hitAttributes)) {
            return null;
        }

        $constant = -0.810520;
        $totalWeight = array_sum($hitAttributes) + $constant;

        foreach ($levelWeightRanges as $level => $levelWeightRange) {
            if ($totalWeight >= $levelWeightRange[0] && $totalWeight <= $levelWeightRange[1]) {
                return $level;
            }
        }

        return null;
    }

    private function getSECLevelsRange(array $attributes): ?string {

        $attributeScore = [
            1  => 2, // Household help (domestic workers and/or gardeners)
            2  => 3, // Fridge/deep freezer
            3  => 1, // Video
            4  => 2, // Car
            5  => 1, // Colour TV
            6  => 1, // Music system
            7  => 4, // Air conditioning unit (split)
            8  => 3, // Air conditioning
            9  => 3, // Satellite dish
            10 => 4, // Washing machine
            11 => 1, // Black & White TV
            12 => 4, // DVD (Digital video disk)
            13 => 2, // Cable satellite
            14 => 3, // Telephone (land)
            15 => 1, // Telephone (mobile)
            16 => 2, // Personal driver
            17 => 3, // Multiple cars
            18 => 3, // Computer
            19 => 4, // Computer Laptop
            20 => 4, // Generator
            21 => 2, // Gas/Electric Cooker/stove
            22 => 1, // Kerosene stove
            23 => 0, // Charcoal/wood
            24 => 2, // Inside/Outside flush toilet/WC
            25 => 1, // Pit latrine
            26 => 0, // None
            27 => 3, // Inside
            28 => 3, // Outside pipe borne tap
            29 => 2, // Borehole
            30 => 1, // Well
            31 => 0, // Stream
            32 => 1, // Primary Incomplete
            33 => 1, // Primary complete
            34 => 1, // Secondary Incomplete
            35 => 2, // Secondary complete
            36 => 3, // University/Polytechnic: OND
            37 => 3, // University/Polytechnic: HND
            38 => 4, // Post-University Incomplete
            39 => 5, // Post University Complete
            40 => 0, // Illiterate/None
            41 => 3, // Low density
            42 => 2, // Medium density
            43 => 1, // High density
            44 => 2, // Self-occupied bungalow
            45 => 5, //  House and/or  Villa
            46 => 3, // Flat
            47 => 4, // Duplex
            48 => 2, // Mini flat
            49 => 1, // Room and parlour
            50 => 1, // Single Room
            51 => 5, // Senior Management/Admin.
            52 => 5, // Managing Director
            53 => 4, // Head of department/Senior Manager
            54 => 3, // Manager
            55 => 4, // Professional (white collar) e.g. Marketing Executive, Doctor, Lawyer, Engineers, etc.
            56 => 2, // Skilled workers (mechanics, tailoring, carpenters, bricklayers)
            57 => 1, // Unskilled workers
            58 => 2, // Clerical workers
            59 => 0, // Unemployed/student
            60 => 3, // Membership of social/recreational club
            61 => 4, // Travel abroad for holidays
            62 => 2, // Read regularly as a habit
            63 => 1, // Spend leisure time with friends
            64 => 1, // Attend social occasions
            65 => 1, // Like modern fashion
        ];

        $hitAttributes = array_intersect_key($attributeScore, array_flip($attributes));
        if (empty($hitAttributes)) {
            return null;
        }

        $totalScore = array_sum($hitAttributes);

        $levelsScoreRange = [
            'E'  => [0, 24],
            'D'  => [25, 34],
            'C2' => [35, 55],
            'C1' => [56, 69],
            'AB' => [70, 999],
        ];

        foreach ($levelsScoreRange as $level => $levelScoreRange) {
            if ($totalScore >= $levelScoreRange[0] && $totalScore <= $levelScoreRange[1]) {
                return $level;
            }
        }

        return null;
    }
}

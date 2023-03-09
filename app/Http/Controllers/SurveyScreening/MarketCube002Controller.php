<?php

namespace App\Http\Controllers\SurveyScreening;

use App\Constants\DataPointAttribute;
use App\Constants\RespondentStatus;
use App\DataPoint;
use App\Http\Controllers\Controller;
use App\Libraries\Project\ProjectUtils;
use App\Respondent;
use App\Target;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketCube002Controller extends Controller {

    /**
     * @param string $uuid
     * @return RedirectResponse|View
     */
    public function entry(string $uuid)
    {
        if ( ! $respondent = $this->checkRespondent($uuid)) {
            return redirect()->route('home');
        }

        if ( ! $this->checkProject($respondent->project_code)) {
            return redirect()->route('home');
        }

        $respondent->update([
            'current_status' => RespondentStatus::ENROLLING,
            'status_history' => array_merge($respondent->status_history, [
                RespondentStatus::ENROLLING => date('Y-m-d H:i:s'),
            ]),
        ]);

        $currentStep = session("customized_qualification.step", 1);
        if ( ! $questions = $this->getQuestions($currentStep)) {
            return redirect()->route('home');
        }

        // Generate unique question and answers per request.
        foreach ($questions as &$question) {
            $question['code'] = encrypt($question['id']);

            if ( ! isset($question['options'])) {
                continue;
            }

            foreach ($question['options'] as $key => $option) {
                $question['options'][$key]['code'] = encrypt($option['value']);
            }
        }
        unset($question);

        return view('audience_targeting.test_001', compact('uuid', 'questions'));
    }

    /**
     * @param string $uuid
     * @return RedirectResponse
     */
    public function handleAnswers(string $uuid): RedirectResponse
    {
        if ( ! $respondent = $this->checkRespondent($uuid)) {
            return redirect()->route('home');
        }

        if ( ! $projectConfigs = $this->checkProject($respondent->project_code)) {
            return redirect()->route('home');
        }

        $currentStep = session("customized_qualification.step", 1);
        if ( ! $questions = $this->getQuestions($currentStep)) {
            return redirect()->route('home');
        }

        // Validate passed data.
        $data = [];
        foreach (request()->input() as $code => $value) {
            if ($code === '_token') {
                continue;
            }

            $questionId = decrypt($code);
            if (isset($questions[$questionId]['options'])) {
                try {
                    $value = decrypt($value);
                } catch (\Exception $exception) {
                    Log::error('Could not decrypt screening question during audience targeting.',
                        compact('code', 'value'));

                    return redirect()->route('home');
                }
            }
            $data[$questionId] = $value;
        }

        $highestQuestionId = max(array_keys($questions));
        $validationRules = [];
        for ($i = 0; $i <= $highestQuestionId; $i++) {
            $validationRules[$i] = 'nullable'; // Is apparently checked if this element is present in this case.

            // Question exists, so needs validation rule.
            if ($question = $questions[$i] ?? null) {
                if ($question['type'] === 'single_choice') {
                    $validationRules[$i] = [
                        'required',
                        Rule::in(data_get($questions, ($i . '.options.*.value'))),
                    ];
                }
            }
        }
        Validator::make($data, $validationRules)->validate();

        $allAnswers = session("customized_qualification.answers", []) + $data;
        session()->put("customized_qualification.answers", $allAnswers);

        // Determine next step or end.
        $nextStep = $this->determineNextStep($currentStep, $allAnswers);
        if (is_int($nextStep)) {
            session()->put("customized_qualification.step", $nextStep);

            return redirect()->route($projectConfigs['configs']['customized_qualification'], ['uuid' => $uuid]);
        }

        // Determine hit targets and quotas.
        $targetHits = $this->getHitTargets($allAnswers, $respondent->person_id, $respondent->project_code);
        $targetIds = $targetHits->pluck('id')->toArray();
        $matchingQuotas = ProjectUtils::getOpenQuotas($respondent->project_code, $targetIds, false, true);

        $newRespondentStatus = ( ! empty($matchingQuotas) || $respondent->is_test)
            ? RespondentStatus::TARGET_SUITABLE
            : RespondentStatus::TARGET_UNSUITABLE;

        $respondent->update([
            'target_hits'    => $targetIds,
            'current_status' => $newRespondentStatus,
            'status_history' => array_merge((array)$respondent->status_history, [
                $newRespondentStatus => date('Y-m-d H:i:s'),
            ]),
        ]);

        // Handle reached quota, but only if all matching quotas are full.
        $newRespondentStatus = RespondentStatus::QUOTA_FULL;
        foreach ($matchingQuotas as $matchingQuota) {
            if ($newRespondentStatus === RespondentStatus::TARGET_UNSUITABLE) {
                continue;
            }

            if ($matchingQuota->count < $matchingQuota->quota_amount) {
                $newRespondentStatus = null;
            }

            if ($newRespondentStatus === null) {
                continue;
            }

            $respondent->update([
                'current_status' => $newRespondentStatus,
                'status_history' => array_merge((array)$respondent->status_history, [
                    $newRespondentStatus => date('Y-m-d H:i:s'),
                ]),
            ]);
        }

        session()->forget("customized_qualification.answers");

        return redirect()->route('invitation.entry', ['uuid' => $uuid]);
    }

    /**
     * @param array  $answers
     * @param        $personId
     * @param string $projectCode
     * @return Collection
     */
    private function getHitTargets(array $answers, $personId, string $projectCode): Collection
    {
        $countryCode = $this->getProjectConfigs($projectCode)['targets']['country'][0] ?? null;

        $list = [
            'country'          => [
                [
                    'value' => $countryCode,
                    'isHit' => function () use ($personId, $countryCode) {
                        return DataPoint::query()
                            ->where('person_id', $personId)
                            ->where('attribute', DataPointAttribute::COUNTRY_CODE)
                            ->where('value', $countryCode)
                            ->exists();
                    },
                ],
            ],
            'expectant_mother' => [
                [
                    'value' => true,
                    'isHit' => function () use ($answers) {
                        if ( ! isset($answers[1]) || ! isset($answers[3])) {
                            return false;
                        }

                        if ($answers[1] !== 'w') {
                            return false;
                        }

                        if ($answers[3] !== true) {
                            return false;
                        }

                        return true;
                    },
                ],
            ],
            'parent'           => [
                [
                    'value' => true,
                    'isHit' => function () use ($answers) {
                        if ( ! isset($answers[2]) || ! isset($answers[4])) {
                            return false;
                        }

                        if ($answers[2] !== true) {
                            return false;
                        }

                        if ( ! in_array($answers[4], [2, 3, 4, 5, 6, 7])) {
                            return false;
                        }

                        return true;
                    },
                ],
            ],
            'children_age'     => [
                [
                    'value' => '0-5',
                    'isHit' => function () use ($answers) {
                        if ( ! isset($answers[2]) || ! isset($answers[4]) || ! isset($answers[5])) {
                            return false;
                        }

                        if ($answers[2] !== true) {
                            return false;
                        }

                        if ( ! in_array($answers[4], [1, 2, 3, 4, 5, '6+'])) {
                            return false;
                        }

                        if ( ! in_array($answers[5], [1, 2, 3, 4, 5, 6])) {
                            return false;
                        }

                        return true;
                    },
                ], [
                    'value' => '6-15',
                    'isHit' => function () use ($answers) {
                        if ( ! isset($answers[2]) || ! isset($answers[4]) || ! isset($answers[5])) {
                            return false;
                        }

                        if ($answers[2] !== true) {
                            return false;
                        }

                        if ( ! in_array($answers[4], [1, 2, 3, 4, 5, '6+'])) {
                            return false;
                        }

                        if ( ! in_array($answers[5], [7, 8, 9, 10, 11, 12, 13, 14, 15, 16])) {
                            return false;
                        }

                        return true;
                    },
                ],
            ],
        ];

        return Target::query()
            ->where('project_code', $projectCode)
            ->where(function (Builder $query) use ($list) {
                foreach ($list as $criteria => $criteriaValues) {
                    foreach ($criteriaValues as $valueParams) {
                        if ( ! $valueParams['isHit']()) {
                            continue;
                        }

                        $value = $valueParams['value'];
                        $query->orWhere(function (Builder $query) use ($criteria, $value) {
                            $query->where('criteria', $criteria);
                            $query->where('value', $value);
                        });
                    }
                }
            })->get();
    }

    /**
     * @param       $currentStep
     * @param array $allAnswers
     * @return null|int
     */
    private function determineNextStep($currentStep, array $allAnswers): ?int
    {
        switch ($currentStep) {
            case 1:
                // Expectant mothers.
                if ($allAnswers[1] === 'w' && $allAnswers[3] === true && $allAnswers[2] === false) {
                    return null;
                }

                // Parents.
                if ($allAnswers[2] === true) {
                    return 2;
                }

                break;

            case 2:
                // Make sure one or more children present.
                if (in_array($allAnswers[4], [2, 3, 4, 5, 6, 7])) {
                    return 3;
                }

                break;

            case 3:
                // Youngest child needs to be between 0 and 16 years old.
                if (in_array($allAnswers[5], [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16])) {
                    return null;
                }

                break;
        }

        return null;
    }

    /**
     * @param int|string $step
     * @return array[]|null
     */
    private function getQuestions($step): ?array
    {
        $questions = [
            1 => [
                [
                    'id'       => 1,
                    'type'     => 'single_choice',
                    'question' => 'Are you...?',
                    'options'  => [
                        ['id' => 1, 'value' => 'm', 'label' => 'Man'],
                        ['id' => 2, 'value' => 'w', 'label' => 'Woman'],
                    ],
                ], [
                    'id'       => 2,
                    'type'     => 'single_choice',
                    'question' => 'Do you have children?',
                    'options'  => [
                        ['id' => 1, 'value' => true, 'label' => 'Yes'],
                        ['id' => 2, 'value' => false, 'label' => 'No'],
                    ],
                ], [
                    'id'       => 3,
                    'type'     => 'single_choice',
                    'question' => 'Are you or your wife pregnant and expecting a child?',
                    'options'  => [
                        ['id' => 1, 'value' => true, 'label' => 'Yes'],
                        ['id' => 2, 'value' => false, 'label' => 'No'],
                    ],
                ],
            ],
            2 => [
                [
                    'id'       => 4,
                    'type'     => 'single_choice',
                    'question' => 'How many children do you have?',
                    'options'  => [
                        ['id' => 1, 'value' => 0, 'label' => 'None'],
                        ['id' => 2, 'value' => 1, 'label' => 1],
                        ['id' => 3, 'value' => 2, 'label' => 2],
                        ['id' => 4, 'value' => 3, 'label' => 3],
                        ['id' => 5, 'value' => 4, 'label' => 4],
                        ['id' => 6, 'value' => 5, 'label' => 5],
                        ['id' => 7, 'value' => '6+', 'label' => '6 or more'],
                    ],
                ],
            ],
            3 => [
                [
                    'id'       => 5,
                    'type'     => 'single_choice',
                    'question' => 'How old is your youngest child?',
                    'options'  => [
                        ['id' => 1, 'value' => 1, 'label' => 'Not yet 1 year old'],
                        ['id' => 2, 'value' => 2, 'label' => '1 year old'],
                        ['id' => 3, 'value' => 3, 'label' => '2 years old'],
                        ['id' => 4, 'value' => 4, 'label' => '3 years old'],
                        ['id' => 5, 'value' => 5, 'label' => '4 years old'],
                        ['id' => 6, 'value' => 6, 'label' => '5 years old'],
                        ['id' => 7, 'value' => 7, 'label' => '6 years old'],
                        ['id' => 8, 'value' => 8, 'label' => '7 years old'],
                        ['id' => 9, 'value' => 9, 'label' => '8 years old'],
                        ['id' => 10, 'value' => 10, 'label' => '9 years old'],
                        ['id' => 11, 'value' => 11, 'label' => '10 years old'],
                        ['id' => 12, 'value' => 12, 'label' => '11 years old'],
                        ['id' => 13, 'value' => 13, 'label' => '12 years old'],
                        ['id' => 14, 'value' => 14, 'label' => '13 years old'],
                        ['id' => 15, 'value' => 15, 'label' => '14 years old'],
                        ['id' => 16, 'value' => 16, 'label' => '15 years old'],
                        ['id' => 17, 'value' => 17, 'label' => '16 years old'],
                        ['id' => 18, 'value' => 18, 'label' => '17 years old'],
                        ['id' => 19, 'value' => '18+', 'label' => '18 years or older'],
                    ],
                ],
            ],
        ];

        if ( ! $foundStepQuestions = $questions[$step] ?? null) {
            return null;
        }

        $result = [];
        foreach ($foundStepQuestions as $question) {
            $result[$question['id']] = $question;
        }

        return $result;
    }

    /**
     * @param string $uuid
     *
     * @return Respondent|null
     */
    private function getRespondent(string $uuid): ?Respondent
    {
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
     * @param string $projectCode
     * @return array|null
     */
    private function checkProject(string $projectCode): ?array
    {
        if ( ! $configs = $this->getProjectConfigs($projectCode)) {
            return null;
        }

        if ( ! $this->projectAccessible($configs)) {
            return null;
        }

        return $configs;
    }

    /**
     * @param string $projectCode
     * @return array|null
     */
    private function getProjectConfigs(string $projectCode): ?array
    {
        return ProjectUtils::getConfigs($projectCode);
    }

    /**
     * @param array $configs
     * @return bool
     */
    private function projectAccessible(array $configs): bool
    {
        if ( ! $configs['live']) {
            return false;
        }

        if ( ! isset($configs['configs'])) {
            return false;
        }

        if ( ! isset($configs['configs']['customized_qualification'])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $uuid
     * @return Respondent|null
     */
    private function checkRespondent(string $uuid): ?Respondent
    {
        $respondent = $this->getRespondent($uuid);
        if ( ! $respondent) {
            return null;
        }

        if (in_array($respondent->current_status, [
            RespondentStatus::TARGET_UNSUITABLE,
            RespondentStatus::CLOSED,
            RespondentStatus::COMPLETED,
            RespondentStatus::DISQUALIFIED,
            RespondentStatus::QUOTA_FULL,
        ])) {
            return null;
        }

        return $respondent;
    }
}

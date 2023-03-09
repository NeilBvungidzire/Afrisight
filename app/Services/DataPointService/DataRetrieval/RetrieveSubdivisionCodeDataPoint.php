<?php

namespace App\Services\DataPointService\DataRetrieval;

use App\MemberProfilingAnswer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RetrieveSubdivisionCodeDataPoint extends RetrieveDataPointBase {

    /**
     * @param int|string $personId
     * @param bool       $fresh
     * @return string|null
     */
    public function getValue($personId, bool $fresh): ?string
    {
        if ( ! $profilingDataPointQuestions = $this->getProfilingDataPointQuestions($fresh)) {
            return null;
        }

        $ids = Arr::pluck($profilingDataPointQuestions, 'id');
        if (empty($ids)) {
            return null;
        }

        if ( ! $answer = $this->getProfilingAnswer($personId, $ids)) {
            return null;
        }

        // Retrieve related question
        if ( ! $relatedQuestion = $profilingDataPointQuestions[$answer['profiling_question_id']] ?? null) {
            return null;
        }

        // Make sure the related question has minimal one answer option.
        if (empty($relatedQuestion['answer_params'])) {
            return null;
        }

        // Retrieve related answer params
        if ( ! $questionOptionId = $answer['answers'][0] ?? null) {
            return null;
        }

        // Try to retrieve the data point value from the given answer.
        foreach ($relatedQuestion['answer_params'] as $questionAnswerParam) {
            if ($questionAnswerParam['uuid'] !== $questionOptionId) {
                continue;
            }

            if (isset($questionAnswerParam['datapoint_value'])) {
                return $questionAnswerParam['datapoint_value'];
            }
        }

        return null;
    }

    /**
     * @param       $personId
     * @param array $ids
     * @return array|null
     */
    public function getProfilingAnswer($personId, array $ids): ?array
    {
        $exist = DB::table('member_profiling_answers')
            ->where('person_id', $personId)
            ->whereIn('profiling_question_id', $ids)
            ->whereNull('deleted_at')
            ->exists();

        if ( ! $exist) {
            return null;
        }

        $answer = MemberProfilingAnswer::query()
            ->where('person_id', $personId)
            ->whereIn('profiling_question_id', $ids)
            ->first([
                'profiling_question_id',
                'answers',
                'other_answer',
            ]);

        if ( ! $answer) {
            return null;
        }

        // Expect only one answer
        if (count($answer->answers) !== 1) {
            return null;
        }

        return $answer->toArray();
    }
}

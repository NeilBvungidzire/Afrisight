<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MemberProfilingAnswer
 *
 * @package App
 * @property int    $id
 * @property int    $profiling_question_id
 * @property int    $person_id
 * @property array  $answers
 * @property string $other_answer
 * @property string $data_point_attribute
 * @property string $data_point_values
 */
class MemberProfilingAnswer extends Model {

    use SoftDeletes;

    protected $fillable = [
        'profiling_question_id',
        'person_id',
        'answers',
        'other_answer',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    // ------------------------------------------------------------------------
    // Relations
    //

    /**
     * @return BelongsTo
     */
    public function profilingQuestion(): BelongsTo
    {
        return $this->belongsTo(ProfilingQuestion::class, 'profiling_question_id');
    }

    /**
     * @return BelongsTo
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    // ------------------------------------------------------------------------
    // Mutators
    //

    /**
     * Get related data point attribute.
     *
     * @return string|null
     */
    public function getDataPointAttributeAttribute(): ?string
    {
        if ( ! $this->profiling_question_id) {
            return null;
        }

        if ( ! $relatedProfilingQuestion = $this->getRelatedProfilingQuestion($this->profiling_question_id)) {
            return null;
        }

        return $relatedProfilingQuestion['datapoint_identifier'] ?? null;
    }

    /**
     * Get related data point value.
     *
     * @return array|null
     */
    public function getDataPointValuesAttribute(): ?array
    {
        if ( ! $this->profiling_question_id) {
            return null;
        }

        if ( ! $relatedProfilingQuestion = $this->getRelatedProfilingQuestion($this->profiling_question_id)) {
            return null;
        }

        $answerParams = $relatedProfilingQuestion['answer_params'] ?? null;
        if ( ! $answerParams) {
            return null;
        }

        $values = [];
        foreach ($this->answers as $answer) {
            foreach ($answerParams as $answerParam) {
                if ( ! isset($answerParam['datapoint_value'])) {
                    continue;
                }

                if ($answer === $answerParam['uuid']) {
                    $values[] = $answerParam['datapoint_value'];
                }
            }
        }

        return $values;
    }

    // ------------------------------------------------------------------------
    // Custom methods
    //

    /**
     * @param $profilingQuestionId
     * @return array|null
     */
    private function getRelatedProfilingQuestion($profilingQuestionId): ?array
    {
        if ( ! $profilingQuestionId) {
            return null;
        }

        $cacheKey = "PROFILING_QUESTION_${profilingQuestionId}";
        try {
            $profilingQuestions = cache()->remember($cacheKey, now()->addMonth(), static function () use ($profilingQuestionId) {
                return ProfilingQuestion::query()
                    ->where('id', $profilingQuestionId)
                    ->whereNotNull('datapoint_identifier')
                    ->first([
                        'answer_params',
                        'datapoint_identifier',
                    ]);
            });
        } catch (Exception $exception) {
            return null;
        }

        return $profilingQuestions ? $profilingQuestions->toArray() : null;
    }
}

<?php

namespace App\Http\Controllers;

use App\MemberProfilingAnswer;
use App\ProfilingQuestion;
use App\Questionnaire\Question;
use App\Questionnaire\QuestionType;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use App\Libraries\ProfilingQuestionnaire\ProfilingQuestionnaire;

class MemberProfilingController extends Controller {

    /**
     * @return View
     */
    public function index() {
        $person = auth()->user()->person;
        $answerIdsFromCache = ProfilingQuestionnaire::getCache($person->id);
        $profilingAnswers = MemberProfilingAnswer::query()
            ->where('person_id', $person->id)
            ->when( ! empty($answerIdsFromCache), function($query) use ($answerIdsFromCache) {
                $query->whereIn('id', $answerIdsFromCache);
            })
            ->get();
        if (empty($answerIdsFromCache)) {
            ProfilingQuestionnaire::handleCache($person->id, ProfilingQuestionnaire::ADD, $profilingAnswers->pluck('id')->toArray());
        }

        $questions = ProfilingQuestion::all();

        $questionList = [];

        foreach ($questions as $question) {
            // Check if conditions is met.
            if (isset($question->conditions['country_id']) && (int) $question->conditions['country_id'] !== (int) $person->country_id) {
                continue;
            }

            $questionClass = new Question($question->title, $question->type, $question->uuid);
            $questionClass->setSettings($question->settings);

            try {
                $questionClass->setAnswerParams($question->answer_params);
            } catch (Exception $exception) {
                Log::error('Empty answer params value', ['question' => $question->toArray()]);
            }

            $previousAnswersColumn = [];
            if ($previousAnswer = $profilingAnswers->firstWhere('profiling_question_id', $question->id)) {
                $previousAnswersColumn = $previousAnswer->answers;
            }
            $questionClass->generateFormData($previousAnswersColumn);

            $questionList[$question->uuid] = $questionClass;
        }

        return view('questionnaire.index', compact('questionList'));
    }

    /**
     * @return RedirectResponse
     */
    public function store() {
        $allData = request()->all();
        $questions = ProfilingQuestion::all();
        $availableQuestionUuids = $questions->keyBy('uuid');

        $newAndValidQuestionsByUuid = new Collection();
        $changedQuestionsIds = [];
        foreach ($allData as $uuid => $data) {
            if (isset($availableQuestionUuids[$uuid]) && $this->isValid($availableQuestionUuids[$uuid], $data)) {
                $newAndValidQuestionsByUuid->add($availableQuestionUuids[$uuid]);
                $changedQuestionsIds[] = $availableQuestionUuids[$uuid]->id;
            }
        }

        $person = auth()->user()->person;
        $answerIdsFromCache = ProfilingQuestionnaire::getCache($person->id);
        $previousAnswers = MemberProfilingAnswer::query()
            ->where('person_id', $person->id)
            ->whereIn('profiling_question_id', $changedQuestionsIds)
            ->when( ! empty($answerIdsFromCache), function($query) use ($answerIdsFromCache) {
                $query->whereIn('id', $answerIdsFromCache);
            })
            ->get();
        if (empty($answerIdsFromCache)) {
            ProfilingQuestionnaire::handleCache($person->id, ProfilingQuestionnaire::ADD, $previousAnswers->pluck('id')->toArray());
        }

        $delete = [];
        $new = [];
        foreach ($newAndValidQuestionsByUuid as $updatedQuestion) {
            $previousAnswer = $previousAnswers->firstWhere('profiling_question_id', $updatedQuestion->id);

            $newAnswers = (array) $allData[$updatedQuestion->uuid];

            if ( ! $previousAnswer) {
                $new[] = [
                    'profiling_question_id' => $updatedQuestion->id,
                    'answers'               => $newAnswers,
                ];
            } elseif (json_encode($previousAnswer->answers) !== json_encode($newAnswers)) {
                $delete[] = $previousAnswer->id;
                $new[] = [
                    'profiling_question_id' => $updatedQuestion->id,
                    'answers'               => $newAnswers,
                ];
            }
        }

        DB::transaction(static function () use ($new, $delete, $person) {
            MemberProfilingAnswer::whereIn('id', $delete)->delete();
            $person->profilingAnswers()->createMany($new);
        });

        return redirect()->route('profile.surveys');
    }

    /**
     * @param  ProfilingQuestion  $profilingQuestion
     * @param $data
     *
     * @return bool
     */
    private function isValid(ProfilingQuestion $profilingQuestion, $data): bool {
        if (empty($data)) {
            return false;
        }

        switch ($profilingQuestion->type) {
            case QuestionType::DROPDOWN:
            case QuestionType::MULTIPLE_CHOICE:
                $available = Collection::make($profilingQuestion->answer_params);

                return $available->pluck('uuid')->contains($data);
            case QuestionType::CHECKBOXES:
                $available = Collection::make($profilingQuestion->answer_params);
                $intersect = $available->pluck('uuid')->intersect($data);

                return count($intersect) === count($data);
            case QuestionType::SINGLE_TEXT_BOX:
                return true;
        }

        return false;
    }
}

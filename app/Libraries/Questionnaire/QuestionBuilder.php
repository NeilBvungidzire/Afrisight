<?php

namespace App\Libraries\Questionnaire;

use App\Libraries\Questionnaire\Exceptions\UnknownQuestionType;

class QuestionBuilder {

    use FormElementBuilder;

    /**
     * @var string
     */
    private $questionType;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $answerSettings;

    /**
     * @var array|null
     */
    private $settings;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var array|null
     */
    private $filledAnswers;

    /**
     * Question constructor.
     *
     * @param string $uuid
     * @param string $questionType
     * @param string $title
     * @param array $answerSettings
     * @param array|null $settings
     * @param array $filledAnswers
     *
     * @throws UnknownQuestionType
     */
    public function __construct(
        string $uuid,
        string $questionType,
        string $title,
        array $answerSettings,
        array $settings = null,
        array $filledAnswers = null
    ) {
        if ( ! QuestionType::checkType($questionType)) {
            throw new UnknownQuestionType('Passed question type does not exist.');
        }
        $this->questionType = $questionType;

        $this->uuid = $uuid;
        $this->title = $title;
        $this->answerSettings = $answerSettings;
        $this->settings = $settings;
        $this->filledAnswers = $filledAnswers;

        return $this;
    }

    /**
     * @return array|null
     */
    public function generateQuestionInput()
    {
        switch ($this->questionType) {

            case QuestionType::MULTIPLE_CHOICE:
                return $this->generateMultipleChoiceQuestionInput();
        }

        return null;
    }

    /**
     * @return array
     */
    private function generateAnswerOptions()
    {
        switch ($this->questionType) {

            case QuestionType::MULTIPLE_CHOICE:
                return $this->generateOptionsForMultipleChoiceQuestion();
        }
    }

    /**
     * @return array
     */
    private function generateOptionsForMultipleChoiceQuestion()
    {
        $answer = null;
        if ($this->filledAnswers && isset($this->filledAnswers[0]['answers'][0])) {
            $answer = $this->filledAnswers[0]['answers'][0];
        }

        $options = [];
        foreach ($this->answerSettings as $setting) {
            $options[] = [
                'value'   => $setting['value'],
                'label'   => $setting['label'],
                'checked' => ($answer && $answer === $setting['value']),
            ];
        }

        return $options;
    }

    /**
     * @return string
     */
    private function getFieldType()
    {
        if ($this->questionType === QuestionType::MULTIPLE_CHOICE) {
            return 'radio';
        }
    }
}

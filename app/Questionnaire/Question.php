<?php

namespace App\Questionnaire;

use App\Questionnaire\FormData\CheckboxesFormData;
use App\Questionnaire\FormData\DropdownFormData;
use App\Questionnaire\FormData\MultipleChoiceFormData;
use App\Questionnaire\FormData\SingleTextBoxFormData;
use Exception;
use Illuminate\Support\Str;

class Question {

    use CheckboxesFormData, DropdownFormData, MultipleChoiceFormData, SingleTextBoxFormData;

    /**
     * @var null
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $publicId;

    /**
     * @var bool
     */
    public $isRequired;

    /**
     * @var array
     */
    public $answerParams;

    /**
     * @var string
     */
    public $viewTemplate;

    /**
     * @var array
     */
    public $formData;

    /**
     * Question constructor.
     *
     * @param string $title
     * @param string $type
     * @param string $publicId
     */
    public function __construct(
        string $title,
        string $type,
        string $publicId
    ) {
        $this->title = $title;
        $this->type = $type;
        $this->publicId = $publicId;

        $this->setViewTemplate();
    }

    public function setSettings(array $settings)
    {
        $this->isRequired = isset($settings['isRequired']) ? $settings['isRequired'] : false;
    }

    /**
     * @param array $answerParams
     *
     * @throws Exception
     */
    public function setAnswerParams(array $answerParams)
    {
        if (empty($answerParams)) {
            throw new Exception('Answer params can not be empty');
        }

        switch ($this->type) {
            case QuestionType::DROPDOWN:
            case QuestionType::CHECKBOXES:
            case QuestionType::MULTIPLE_CHOICE:
                foreach ($answerParams as $answerParam) {
                    $this->answerParams[$answerParam['uuid']] = [
                        'uuid'  => $answerParam['uuid'],
                        'label' => $answerParam['label'],
                    ];
                }
                break;
            case QuestionType::SINGLE_TEXT_BOX:
                $this->answerParams = [
                    'value_type' => $answerParams['valueType'],
                ];
                break;
            default:
        }
    }

    public function setValue(array $values)
    {
        //
    }

    public function generateFormData(array $initialValue = [])
    {
        $this->{'generate' . self::getMethodNamePart($this->type) . 'FormData'}($initialValue);
    }

    private function setViewTemplate()
    {
        switch ($this->type) {
            case QuestionType::DROPDOWN:
                $this->viewTemplate = 'questionnaire.dropdown';
                break;
            case QuestionType::CHECKBOXES:
                $this->viewTemplate = 'questionnaire.checkboxes';
                break;
            case QuestionType::MULTIPLE_CHOICE:
                $this->viewTemplate = 'questionnaire.multiple-choice';
                break;
            case QuestionType::SINGLE_TEXT_BOX:
                $this->viewTemplate = 'questionnaire.single-text-box';
                break;
            default:
                $this->viewTemplate = '';
        }
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private static function getMethodNamePart(string $type)
    {
        return Str::studly(strtolower($type));
    }
}

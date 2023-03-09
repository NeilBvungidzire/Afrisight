<?php

namespace App\Libraries\Questionnaire;

trait FormElementBuilder {

    /**
     * @return array
     */
    private function generateMultipleChoiceQuestionInput()
    {
        $input = [
            'view' => config('questionnaire.question_types.multiple_choice.view'),
            'title' => $this->title,
            'options' => [],
        ];

        $commonAttributes = [
            'type' => $this->getFieldType(),
            'name' => $this->uuid,
        ];

        $isRequired = isset($this->settings['required']) && $this->settings['required'];
        if ($isRequired) {
            $commonAttributes['required'] = true;
            $input['required_message'] = __('questionnaire.is-required-field');
        }

        foreach ($this->generateAnswerOptions() as $option) {
            $fieldId = "{$this->uuid}-{$option['value']}";

            $optionAttributes = [
                'value' => $option['value'],
                'id'    => $fieldId,
            ];
            if ($option['checked']) {
                $optionAttributes['checked'] = true;
            }

            $optionInput = [
                'input' => [
                    'attributes' => htmlAttributes(array_merge($commonAttributes, $optionAttributes)),
                ],
                'label' => [
                    'for' => $fieldId,
                    'text' => $option['label'],
                ],
            ];

            $input['options'][] = $optionInput;
        }

        return $input;
    }
}

<?php

namespace App\Questionnaire\FormData;

trait CheckboxesFormData {

    private function generateCheckboxesFormData(array $initialValue = [])
    {
        $result = [
            'options' => [],
        ];

        foreach ($this->answerParams as $answerParam) {
            $result['options'][] = [
                'value'   => $answerParam['uuid'],
                'label'   => $answerParam['label'],
                'checked' => in_array($answerParam['uuid'], $initialValue),
                'name'    => $this->publicId . '[' . $answerParam['uuid'] . ']',
            ];
        }

        $this->formData = $result;
    }
}

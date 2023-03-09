<?php

namespace App\Questionnaire\FormData;

trait DropdownFormData {

    private function generateDropdownFormData(array $initialValue = [])
    {
        $result = [
            'name'    => $this->publicId,
            'options' => [],
        ];

        foreach ($this->answerParams as $answerParam) {
            $result['options'][] = [
                'value'    => $answerParam['uuid'],
                'label'    => $answerParam['label'],
                'selected' => in_array($answerParam['uuid'], $initialValue),
            ];
        }

        $this->formData = $result;
    }
}

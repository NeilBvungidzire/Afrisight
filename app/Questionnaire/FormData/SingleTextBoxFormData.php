<?php

namespace App\Questionnaire\FormData;

trait SingleTextBoxFormData {

    private function generateSingleTextBoxFormData(array $initialValue = [])
    {
        $result = [
            'name'  => $this->publicId,
            'value' => $initialValue[0] ?? '',
        ];

        switch ($this->answerParams['value_type']) {
            case 'NUMBER':
                $result['type'] = 'number';
                break;
            case 'TEXT':
                $result['type'] = 'text';
                break;
        }

        $this->formData = $result;
    }
}

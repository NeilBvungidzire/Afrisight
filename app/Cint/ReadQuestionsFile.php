<?php

namespace App\Cint;

use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class ReadQuestionsFile {

    /**
     * @var array|null
     */
    private $data = null;

    /**
     * ReadQuestionsFile constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $file = Storage::get($filePath);
        $xml = new SimpleXMLElement($file);

        $data = [];
        foreach ($xml->sss as $sss) {

            // Set categories
            $categoryId = (int)$sss->survey->record->attributes('cint', true)->{'category-id'};
            $data[$categoryId] = [
                'category_id' => $categoryId,
                'category_title' => (string)trim($sss->survey->title),
            ];

            $data[$categoryId]['variables'] = [];
            foreach ($sss->survey->record->variable as $variable) {

                // Set answers
                $values = [];
                foreach ($variable->values->value as $value) {
                    $valueId = (int)$value->attributes('cint', true)->{'variable-id'};
                    $values[$valueId] = [
                        'value_id' => $valueId,
                        'value_label' => (string)trim($value),
                    ];
                }

                // Set questions
                $variableId = (int)$variable->attributes()->ident;
                $data[$categoryId]['variables'][$variableId] = [
                    'variable_id' => $variableId,
                    'variable_name' => (string)trim($variable->name),
                    'variable_label' => (string)trim($variable->label),
                    'values' => $values,
                ];
            }
        }

        $this->data = $data;
    }

    /**
     * @return array|null
     */
    public function getArray()
    {
        return $this->data;
    }
}

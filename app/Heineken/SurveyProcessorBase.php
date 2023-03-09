<?php

namespace App\Heineken;

use App\Imports\HeinekenSurveyResultsImport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

abstract class SurveyProcessorBase {

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var array
     */
    private $keyReference;

    /**
     * SurveyProcessorBase constructor.
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;

        $this->keyReference = $this->getPreparedKeyReferences($this->setKeyReference());
    }

    /**
     * @return array
     */
    abstract protected function setKeyReference(): array;

    /**
     * @param array $surveyResult
     *
     * @return array
     */
    abstract protected function prepareResult(array $surveyResult): array;

    /**
     * @return array
     */
    public function getData()
    {
        $preparedData = $this->prepareQuestions();
        $results = [];
        foreach ($preparedData as $surveyResult) {
            if ($surveyResult['status'] !== 'Complete') {
                continue;
            }

            $surveyResult['country'] = $surveyResult['country'] ?? 'Sierra Leone';
            if ($surveyResult['country'] !== 'Sierra Leone') {
                continue;
            }

            if ( ! $result = $this->prepareResult($surveyResult)) {
                continue;
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * @return array
     */
    protected function prepareQuestions()
    {
        ini_set('memory_limit','512M');
        $data = $this->retrieveData();
        $this->getLabelledCells($data);

        $results = [];
        foreach ($data as $index => $item) {
            $tempData = [];
            foreach ($item as $key => $record) {
                $this->prepareQuestion($key, $record, $tempData);
            }
            $results[] = $tempData;
            unset($data[$index]);
        }
        unset($tempData);

        return $results;
    }

    /**
     * @param string $identifier
     * @param array $surveyResult
     * @param callable $callback
     *
     * @return array
     */
    protected function handleQuestion(string $identifier, array &$surveyResult, callable $callback)
    {
        $surveyResult[$identifier] = call_user_func($callback, $surveyResult[$identifier], $surveyResult, $identifier);

        return $surveyResult;
    }

    /**
     * @param int $key
     * @param array $record
     * @param array $data
     */
    private function prepareQuestion(int $key, array $record, array &$data)
    {
        foreach ($this->keyReference as $reference => $range) {
            if (in_array($key, $range)) {
                if (count($range) === 1) {
                    $data[$reference] = $record['value'];
                } else {
                    $data[$reference][] = $record['value'];
                }
            }
        }
    }

    /**
     * @param array $keyReference
     *
     * @return array
     */
    private function getPreparedKeyReferences(array $keyReference)
    {
        $results = [];
        foreach ($keyReference as $label => $keyRange) {
            if (is_int($keyRange)) {
                $results[$label][] = $keyRange;
            } elseif (is_array($keyRange)) {
                foreach ($keyRange as $range) {
                    if (is_int($range)) {
                        $results[$label][] = $range;
                    } elseif (is_array($range)) {
                        $results[$label] = array_merge($results[$label] ?? [], range($range[0], $range[1]));
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param array $data
     */
    private function getLabelledCells(array &$data)
    {
        $rawColumnNames = $data[0];
        unset($data[0]);

        foreach ($data as $indexLevel0 => &$rawRecord) {
            foreach ($rawRecord as $indexLevel1 => &$value) {
                $value = [
                    'column' => $rawColumnNames[$indexLevel1],
                    'value'  => $value,
                ];
            }
        }
    }

    /**
     * @return array
     */
    private function retrieveData()
    {
        $result = Excel::toArray(new HeinekenSurveyResultsImport, $this->filePath);

        if ( ! array_key_exists(0, $result)) {
            return [];
        }

        return $result[0];
    }
}

<?php

namespace App\Heineken;

use App\Imports\HeinekenSurveyResultsImport;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class GenerateOverview {

    /**
     * @var string[]
     */
    private $foundDevices = [];

    /**
     * @var string[]
     */
    private $foundCountries = [];

    /**
     * @var string[]
     */
    private $foundCities = [];

    /**
     * @var string[]
     */
    private $foundDates = [];

    /**
     * @var array
     */
    private $surveyImports;

    /**
     * GenerateOverview constructor.
     *
     * @param array $surveyImports
     */
    public function __construct(array $surveyImports)
    {
        $this->surveyImports = $surveyImports;
    }

    /**
     * @return array
     */
    public function generateOverview()
    {
        $data = $this->getProcessedData();

        return $this->groupByDate($data);
    }

    /**
     * @return array
     */
    public function getFoundDates()
    {
        return array_keys($this->foundDates);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function groupByDate(array $data)
    {
        $dates = array_keys($this->foundDates);
        // Sort date ascending.
        usort($dates, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });
        $this->foundDates = array_fill_keys($dates, null);

        $result = [];
        $totals = [
            'by_status'  => [
                'complete'     => 0,
                'incomplete'   => 0,
                'disqualified' => 0,
            ],
            'by_country' => array_fill_keys(array_keys($this->foundCountries), array_fill_keys($dates, 0)),
            'by_city'    => array_fill_keys(array_keys($this->foundCities), array_fill_keys($dates, 0)),
            'by_device'  => array_fill_keys(array_keys($this->foundDevices), array_fill_keys($dates, 0)),
            'dates'      => $dates,
        ];

        foreach ($data as $surveyKey => $surveyResults) {
            $surveyTotals = [
                'complete'     => 0,
                'incomplete'   => 0,
                'disqualified' => 0,
            ];

            $result[$surveyKey] = [];

            foreach ($dates as $date) {
                $result[$surveyKey][$date] = [
                    'results'    => [],
                    'by_status'  => [
                        'complete'     => 0,
                        'incomplete'   => 0,
                        'disqualified' => 0,
                    ],
                    'by_country' => array_fill_keys(array_keys($this->foundCountries), 0),
                    'by_city'    => array_fill_keys(array_keys($this->foundCities), 0),
                    'by_device'  => array_fill_keys(array_keys($this->foundDevices), 0),
                ];
            }

            foreach ($surveyResults as $surveyResult) {
                $result[$surveyKey][$surveyResult['result_date']]['results'][] = $surveyResult;

                // By device (total)
                $totals['by_device'][$surveyResult['device']][$surveyResult['result_date']]++;
                // By device (per date)
                $result[$surveyKey][$surveyResult['result_date']]['by_device'][$surveyResult['device']]++;

                // Count country (total)
                $totals['by_country'][$surveyResult['country']][$surveyResult['result_date']]++;

                // Count country (per date)
                $result[$surveyKey][$surveyResult['result_date']]['by_country'][$surveyResult['country']]++;

                // Count city
                $totals['by_city'][$surveyResult['city']][$surveyResult['result_date']]++;

                switch ($surveyResult['status']) {
                    case 'Complete':
                        $totals['by_status']['complete']++;
                        $surveyTotals['complete']++;
                        $result[$surveyKey][$surveyResult['result_date']]['by_status']['complete']++;
                        break;
                    case 'Partial':
                        $totals['by_status']['incomplete']++;
                        $surveyTotals['incomplete']++;
                        $result[$surveyKey][$surveyResult['result_date']]['by_status']['incomplete']++;
                        break;
                    case 'Disqualified':
                        $totals['by_status']['disqualified']++;
                        $surveyTotals['disqualified']++;
                        $result[$surveyKey][$surveyResult['result_date']]['by_status']['disqualified']++;
                        break;
                }
            }

            $result[$surveyKey]['totals'] = $surveyTotals;
        }

        $result = array_merge($result, $totals);

        return $result;
    }

    /**
     * @return array[]
     */
    private function getProcessedData()
    {
        $results = [];
        foreach ($this->surveyImports as $surveyKey => $filePath) {
            $rawData = Excel::toCollection(new HeinekenSurveyResultsImport, $filePath)
                ->get(0)
                ->forget(0)
                ->toArray();

            $results[$surveyKey] = $this->processData($rawData);
        }

        return $results;
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    private function processData(array $rawData)
    {
        $processedData = [];
        foreach ($rawData as $row) {
            $country = $row[15] ?? 'Sierra Leone';
            $city = $row[16];
            $resultDate = (new Carbon($row[2]))->format('d-m-Y');

            $processedData[$row[0]] = [
                'id'          => $row[0],
                'started_at'  => $row[1],
                'ended_at'    => $row[2],
                'result_date' => $resultDate,
                'status'      => $row[3],
                'user_agent'  => $row[10],
                'ip'          => $row[12],
                'lon'         => $row[13],
                'lat'         => $row[14],
                'country'     => $country,
                'city'        => $row[16],
                'device'      => null,
            ];

            $foundDeviceData = $this->getDeviceByUserAgentValue($row[10]);
            $device = array_keys($this->getDeviceByUserAgentValue($row[10]))[0];
            $processedData[$row[0]]['device'] = $device;

            $this->foundCountries[$country] = null;
            $this->foundCities[$city] = null;
            $this->foundDevices[$device] = $foundDeviceData[$device];
            $this->foundDates[$resultDate] = null;
        }

        return $processedData;
    }

    /**
     * @param string $userAgentValue
     *
     * @return string[]
     */
    private function getDeviceByUserAgentValue(string $userAgentValue)
    {
        // Check for iPhone
        if (strpos($userAgentValue, 'iPhone') !== false) {
            return ['iPhone' => 'iPhone'];
        }

        // Check for Android
        if (strpos($userAgentValue, 'Android') !== false) {
            $matches = [];
            preg_match('/(SM-[A-Z0-9]+)/', $userAgentValue, $matches);

            if (isset($matches[0])) {
                return [$matches[0] => 'Android (' . $matches[0] . ')'];
            }
        }

        return ['Other' => 'Other'];
    }
}

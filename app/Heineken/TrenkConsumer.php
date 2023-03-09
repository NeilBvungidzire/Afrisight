<?php

namespace App\Heineken;

class TrenkConsumer extends SurveyProcessorBase {

    private $consumptionFrequency = [
        1 => 'Every day',
        2 => '4 to 5 times in a week',
        3 => '2 To 3 times in a week',
        4 => 'Once in a week',
        5 => '2 or 3 times in a month',
        6 => 'Less than once in a month',
        7 => 'Not very often',
        8 => 'Never',
    ];

    /**
     * @param array $surveyResult
     *
     * @return array
     */
    protected function prepareResult(array $surveyResult): array
    {
        $this->handleQuestion('SR5', $surveyResult, function ($questionData, $surveyResult) {
            $transformedAnswer = [];

            foreach ($questionData as $key => $value) {
                if ( ! $surveyResult['SR4'][$key]) {
                    continue;
                }

                $transformedAnswer[$surveyResult['SR4'][$key]] = $this->consumptionFrequency[$value];
            }

            return $transformedAnswer;
        });

        $this->handleQuestion('Q10_c', $surveyResult, function ($questionData) {
            $labels = [
                0 => 'Maltina',
                1 => 'Vicco Malt',
                2 => 'Trenk',
                3 => 'Others Q10_a',
                4 => 'Others Q10_b',
            ];

            $transformedAnswer = [];
            foreach ($questionData as $key => $value) {
                if ( ! $value) {
                    continue;
                }

                $transformedAnswer[$labels[$key]] = $this->consumptionFrequency[$value];
            }

            return $transformedAnswer;
        });

        foreach (['Q10_f', 'Q10_g', 'Q10_h'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult, $identifier) {
                $labels = array_values(array_filter($surveyResult['MD1']));
                $values = array_values(array_filter($questionData));

                $results = [];
                foreach ($labels as $index => $label) {
                    if ( ! isset($values[$index])) {
                        continue;
                    }

                    $value = (in_array($identifier, ['Q10_g', 'Q10_h']) && is_string($values[$index]))
                        ? 0
                        : $values[$index];

                    $results[$label] = $value;
                }

                return $results;
            });
        }

        $this->handleQuestion('Q11_c', $surveyResult, function ($questionData, $surveyResult) {
            $labels = [
                0  => 'Bitter Kola Energizer',
                1  => 'Bum Bum',
                2  => 'Dragon',
                3  => 'Parrot',
                4  => '3X',
                5  => 'Mega Energy',
                6  => 'Red bull',
                7  => 'Sting',
                8  => 'Trenk',
                9  => 'Vandam',
                10 => $surveyResult['Q11_a'][1],
                11 => $surveyResult['Q11_b'][11],
            ];

            $transformedAnswer = [];
            foreach ($questionData as $key => $value) {
                if ( ! $value) {
                    continue;
                }

                $transformedAnswer[$labels[$key]] = $this->consumptionFrequency[$value];
            }

            return $transformedAnswer;
        });

        foreach (['Q11_f', 'Q11_g', 'Q11_h'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult, $identifier) {
                $labels = array_values(array_filter($surveyResult['MD2']));
                $values = array_values(array_filter($questionData));

                $results = [];
                foreach ($labels as $index => $label) {
                    if ( ! isset($values[$index])) {
                        continue;
                    }

                    $value = (in_array($identifier, ['Q11_g', 'Q11_h']) && is_string($values[$index]))
                        ? 0
                        : $values[$index];

                    $results[$label] = $value;
                }

                return $results;
            });
        }

        foreach (['QO11_a', 'Q14'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                return $questionData[0] ?? $questionData[1] ?? 'No opinion';
            });
        }

        $this->handleQuestion('MD3', $surveyResult, function ($questionData) {
            $transformedAnswer = [];
            if (in_array('Malt drink', $questionData)) {
                $transformedAnswer[] = 'Malt';
            }
            if (in_array('Energy drink', $questionData) || in_array('Energy Malt', $questionData)) {
                $transformedAnswer[] = 'Energy';
            }

            return $transformedAnswer;
        });

        $questionsList = ['Q16_a_1', 'Q16_b_1', 'Q17_a_1', 'Q17_b_1', 'Q16_a_2', 'Q16_b_2', 'Q17_a_2', 'Q17_b_2'];
        foreach ($questionsList as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult, $identifier) {
                $mapping = [
                    // Malt
                    'Q16_a_1' => 'QM10_b_2',
                    'Q16_b_1' => 'QM10_b_2',
                    'Q17_a_1' => 'QM10_c_2',
                    'Q17_b_1' => 'QM10_c_2',
                    // Energy
                    'Q16_a_2' => 'QM11_b_2',
                    'Q16_b_2' => 'QM11_b_2',
                    'Q17_a_2' => 'QM11_c_2',
                    'Q17_b_2' => 'QM11_c_2',
                ];

                $labels = array_values(array_filter($surveyResult[$mapping[$identifier]]));
                $values = array_values(array_filter($questionData));

                $results = [];
                foreach ($labels as $index => $label) {
                    if ( ! isset($values[$index])) {
                        continue;
                    }

                    if (is_string($values[$index])) {
                        preg_match('/\d+/', $values[$index], $numbers);
                        $value = (int)$numbers[0] ?? null;
                    } else {
                        $value = $values[$index];
                    }

                    $results[$label] = $value;
                }

                return $results;
            });
        }

        $this->handleQuestion('Q24', $surveyResult, function ($questionData) {
            $labels = [
                0 => 'It is unique and different from other products of this type',
                1 => 'It will meet my needs',
                2 => 'Is relevant to me',
            ];

            $results = [];
            foreach ($questionData as $index => $answer) {
                if ($labels[$index]) {
                    $results[$labels[$index]] = $answer;
                }
            }

            return $results;
        });

        // Remove all null value array elements.
        foreach ($surveyResult as $identifier => $values) {
            if ( ! is_array($values)) {
                continue;
            }

            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                $filteredArray = array_filter($questionData, function ($var) {
                    return $var !== null;
                });

                if (empty($filteredArray)) {
                    return null;
                }

                // If sequential array, reset the index.
                if (is_int(array_key_first($filteredArray))) {
                    return array_values($filteredArray);
                }

                return $filteredArray;
            });
        }

        // Elastic preps
        foreach ($surveyResult as $identifier => $values) {
            if ( ! is_array($values)) {
                continue;
            }

            if (array_keys($values) === range(0, count($values) - 1)) {
                continue;
            }

            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                $result = [];

                foreach ($questionData as $label => $value) {
                    $result[] = [
                        'label' => $label,
                        'value' => $value,
                    ];
                }

                return $result;
            });
        }

        return $surveyResult;
    }

    protected function setKeyReference(): array
    {
        return [
            // Meta data
            'respondent_id'   => 0,
            'time_started'    => 1,
            'time_submitted'  => 2,
            'status'          => 3,
            'user_agent'      => 10,
            'ip_address'      => 12,
            'country'         => 15,
            // Screening
            'SR1'             => [[19, 29]],
            'SR2'             => 30,
            'SR3'             => 31,
            'SR4'             => [[32, 45]],
            'SR5'             => [[46, 59]],
            // Demographics
            'Q1'              => 60,
            'Q2'              => 61,
            'Q3_a'            => 62,
            'Q3_b'            => 63,
            'Q4'              => 64,
            'Q5'              => 65,
            'Q6'              => 66,
            'Q7'              => 67,
            'Q9'              => 68,
            'Q10'             => 69,
            // Survey questions
            'Q10_a'           => [70, 71],
            'Q10_b'           => [[72, 77]],
            'Q10_c'           => [[78, 82]],
            'MD1'             => [[83, 96]],
            'Q10_d'           => 97,
            'Q10_e'           => 98,
            'Q10_f'           => [[99, 106]],
            'Q10_g'           => [[107, 114]],
            'Q10_h'           => [[115, 120]],
            'QM10_a'          => [[121, 148]],
            'QM10_b_1'        => [[149, 172]],
            'QM10_c_1'        => [[173, 189]],
            'QO10_a'          => [190, 191],
            'Q11_a'           => [192, 193],
            'Q11_b'           => [[194, 203], 205, 206],
            'Q11_c'           => [[207, 218]],
            'MD2'             => [[220, 229]],
            'Q11_d'           => 232,
            'Q11_e'           => 233,
            'Q11_f'           => [[234, 253]],
            'Q11_g'           => [254, 256, 258, 260, 262, 264, 266, 268, 270, 272],
            'Q11_h'           => [[275, 284]],
            'QM11_a'          => [[285, 312]],
            'QM11_b_1'        => [[313, 335]],
            'QM11_c_1'        => [[336, 352]],
            'QO11_a'          => [353, 354],
            'Q12'             => 355,
            'Q13'             => 356,
            'Q14'             => [357, 358],
            'Q15'             => 359,
            'MD3'             => [[361, 371]],
            // Trenk classified as Malt
            'QM10_b_2'        => [[372, 395]],
            'QM10_c_2'        => [[396, 412]],
            // Trenk classified as Energy
            'QM11_b_2'        => [[413, 435]],
            'QM11_c_2'        => [[436, 452]],
            // Trenk classified as Malt
            'Q16_a_1'         => [[453, 473]],
            'Q16_b_1'         => [[474, 494]],
            'Q17_a_1'         => [[495, 510]],
            'Q17_b_1'         => [[511, 526]],
            // Trenk classified as Energy
            'Q16_a_2'         => [[527, 549]],
            'Q16_b_2'         => [[550, 572]],
            'Q17_a_2'         => [[573, 589]],
            'Q17_b_2'         => [[590, 606]],
            'Q18'             => 607,
            'Q19'             => 608,
            'Q20'             => [[609, 630]],
            'Q21'             => 631,
            'Q22'             => 632,
            'Q23'             => 633,
            'Q24'             => [[634, 636]],
            'Q25'             => 637,
            'Q26'             => 638,
            'Q27'             => 639,
            'Q28'             => 640,
            // Contact
            'agree_follow_up' => 641,
            'mobile_number'   => 642,
            'email_address'   => 643,
        ];
    }
}

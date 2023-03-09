<?php

namespace App\Heineken;

class SaloneConsumer extends SurveyProcessorBase {

    private $drinks = [
        0  => 'Pure water (in sachet)',
        1  => 'Soft drink',
        2  => 'Bottled water',
        3  => 'Beer',
        4  => 'Local Spirits',
        5  => 'Spirits',
        6  => 'Juice',
        7  => 'Alcoholic wine',
        8  => 'Energy drink',
        9  => 'Malt drink',
        10 => 'Palm wine',
        11 => 'Cider',
        12 => 'Fruit wine',
        13 => 'Q10.a: ..',
        14 => 'Q10.b: ..',
    ];

    protected function prepareResult(array $surveyResult): array
    {
        $this->handleQuestion('Q11_e', $surveyResult, function ($questionData) {
            // @todo Check if drinks index 13 and 14 are filled in, retrieve from destined question and set as label.

            return array_combine($this->drinks, $questionData);
        });

        foreach (['Q12_a', 'Q12_b', 'Q12_c'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult, $identifier) {
                $labels = array_values(array_filter($surveyResult['MD1']));
                $values = array_values(array_filter($questionData));

                $results = [];
                foreach ($labels as $index => $label) {
                    if ( ! isset($values[$index])) {
                        continue;
                    }

                    $value = $values[$index];
                    if ($identifier === 'Q12_c' && is_string($value)) {
                        $value = preg_replace('/[o]/', '0', $value);
                        $value = (int)preg_replace('/[a-zA-Z ,]/', '', $value);
                    }

                    $results[$label] = $value;
                }

                return $results;
            });
        }

        foreach (['QM15', 'QM16'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult) {
                $labels = array_values(array_filter($surveyResult['MD1']));
                $valueChunks = [];
                foreach (array_chunk($questionData, 30) as $index => $chunk) {
                    $chunk = array_values(array_filter($chunk));

                    if (empty($chunk)) {
                        continue;
                    }

                    $valueChunks[] = $chunk;
                }

                $results = [];
                foreach ($labels as $index => $label) {
                    if ( ! isset($valueChunks[$index])) {
                        continue;
                    }

                    $results[$label] = $valueChunks[$index];
                }

                return $results;
            });
        }

        $this->handleQuestion('Q19', $surveyResult, function ($questionData, $surveyResult) {
            $labels = array_values(array_filter($surveyResult['Q18']));
            $values = array_values(array_filter($questionData));

            $results = [];
            foreach ($labels as $index => $label) {
                if ( ! isset($values[$index])) {
                    continue;
                }

                $results[$label] = $values[$index];
            }

            return $results;
        });

        $this->handleQuestion('Q27', $surveyResult, function ($questionData) {
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

        $this->handleQuestion('mobile_number', $surveyResult, function ($questionData) {
            $value = (string)$questionData;

            if (empty($value)) {
                return null;
            }

            return $value;
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
            'SR4'             => 32,
            // Demographics
            'Q1'              => 33,
            'Q2'              => 34,
            'Q3_a'            => 35,
            'Q3_b'            => 36,
            'Q4'              => 37,
            'Q5'              => 38,
            'Q6'              => 39,
            'Q7'              => 40,
            'Q9'              => 41,
            'Q10'             => 42,
            // Survey questions
            'Q10_a'           => [[43, 45]],
            'Q10_b'           => [[46, 60]],
            'Q11_a'           => [[61, 94]],
            'Q11_b'           => [[95, 128]],
            'Q11_c'           => 129,
            'Q11_d'           => 130,
            'Q11_e'           => [[131, 145]],
            'MD1'             => [[146, 157]],
            'Q12_a'           => [[158, 225]],
            'Q12_b'           => [[226, 247]],
            'Q12_c'           => [[248, 259]],
            'Q13'             => 260,
            'QM14'            => [[261, 278]],
            'QM15'            => [[279, 608]],
            'QM16'            => [[609, 938]],
            'Q13_a'           => 939,
            'Q13_b'           => [[940, 956]],
            'Q14'             => [[957, 978]],
            'Q15'             => [[979, 1000]],
            'Q16'             => 1001,
            'Q17_a'           => 1002,
            'Q17_b'           => [[1003, 1023]],
            'Q18'             => [[1024, 1045]],
            'Q19'             => [[1046, 1067]],
            'Q20'             => [[1068, 1074]],
            'Q21'             => 1075,
            'Q22'             => [[1076, 1097]],
            'Q23'             => 1098,
            'Q24'             => 1099,
            'Q25'             => 1100,
            'Q26'             => 1101,
            'Q27'             => [[1102, 1104]],
            'Q28_a'           => 1105,
            'Q28_b'           => 1106,
            'Q29'             => 1107,
            'Q30_a'           => 1108,
            'Q30_b'           => 1109,
            // Contact
            'agree_follow_up' => 1110,
            'mobile_number'   => 1111,
            'email_address'   => 1112,
        ];
    }
}

<?php

namespace App\Heineken;

class TrenkCustomer extends SurveyProcessorBase {

    protected function prepareResult(array $surveyResult): array
    {
        $this->handleQuestion('B1', $surveyResult, function ($questionData) {
            return empty($questionData[0]) ? $questionData[1] : $questionData[0];
        });

        foreach (['Q1_a_1', 'Q1_a_2', 'Q1_a_3'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                $values = [];
                foreach ($questionData as $value) {
                    if ($value === 'Others (specify)') {
                        continue;
                    }

                    $values[] = $value;
                }

                return $values;
            });
        }

        foreach (['MD2', 'Q1_b', 'Q1_c'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData, $surveyResult) {
                $mapping = [
                    '[question("option value"),id="115",option="10606"]' => array_values(array_filter(array_slice($surveyResult['Q1_a_1'],
                            3)))[0] ?? null,
                    '[question("option value"),id="115",option="10607"]' => array_values(array_filter(array_slice($surveyResult['Q1_a_1'],
                            3)))[1] ?? null,
                    '[question("option value"),id="116",option="10625"]' => array_values(array_filter(array_slice($surveyResult['Q1_a_2'],
                            10)))[0] ?? null,
                    '[question("option value"),id="118",option="10647"]' => array_values(array_filter(array_slice($surveyResult['Q1_a_3'],
                            18)))[0] ?? null,
                ];

                if (is_array($questionData)) {
                    $values = array_values(array_filter($questionData));
                    $results = [];
                    foreach ($values as $index => $value) {
                        if (array_key_exists($value, $mapping)) {
                            $results[$index] = $mapping[$value];
                        } else {
                            $results[] = $value;
                        }
                    }
                } else {
                    if (array_key_exists($questionData, $mapping)) {
                        $results = $mapping[$questionData];
                    } else {
                        $results = $questionData;
                    }
                }

                return $results;
            });
        }

        $this->handleQuestion('Q1_d', $surveyResult, function ($questionData, $surveyResult) {
            $labels = array_values(array_filter($surveyResult['MD2']));
            $values = array_values(array_filter($questionData));
            $results = [];
            foreach ($values as $index => $value) {
                if (strpos($value, 'specify') !== false) {
                    continue;
                }

                if (isset($labels[$index])) {
                    $results[$labels[$index]] = $value;
                }
            }

            return $results;
        });

        $this->handleQuestion('Q3', $surveyResult, function ($questionData, $surveyResult) {
            $labels = array_values(array_filter($surveyResult['Q2_b']));
            $values = array_values(array_filter($questionData));
            $results = [];
            foreach ($values as $index => $value) {
                if (isset($labels[$index])) {
                    $results[$labels[$index]] = $value;
                }
            }

            return $results;
        });

        $this->handleQuestion('Q4', $surveyResult, function ($questionData) {
            $labels = [
                'Financial terms – margins from the brand',
                'Financial terms – discounts and offers',
                'Financial terms – Payment terms',
                'Assortment – Wide Range of brands',
                'Assortment – Best-selling brands',
                'Assortment – Right pack sizes',
                'Assortment – Up to date pack types (Cans, PET bottles, etc.)',
                'Service – Regulairty of visits – Company Sales persons',
                'Service – Regularity of order-booking and delivery',
                'Service – Return of damages',
                'Service – Credit terms with Distributor',
                'Service – Merchandising support',
                'Service – Maintenance of equipment like chillers',
                'Consulting – Information from the Company on market trends, consumer behaviour, stocking and planning',
                'Consulting – Advice by Company people given on stocking levels, pricing, display, etc.',
                'Activation – Promotions',
                'Activation – Signages',
                'Activation – Branded merchandise (bottle openers, coasters, etc.)',
                'Activation – Promoters',
                'Activation – Chiller/fridge Painting',
                'Activation – Shopboard Painting',
                'Activation – POS Material',
                'Relationship – Complaint Handling – Company',
                'Relationship – Complaint Handling - Distributor',
                'Relationship – Courtesy and Behaviour of Distributor Salesmen',
                'Relationship – Openness to listening – Company Salesmen',
                'Relationship – Openness to listening – Distributor',
                'Relationship – Availability of Distributor when needed',
            ];

            return array_combine($labels, $questionData);
        });

        $this->handleQuestion('Q5', $surveyResult, function ($questionData, $surveyResult) {
            $labels = array_values(array_filter($surveyResult['MD3']));
            $values = array_values(array_filter($questionData));

            return array_combine($labels, $values);
        });

        foreach (['Q16_a', 'Q16_b', 'Q16_c', 'Q16_d'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                $labels = [
                    'Packaging',
                    'Product',
                    'Price',
                    'Promotions',
                    'Margins',
                ];

                return array_combine($labels, $questionData);
            });
        }

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
            'respondent_id'  => 0,
            'time_started'   => 1,
            'time_submitted' => 2,
            'status'         => 3,
            'user_agent'     => 10,
            'ip_address'     => 12,
            'country'        => 15,
            // Screening
            'SR1'            => 19,
            'SR2'            => 20,
            'SR3'            => 21,
            'SR4'            => 22,
            'SR5'            => 23,
            // Survey questions
            'B1'             => [24, 25],
            'B2'             => [[26, 41]],
            'MD1'            => [[43, 45]],
            'Q1_a_1'         => [[46, 53]],
            'Q1_a_2'         => [[54, 66]],
            'Q1_a_3'         => [[67, 89]],
            'MD2'            => [[92, 113]],
            'Q1_b'           => [[114, 127]],
            'Q1_c'           => 128,
            'Q1_d'           => [[129, 164]],
            'Q2_a'           => [[165, 188]],
            'Q2_b'           => [[189, 215]],
            'Q3'             => [[216, 228]],
            'Q4'             => [[229, 256]],
            'MD3'            => [[259, 287]],
            'Q5'             => [[288, 315]],
            'Q12'            => 316,
            'Q13'            => 317,
            'Q14'            => 318,
            'Q15'            => [[319, 339]],
            'Q16_a'          => [[340, 344]],
            'Q16_b'          => [[345, 349]],
            'Q16_c'          => [[350, 354]],
            'Q16_d'          => [[355, 359]],
        ];
    }
}

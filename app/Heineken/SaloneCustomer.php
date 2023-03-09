<?php

namespace App\Heineken;

class SaloneCustomer extends SurveyProcessorBase {

    protected function prepareResult(array $surveyResult): array
    {
        $questionsList = ['B1_a', 'Q2_a', 'Q2_b', 'Q7_a', 'Q7_b', 'Q11'];
        foreach ($questionsList as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                return array_values(array_filter($questionData));
            });
        }

        $this->handleQuestion('Q3', $surveyResult, function ($questionData, $surveyResult) {
            $labels = $surveyResult['Q2_b'];

            if ( ! isset($labels[0])) {
                return null;
            }

            return array_combine($labels, array_filter($questionData));
        });

        foreach (['Q4', 'Q5'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
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
        }

        $this->handleQuestion('Q7_d', $surveyResult, function ($questionData, $surveyResult) {
            $labels = array_filter($surveyResult['Q7_a']);

            if (empty($labels) || in_array('Can\'t name any', $labels)) {
                return null;
            }

            $values = [];
            foreach ($questionData as $value) {
                if ($value === null || $value === 'Other (please specify)') {
                    continue;
                }
                $values[] = $value;
            }

            return array_combine($labels, $values);
        });

        foreach (['Q13_a', 'Q13_b'] as $identifier) {
            $this->handleQuestion($identifier, $surveyResult, function ($questionData) {
                $labels = [
                    'Packaging (how you purchase it)',
                    'Product',
                    'Price',
                    'Promotions',
                    'Margins',
                    'Sourcing the product',
                    'Packaging (format you sell to your customers',
                ];

                return array_combine($labels, $questionData);
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
            'B1_a'           => [[24, 39]],
            'B1_b'           => 40,
            'B1_c'           => 41,
            'Q2_a'           => [[42, 65]],
            'Q2_b'           => [[66, 95]],
            'Q3'             => [[96, 108]],
            'Q4'             => [[109, 136]],
            'Q5'             => [[171, 198]],
            'Q7_a'           => [[199, 208], 212, 213],
            'Q7_b'           => [[216, 239]],
            'Q7_c'           => 240,
            'Q7_d'           => [[241, 260]],
            'Q8'             => 261,
            'Q9'             => 262,
            'Q10'            => 263,
            'Q11'            => [[264, 283]],
            'Q12'            => 284,
            'Q13_a'          => [[285, 291]],
            'Q13_b'          => [[292, 298]],
        ];
    }
}

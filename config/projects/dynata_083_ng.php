<?php

return [
    'description'         => 'Nigeria, n=150, Estimations (IR=30% (base=?), LOI=10 minutes, CPI=$9.95, Start=25-01-2023), DA=Yes, Language=English, ORD-797656-B6W0',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 414,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'                        => [
            'NG',
        ],
        'age_range'                      => [
            '18-100',
        ],
        'monthly_household_income_level' => [
            'HIGH',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'age_range'                      => null,
            'monthly_household_income_level' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [61],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=HirBAcxE_SlbBiLYlihzzQ**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=HirBAcxE_SlbBiLYlihzzQ**&id={RID}&testMode=true',
    ],
];

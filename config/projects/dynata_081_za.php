<?php

return [
    'description'         => 'Nigeria, n=400, Estimations (IR=40% (base=?), LOI=30 minutes, CPI=105 ZAR, Start=16-12-2022), DA=Yes, Language=English, ORD-783764-V0B3',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 30,
            'usd_amount'     => 2.70,
            'local_amount'   => 47.49,
            'local_currency' => 'ZAR',
        ],
    ],
    'targets'             => [
        'country'   => [
            'ZA',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '20-24',
            '25-34',
            '35-40',
        ],
        'city'      => [
            'cape-town',
            'johannesburg',
            'durban',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'city'      => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [106],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=Catq7Lv1muEq5d1LDNrB6g**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=Catq7Lv1muEq5d1LDNrB6g**&id={RID}&testMode=true',
    ],
];

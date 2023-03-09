<?php

return [
    'description'         => 'Kenya, n=400, Estimations (IR=40% (base=?), LOI=30 minutes, CPI=105 ZAR, Start=12-12-2022), DA=Yes, Language=English, ORD-783764-V0B3',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 30,
            'usd_amount'     => 2.70,
            'local_amount'   => 331,
            'local_currency' => 'KES',
        ],
    ],
    'targets'             => [
        'country'   => [
            'KE',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '20-40',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => true,
        'qualification_question_ids'        => [],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=h8_Gmk00LHF77kCZK4G2Sg**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=h8_Gmk00LHF77kCZK4G2Sg**&id={RID}&testMode=true',
    ],
];

<?php

return [
    'description'         => 'Kenya, n=?, Estimations (IR=50% (base=?), LOI=17 minutes, CPI=$4.25, Start=18-01-2023), DA=Yes, Language=English, ORD-788105-Y6C8',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 17,
            'usd_amount'     => 1.53,
            'local_amount'   => 190,
            'local_currency' => 'KES',
        ],
    ],
    'targets'             => [
        'country'                => [
            'KE',
        ],
        'gender'                 => [
            'm',
            'w',
        ],
        'age_range'              => [
            '18-24',
            '25-34',
            '35-44',
        ],
        'subdivision_geo_region' => [
            'KE-Central',
            'KE-Coast',
            'KE-Eastern',
            'KE-Nairobi',
            'KE-North Eastern',
            'KE-Nyanza',
            'KE-Rift Valley',
            'KE-Western',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'                 => null,
            'age_range'              => null,
            'subdivision_geo_region' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [9],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=yBag91Yqb8ZZu_4u2HMqbw**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=yBag91Yqb8ZZu_4u2HMqbw**&id={RID}&testMode=true',
    ],
];

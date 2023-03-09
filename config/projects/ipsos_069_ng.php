<?php

return [
    'description'         => 'Nigeria, N=100, Estimations (IR=80-100% (base=?), LOI=15 minutes, CPI=$5.85, Start=31-01-2023), DA=Yes, Language=English, Cortex ID=876174',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 622,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'                => [
            'NG',
        ],
        'gender'                 => [
            'w',
        ],
        'age_range'              => [
            '25-44',
        ],
        'subdivision_geo_region' => [
            'NG-North-East',
            'NG-North-West',
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
        'qualification_question_ids'        => [11],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=m2H1yJ%2bbR4iF7pLXJXmvXDVerWdXWKM0tupFFZbQAAXMwHwxLDJObTOMZNOPGUWNe9h7q9CeX%2boSNoYNN1OJpg%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=m2H1yJ%2bbR4iF7pLXJXmvXDVerWdXWKM0tupFFZbQAAXMwHwxLDJObTOMZNOPGUWNe9h7q9CeX%2boSNoYNN1OJpg%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
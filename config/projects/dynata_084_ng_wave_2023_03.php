<?php

return [
    'description'         => 'Nigeria, n=?, Estimations (IR=40% (base=?), LOI=10 minutes, CPI=?, Start=27-02-2023), DA=Yes, Language=English, ORD-787446-C1X7',
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
        'country'                => [
            'NG',
        ],
        'gender'                 => [
            'm',
            'w',
        ],
        'age_range'              => [
            '18-24',
            '25-34',
            '35-44',
            '45-55',
        ],
        'subdivision_geo_region' => [
            'NG-North-East',
            'NG-North-West',
            'NG-South-East',
            'NG-South-West',
            'NG-North-Central',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'                 => [
                'age_range' => null,
            ],
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
        'exclude_respondents_from_projects' => [
            'dynata_084_ng_wave_2023_02',
        ],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=mJQR-W2vHnAY-LPbuI9egQ**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=mJQR-W2vHnAY-LPbuI9egQ**&id={RID}&testMode=true',
    ],
];

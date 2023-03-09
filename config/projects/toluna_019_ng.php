<?php

return [
    'description'         => 'Nigeria, N=?, Estimations (IR=100% (base=?), LOI=15 minutes, CPI=?, Start=16-12-2022), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 601,
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
            '45-54',
            '55-64',
        ],
        'subdivision_geo_region' => [
            "NG-North-Central",
            "NG-North-East",
            "NG-North-West",
            "NG-South-East",
            "NG-South-South",
            "NG-South-West",
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
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=c745c96d99bf4eb9bb348ddc59e85389&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=c745c96d99bf4eb9bb348ddc59e85389&ids={RID}&mode=tprod',
    ],
];
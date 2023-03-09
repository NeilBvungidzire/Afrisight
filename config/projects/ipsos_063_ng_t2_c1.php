<?php

return [
    'description'         => 'Nigeria, N=282, Estimations (IR=10-19% (base=?), LOI=10 minutes, CPI=$7.85, Start=16-12-2022), DA=Yes, Language=English, Cortex ID=863408',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 400,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'   => [
            'NG',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '25-55',
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
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=P3m6njobblvi7WsHhhQdLkPrEX6J1Ejfjnv3DUXRmrCk5xfMsZJApkDqDbwSSK%2fXOhkIl2XCt67MN3KBNa1S3g%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=P3m6njobblvi7WsHhhQdLkPrEX6J1Ejfjnv3DUXRmrCk5xfMsZJApkDqDbwSSK%2fXOhkIl2XCt67MN3KBNa1S3g%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
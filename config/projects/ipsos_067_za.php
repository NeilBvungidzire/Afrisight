<?php

return [
    'description'         => 'South-Africa, N=100, Estimations (IR=80-100% (base=?), LOI=15 minutes, CPI=?, Start=25-01-2023), DA=Yes, Language=English, Cortex ID=874555',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 23.24,
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
            '45-55',
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
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=zhbKQm6v2E5cPvvv%2fSuHuSCddSCHio7FfPMwsEwleC3SXkb6vxdcjkDtSZtCaE4W8FGCCBkS4Vs4UFnVlDD9HA%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=zhbKQm6v2E5cPvvv%2fSuHuSCddSCHio7FfPMwsEwleC3SXkb6vxdcjkDtSZtCaE4W8FGCCBkS4Vs4UFnVlDD9HA%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
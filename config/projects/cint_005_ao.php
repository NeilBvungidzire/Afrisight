<?php

return [
    'description'         => 'Angola, n=154, Estimations (IR=20% (base=?), LOI=15-20 minutes, CPI=$4.50, Start=20-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 680,
            'local_currency' => 'AOA',
        ],
    ],
    'targets'             => [
        'country'   => [
            'AO',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '21-35',
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
        'survey_link_live'                  => 'https://router.cint.com/ExternalRoute/d8a2f21d-cbb8-48bf-8b58-044fde2a885d?id={RID}',
        'survey_link_test'                  => 'https://router.cint.com/ExternalRoute/d8a2f21d-cbb8-48bf-8b58-044fde2a885d?id={RID}',
    ],
];
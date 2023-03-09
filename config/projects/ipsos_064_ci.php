<?php

return [
    'description'         => 'Ivory Coast, N=1.000, Estimations (IR=90% (base GP), LOI=10 minutes, CPI=$4.45, Start=05-01-2023), DA=Yes, Language=French, Cortex ID=870361',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 556,
            'local_currency' => 'XOF',
        ],
    ],
    'targets'             => [
        'country'   => [
            'CI',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-24',
            '25-34',
            '35-44',
            '45-54',
            '55-100',
        ],
        'state'     => [
            'CI-AB',
            'CI-BS',
            'CI-CM',
            'CI-DN',
            'CI-GD',
            'CI-LC',
            'CI-LG',
            'CI-MG',
            'CI-SM',
            'CI-SV',
            'CI-VB',
            'CI-WR',
            'CI-YM',
            'CI-ZZ',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'state'     => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [25],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=fDIzTPNukYbpuIiXN%2bUre2F6cc5YB3iKZ%2buBzZx51upLwUpyKH3e7bSfWnGvWp16UViCewG828Xz5NHwCjcxACrOjmcgWGRtbLb6NZ1oQho%3d&rType=31&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=fDIzTPNukYbpuIiXN%2bUre2F6cc5YB3iKZ%2buBzZx51upLwUpyKH3e7bSfWnGvWp16UViCewG828Xz5NHwCjcxACrOjmcgWGRtbLb6NZ1oQho%3d&rType=31&id={RID}&testcortex=2',
    ],
];
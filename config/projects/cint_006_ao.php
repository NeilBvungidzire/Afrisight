<?php

return [
    'description'         => 'Angola, n=288, Estimations (IR=20% (base=?), LOI=10-15 minutes, CPI=$4.50, Start=23-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 453,
            'local_currency' => 'AOA',
        ],
    ],
    'targets'             => [
        'country'   => [
            'AO',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-24',
            '25-34',
            '35-100',
        ],
        'sec_1'     => [
            'AB',
            'C1',
            'C2|D',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'sec_1'     => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [41, 42, 43, 44, 45, 46, 47, 48, 49],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://router.cint.com/ExternalRoute/ac03e880-322a-422f-a9f8-b93150cc0da3?id={RID}',
        'survey_link_test'                  => 'https://router.cint.com/ExternalRoute/ac03e880-322a-422f-a9f8-b93150cc0da3?id={RID}',
    ],
];
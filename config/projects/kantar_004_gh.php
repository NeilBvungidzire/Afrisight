<?php

return [
    'description'         => 'Ghana, n=130 (12 waves, monthly), Estimations (IR=21-45% (base=?), LOI=10 minutes, CPI=$4.85, Start=22-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 11.47,
            'local_currency' => 'GHS',
        ],
    ],
    'targets'             => [
        'country'   => [
            'GH',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-24',
            '25-34',
            '35-44',
            '45-100',
        ],
        'city'      => [
            'accra',
            'kumasi',
            'takoradi',
            'tamale',
            'koforidua',
        ],
        'sec_1'     => [
            'AB',
            'C1',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'city'      => null,
            'sec_1'     => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [136, 41, 42, 43, 44, 45, 46, 47, 48, 49],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://router.cint.com/ExternalRoute/4b07ee6b-ce76-4b70-a48e-0f363301b375?id={RID}',
        'survey_link_test'                  => 'https://router.cint.com/ExternalRoute/4b07ee6b-ce76-4b70-a48e-0f363301b375?id={RID}',
    ],
];

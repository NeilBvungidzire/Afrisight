<?php

return [
    'description'         => 'Kenya, N=100, Estimations (IR=80-100% (base=?), LOI=10 minutes, CPI=$4.50, Start=09-01-2023), DA=Yes, Language=English, Cortex ID=871023',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 111,
            'local_currency' => 'KES',
        ],
    ],
    'targets'             => [
        'country'   => [
            'KE',
        ],
        'age_range' => [
            '18-24',
            '25-34',
            '35-44',
            '45-54',
            '55-64',
            '65-100',
        ],
    ],
    'targets_relation'    => [
        'country' => [
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
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=lHx13zSthPnVXDEsdw%2bJicvwMfKCPNlXpm3CqUG0oYPC9W%2fgGi07QkXYuTJHrLRc4uYKYBkCgmAIEnAsG7HD6tHMc5HIj3xee%2bFNJ5a7Gds%3d&rType=31&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=lHx13zSthPnVXDEsdw%2bJicvwMfKCPNlXpm3CqUG0oYPC9W%2fgGi07QkXYuTJHrLRc4uYKYBkCgmAIEnAsG7HD6tHMc5HIj3xee%2bFNJ5a7Gds%3d&rType=31&id={RID}&testcortex=2',
    ],
];
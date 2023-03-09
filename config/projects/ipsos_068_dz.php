<?php

return [
    'description'         => 'Algeria, N=80, Estimations (IR=80% (base=GP), LOI=10 minutes, CPI=?, Start=30-01-2023), DA=Yes, Language=French, Cortex ID=875322',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.9,
            'local_amount'   => 122,
            'local_currency' => 'DZD',
        ],
    ],
    'targets'             => [
        'country'   => [
            'DZ',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '40-50',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => true,
        'qualification_question_ids'        => [],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=xNt052NY7GjihbkbsK2OENn0w9uLWVOABTjrvJfZFCZpn5XLMW0kp432Cu7LLYF%2byLgayyqxkhzsTbqrJYneVw%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=xNt052NY7GjihbkbsK2OENn0w9uLWVOABTjrvJfZFCZpn5XLMW0kp432Cu7LLYF%2byLgayyqxkhzsTbqrJYneVw%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
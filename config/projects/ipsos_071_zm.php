<?php

return [
    'description'         => 'Zambia, n=300, Estimations (IR=90% (base GP), LOI=15 minutes, CPI=$4.95, Start=01-03-2023), DA=Yes, Language=English, Cortex ID=884455',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 26.80,
            'local_currency' => 'ZMW',
        ],
    ],
    'targets'             => [
        'country'   => [
            'ZM',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-45',
        ],
        'city'      => [
            'Lusaka',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'city'      => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [76],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=eAaP3Umg219mOea3tHLPBZVyobCxFn5rJ%2bh%2bcFbASfTzTFpXOr1FT2gURJhyh8vZn03dfFzNJBt8QbEOXpXozQ%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=eAaP3Umg219mOea3tHLPBZVyobCxFn5rJ%2bh%2bcFbASfTzTFpXOr1FT2gURJhyh8vZn03dfFzNJBt8QbEOXpXozQ%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
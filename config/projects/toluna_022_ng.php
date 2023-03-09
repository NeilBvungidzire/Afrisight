<?php

return [
    'description'         => 'Nigeria, n=1.000, Estimations (IR=7% (base=GP), LOI=20 minutes, CPI=$10.95, Start=19-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 11,
            'usd_amount'     => 0.99,
            'local_amount'   => 456,
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
            '20-24',
            '25-34',
            '35-44',
        ],
        'sec_1'     => [
            'AB',
            'C1',
            'C2',
            'D',
            'E',
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
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=68e94a6b2d2c4afaba2d17c21ac227b9&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=68e94a6b2d2c4afaba2d17c21ac227b9&ids={RID}&mode=tprod',
    ],
];
<?php

return [
    'description'         => 'Nigeria, n=1.000, Estimations (IR=90% (base=GP), LOI=5 minutes, CPI=$2.45, Start=11-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 5,
            'usd_amount'     => 0.45,
            'local_amount'   => 202,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'   => [
            'NG',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '16-24',
            '25-34',
            '35-44',
            '45-54',
            '55-64',
            '65-100',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender' => [
                'age_range' => null,
            ],
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
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=d6c60876da1e4383a34ab5506a4d9fc4&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=d6c60876da1e4383a34ab5506a4d9fc4&ids={RID}&mode=tprod',
    ],
];
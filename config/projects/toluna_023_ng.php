<?php

return [
    'description'         => 'Nigeria, n=?, Estimations (IR=50% (base=?), LOI=10 minutes, CPI=$4.60, Start=01-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 414,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'   => [
            'NG',
        ],
        'age_range' => [
            '31-35',
        ],
        'lsm'       => [
            'LSM4|LSM5|LSM6|LSM7',
            'LSM8|LSM9|LSM10',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'age_range' => null,
            'lsm'       => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [38],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=e7d87f9925ed468db7dfa9737dc28b84&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=87&bid=43&golsoid=e7d87f9925ed468db7dfa9737dc28b84&ids={RID}&mode=tprod',
    ],
];
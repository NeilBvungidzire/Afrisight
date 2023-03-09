<?php

return [
    'description'         => 'Kenya, n=250, Estimations (IR=30-40% (base=?), LOI=10 minutes, CPI=$5.99, Start=01-03-2023), DA=Yes, Language=English, ORD-781314-Z0K8',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 414,
            'local_currency' => 'KES',
        ],
    ],
    'targets'             => [
        'country'   => [
            'KE',
        ],
        'age_range' => [
            '16-18',
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
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=2Ktfi3wr0DOx-idMmmuYmg**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=2Ktfi3wr0DOx-idMmmuYmg**&id={RID}&testMode=true',
    ],
];

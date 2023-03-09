<?php

return [
    'description'         => 'Ghana, n=250, Estimations (IR=30-40% (base=?), LOI=10 minutes, CPI=$5.99, Start=01-03-2023), DA=Yes, Language=English, ORD-781314-Z0K8',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 11.57,
            'local_currency' => 'GHS',
        ],
    ],
    'targets'             => [
        'country'   => [
            'GH',
        ],
        'age_range' => [
            '19-100',
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
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=2Ktfi3wr0DMcQStVUmaBWA**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=2Ktfi3wr0DMcQStVUmaBWA**&id={RID}&testMode=true',
    ],
];

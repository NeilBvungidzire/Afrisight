<?php

return [
    'description'         => 'South Africa, n=1.500, Estimations (IR=20%, LOI=15 minutes, CPI=$6.95, Start=23-12-2022), DA=No (mobile/tablet), Language=English, ORD-768529-W3Y5',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 24.37,
            'local_currency' => 'ZAR',
        ],
    ],
    'targets'             => [
        'country'   => [
            'ZA',
        ],
        'age_range' => [
            '18-54',
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
        'device_restrictions'               => [
            \App\Constants\DataPointAttribute::MOBILE,
            \App\Constants\DataPointAttribute::TABLET,
        ],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=lWjPOLSSqZx4IFZxD-aVyw**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=lWjPOLSSqZx4IFZxD-aVyw**&id={RID}&testMode=true',
    ],
];

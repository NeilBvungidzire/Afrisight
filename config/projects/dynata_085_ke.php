<?php

return [
    'description'         => 'Kenya, n=300, Estimations (IR=40% (base=?), LOI=15 minutes, CPI=$5.95, Start=14-02-2023), DA=Yes, Language=English, ORD-803625-X1G0',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 169,
            'local_currency' => 'KES',
        ],
    ],
    'targets'             => [
        'country'   => [
            'KE',
        ],
        'gender'    => [
            'm',
            'w',
            'o',
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
            'gender'    => null,
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
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=SKveL1WoRe8EMHfKmP0DUw**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=SKveL1WoRe8EMHfKmP0DUw**&id={RID}&testMode=true',
    ],
];

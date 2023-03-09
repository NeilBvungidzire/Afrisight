<?php

return [
    'description'         => 'Nigeria, n=850, Estimations (IR=5-10% (base=GP), LOI=15 minutes, CPI=$7.45, Start=12-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 609,
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
            'u',
        ],
        'age_range' => [
            '21-34',
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
        'survey_link_live'                  => 'https://surveyengine.pureprofile.com/start/a0a1e785-ba0d-41f4-aaaa-8d25a0486ae3?rid={RID}',
        'survey_link_test'                  => 'https://surveyengine.pureprofile.com/start/a0a1e785-ba0d-41f4-aaaa-8d25a0486ae3?rid={RID}',
    ],
];
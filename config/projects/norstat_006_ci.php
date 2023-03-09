<?php

return [
    'description'         => 'Ivory Coast, n=800, Estimations (IR=?% (base=?), LOI=20 minutes, CPI=?, Start=01-03-2023), DA=Yes, Language=French',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 1115,
            'local_currency' => 'XOF',
        ],
    ],
    'targets'             => [
        'country'   => [
            'CI',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-34',
            '35-49',
            '50-100',
        ],
        'sec_1'     => [
            'AB',
            'C1',
            'C2',
            'DE',
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
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [41, 42, 43, 44, 45, 46, 47, 48, 49],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://web.norstatsurveys.com/survey/selfserve/53c/2302415?list=100&id={RID}',
        'survey_link_test'                  => 'https://web.norstatsurveys.com/survey/selfserve/53c/2302415?list=100&id={RID}',
    ],
];
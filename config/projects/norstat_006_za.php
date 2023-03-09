<?php

return [
    'description'         => 'South-Africa, n=?, Estimations (IR=?% (base=?), LOI=20 minutes, CPI=?, Start=27-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 33.09,
            'local_currency' => 'ZAR',
        ],
    ],
    'targets'             => [
        'country'   => [
            'ZA',
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
        'lsm'       => [
            'LSM1|LSM2|LSM3|LSM4|LSM5|LSM6',
            'LSM7|LSM8',
            'LSM9|LSM10',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
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
        'survey_link_live'                  => 'https://web.norstatsurveys.com/survey/selfserve/53c/2302415?list=100&id={RID}',
        'survey_link_test'                  => 'https://web.norstatsurveys.com/survey/selfserve/53c/2302415?list=100&id={RID}',
    ],
];
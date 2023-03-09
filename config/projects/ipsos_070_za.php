<?php

return [
    'description'         => 'South-Africa, n=150, Estimations (IR=10-14% (base=?), LOI=20 minutes, CPI=$8.99, Start=23-02-2023), DA=Yes, Language=English, Cortex ID=878830',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 32.84,
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
            '18-25',
            '26-29',
            '30-40',
            '41-50',
        ],
        'lsm'       => [
            'LSM6',
            'LSM7',
            'LSM8|LSM9|LSM10',
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
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=zhbKQm6v2E5cPvvv%2fSuHudQWh8KrnmufZDFCt3%2bdP1DxLWQjVJYL%2f2llxXRhylIK5FgsnMW297W9AjiDxNCmzz1tt7W4CuQN2kaGGAWhrc8%3d&rType=355&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=zhbKQm6v2E5cPvvv%2fSuHueeWHemLTh4AVunKnxA5pM2jAr9ap2SIQ2nzGNJTX3S89fwxS7YfCl3D1CYxFPIIBSulC4aYI5gTlA9MKII56oCN02FFtR9Q6HbFceJHhi0Q&rType=355&id={RID}',
    ],
];
<?php

return [
    'description'         => 'Ethiopia, n=100, Estimations (IR=100% (base=?), LOI=20 minutes, CPI=$4.99, Start=23-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 97,
            'local_currency' => 'ETB',
        ],
    ],
    'targets'             => [
        'country'   => [
            'ET',
        ],
        'gender'    => [
            'm',
            'w',
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
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://www.msi-aci.com/dotdata/start?u=35F79DAD61134FD4B19C5BC91F0049E0&ExtID=AS&ui={RID}',
        'survey_link_test'                  => 'https://www.msi-aci.com/dotdata/start?u=35F79DAD61134FD4B19C5BC91F0049E0&testsession=1&l=28&ver=1&labels=1&testvu=1708699894&testkey=9d262ac7f5589800e17892b090e83d5b&ExtID=AS&ui={RID}',
    ],
];

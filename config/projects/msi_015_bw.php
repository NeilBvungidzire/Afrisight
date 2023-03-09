<?php

return [
    'description'         => 'Botswana, n=100, Estimations (IR=100% (base=?), LOI=20 minutes, CPI=$4.99, Start=23-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 23.90,
            'local_currency' => 'BWP',
        ],
    ],
    'targets'             => [
        'country'   => [
            'BW',
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
        'survey_link_live'                  => 'https://www.msi-aci.com/dotdata/start?u=E954D4B341DE46F081E4CB02C08E1E32&ExtID=AS&ui={RID}',
        'survey_link_test'                  => 'https://www.msi-aci.com/dotdata/start?u=E954D4B341DE46F081E4CB02C08E1E32&testsession=1&l=28&ver=1&labels=1&testvu=1708700033&testkey=e1d4363e6ebb8c2d9d5e895dc0506b6d&ExtID=AS&ui={RID}',
    ],
];

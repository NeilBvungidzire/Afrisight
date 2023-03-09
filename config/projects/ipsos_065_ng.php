<?php

return [
    'description'         => 'Nigeria, n=?, Estimations (IR=80-100% (base=?), LOI=10 minutes, CPI=$4.50, Start=10-01-2023), DA=Yes, Language=English, Cortex ID=871257',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 405,
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
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=P3m6njobblvi7WsHhhQdLmNep9CJreslomkGsqRRV0BHRtVpI6Ql5UoReI19eerihg59qdzLoFRUVSQvzCiaNinQAVFtlMlnybfp%2fPETj2M%3d&rType=31&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=P3m6njobblvi7WsHhhQdLr75fh8GeTkVtlkefulu%2fC12Ht0FzV6yTog93JfrVCPGbCQnix%2fb0GNU40Gde4UdBE5Lf%2bE4PIGn9YdwDUG02PIWfMx8KcbjYCqKa3Rg%2bBhL&rType=31&id={RID}',
    ],
];
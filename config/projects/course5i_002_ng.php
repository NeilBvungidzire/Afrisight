<?php

return [
    'description'         => 'Nigeria, n=400, Estimations (IR=60% (base=?), LOI=15 minutes, CPI=$4.95, Start=15-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 622,
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
        'sec_1'     => [
            'AB',
            'C1',
            'C2',
            'D',
            'E',
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
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [41, 42, 43, 44, 45, 46, 47, 48, 49],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'device_restrictions'               => [],
        'survey_link_live'                  => 'https://hub.decipherinc.com/survey/selfserve/170c/2212155?list=7&samp=1&co=NG&id={RID}&decLang=english_nigeria',
        'survey_link_test'                  => 'https://hub.decipherinc.com/survey/selfserve/170c/2212155?list=7&samp=1&co=NG&id={RID}&test=test&decLang=english_nigeria',
    ],
];

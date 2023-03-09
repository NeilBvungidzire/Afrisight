<?php

return [
    'description'         => 'Morocco, N=1.000, Estimations (IR=90% (base GP), LOI=10 minutes, CPI=$4.45, Start=06-01-2023), DA=Yes, Language=French, Cortex ID=866947',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 9.38,
            'local_currency' => 'MAD',
        ],
    ],
    'targets'             => [
        'country'                => [
            'MA',
        ],
        'gender'                 => [
            'm',
            'w',
        ],
        'age_range'              => [
            '18-24',
            '25-34',
            '35-44',
            '45-54',
            '55-100',
        ],
        'subdivision_geo_region' => [
            'MA-01',
            'MA-02',
            'MA-03',
            'MA-04',
            'MA-05',
            'MA-06',
            'MA-07',
            'MA-08',
            'MA-09',
            'MA-10',
            'MA-11',
            'MA-12',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'                 => null,
            'age_range'              => null,
            'subdivision_geo_region' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [68],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=Ash15N5OpnIrhfgN8I9mJsmFuJcVsHZrI7%2bXgA6BVoOD2SsaHiIr%2bVeTGIOU9A8k%2fkusMzTvj9D0oq4R7TROXiXoRG3NJFW4tIpGX09MKno%3d&rType=31&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=Ash15N5OpnIrhfgN8I9mJsmFuJcVsHZrI7%2bXgA6BVoOD2SsaHiIr%2bVeTGIOU9A8k%2fkusMzTvj9D0oq4R7TROXiXoRG3NJFW4tIpGX09MKno%3d&rType=31&id={RID}&testcortex=2',
    ],
];
<?php

return [
    'description'         => 'Malawi, n=400, Estimations (IR=70-80% (base=?), LOI=20 minutes, CPI=$3.95, Start=27-02-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 20,
            'usd_amount'     => 1.80,
            'local_amount'   => 1849,
            'local_currency' => 'MWK',
        ],
    ],
    'targets'             => [
        'country'   => [
            'MW',
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
        'survey_link_live'                  => 'https://surveyengine.pureprofile.com/start/47ad766e-41e6-4284-9444-36887e3b73e0?rid={RID}',
        'survey_link_test'                  => 'https://surveyengine.pureprofile.com/start/47ad766e-41e6-4284-9444-36887e3b73e0?rid={RID}',
    ],
];
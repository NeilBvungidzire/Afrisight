<?php

return [
    'description'         => 'DRC, n=121, Estimations (IR=?% (base=?), LOI=10+ minutes, CPI=$4.50, Start=30-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 10,
            'usd_amount'     => 0.90,
            'local_amount'   => 20450,
            'local_currency' => 'CDF',
        ],
    ],
    'targets'             => [
        'country'   => [
            'CD',
        ],
        'gender'    => [
            'w',
        ],
        'age_range' => [
            '21-35',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => [],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => true,
        'qualification_question_ids'        => [],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://router.cint.com/ExternalRoute/1faef216-f39d-4cae-bf27-b71bb685143a?id={RID}',
        'survey_link_test'                  => 'https://router.cint.com/ExternalRoute/1faef216-f39d-4cae-bf27-b71bb685143a?id={RID}',
    ],
];
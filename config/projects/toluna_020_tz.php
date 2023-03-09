<?php

return [
    'description'         => 'Tanzania, n=1.000, Estimations (IR=90% (base=GP), LOI=5 minutes, CPI=$2.45, Start=05-01-2023), DA=Yes, Language=English',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 5,
            'usd_amount'     => 0.45,
            'local_amount'   => 1050,
            'local_currency' => 'TZS',
        ],
    ],
    'targets'             => [
        'country'   => [
            'TZ',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '16-24',
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
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=238&bid=43&golsoid=a6f583e119ea44cca07e997d5ae66279&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=238&bid=43&golsoid=a6f583e119ea44cca07e997d5ae66279&ids={RID}&mode=tprod',
    ],
];
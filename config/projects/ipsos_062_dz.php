<?php

return [
    'description'         => 'Algeria, N=150, Estimations (IR=30-49% (base ?), LOI=15 minutes, CPI=$6.95, Start=15-12-2022), DA=Yes, Language=French,Arabic, Cortex ID=866425',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 186,
            'local_currency' => 'DZD',
        ],
    ],
    'targets'             => [
        'country'   => [
            'DZ',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-100',
        ],
        'sec_1'     => [
            'B|C1|C2|D',
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
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [41, 42, 43, 44, 45, 46, 47, 48, 49],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=FA9e3JCJ8ytrEI64rt5PMel5U1BModlDOT9bMuiMODtrUFdidRFkGVd8pG%2bmyRFEKCNSWhKQwo2td0tFlZV66Q%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=FA9e3JCJ8ytrEI64rt5PMel5U1BModlDOT9bMuiMODtrUFdidRFkGVd8pG%2bmyRFEKCNSWhKQwo2td0tFlZV66Q%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
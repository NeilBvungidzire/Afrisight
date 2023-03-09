<?php

return [
    'description'         => 'Ivory Coast, n=300, Estimations (IR=90% (base GP), LOI=15 minutes, CPI=$4.95, Start=01-03-2023), DA=Yes, Language=French/English, Cortex ID=884558',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 15,
            'usd_amount'     => 1.35,
            'local_amount'   => 836,
            'local_currency' => 'XOF',
        ],
    ],
    'targets'             => [
        'country'   => [
            'CI',
        ],
        'gender'    => [
            'm',
            'w',
        ],
        'age_range' => [
            '18-59',
        ],
        'state'     => [
            'CI-AB',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'state'     => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['FR', 'EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [25],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=WP4WNXxlH7wZdZTl5lmp%2fLdWCJwbigt5FMr7SK1JX62t0kgOTAYixL3%2bsjtXneM8oc5B22hxZjf9UFevbr0pTw%3d%3d&routerID=0&rType=0&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=WP4WNXxlH7wZdZTl5lmp%2fLdWCJwbigt5FMr7SK1JX62t0kgOTAYixL3%2bsjtXneM8oc5B22hxZjf9UFevbr0pTw%3d%3d&routerID=0&rType=0&id={RID}&testcortex=2',
    ],
];
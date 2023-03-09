<?php

return [
    'description'         => 'Morocco, N=800, Estimations (IR=80% (base=GP), LOI=10 minutes, CPI=?, Start=11-01-2023), DA=Yes, Language=French,Arabic, Cortex ID=871228',
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
        'country'   => [
            'MA',
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
        'language_restrictions'             => ['FR'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => true,
        'qualification_question_ids'        => [],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://enter.ipsosinteractive.com/landing/?p=joNqYldo%2ftHnx6LVPWMZVa3n%2fx%2bo2DxXv7IZaJG8kVGDPpII62Y2p%2fM8%2bdr5OUfkV%2fkjBJbUTnpIStGez24F%2bgukN8TQxrIDPRn25xeSOpo%3d&rType=350&id={RID}',
        'survey_link_test'                  => 'https://enter.ipsosinteractive.com/landing/?p=joNqYldo%2ftHnx6LVPWMZVa3n%2fx%2bo2DxXv7IZaJG8kVGDPpII62Y2p%2fM8%2bdr5OUfkV%2fkjBJbUTnpIStGez24F%2bgukN8TQxrIDPRn25xeSOpo%3d&rType=350&id={RID}&testcortex=2',
    ],
];
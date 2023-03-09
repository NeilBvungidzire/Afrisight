<?php

return [
    'description'         => 'Algeria, N=65, Estimations (IR=15% (base=GP), LOI=15 minutes, CPI=$8.95, Start=15-12-2022), DA=Yes, Language=Arabic',
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
            '18-64',
        ],
        'city'      => [
            'algiers',
            'oran',
            'tizi-ouzou',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'    => null,
            'age_range' => null,
            'city'      => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => [],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [90],
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [],
        'survey_link_live'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=35&bid=43&golsoid=7a5f480be6324b3e8ecd58bcde6bbd9c&ids={RID}',
        'survey_link_test'                  => 'https://ups.surveyrouter.com/TrafficUI/MSCUI/Page.aspx?pgtid=19&cid=35&bid=43&golsoid=7a5f480be6324b3e8ecd58bcde6bbd9c&ids={RID}&mode=tprod',
    ],
];
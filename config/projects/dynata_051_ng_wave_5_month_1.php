<?php

use App\Constants\DataPointAttribute;

return [
    'description'         => 'Nigeria, n=200/wave (4 waves (quarterly) in total, year period of exclusion), Estimations (IR=100%, LOI=15 minutes, CPI=$3.80, Start=03-01-2023), DA=NO (only mobile), Language=English, ORD-760848-L9N6',
    'live'                => false,
    'enabled_via_web_app' => true,
    'enabled_for_admin'   => true,
    'incentive_packages'  => [
        1 => [
            'loi'            => 30,
            'usd_amount'     => 2.70,
            'local_amount'   => 1124,
            'local_currency' => 'NGN',
        ],
    ],
    'targets'             => [
        'country'         => [
            'NG',
        ],
        'gender'          => [
            'm',
            'w',
        ],
        'age_range'       => [
            '16-24',
            '25-34',
            '35-44',
            '45-54',
            '55-64',
        ],
        'education_level' => [
            'primary-school',
            'secondary-school|high-school',
            'tertiary/technical-college|university/higher-education|postgraduate-education',
        ],
    ],
    'targets_relation'    => [
        'country' => [
            'gender'          => [
                'age_range' => null,
            ],
            'education_level' => null,
        ],
    ],
    'configs'             => [
        'language_restrictions'             => ['EN'],
        'customized_qualification'          => '',
        'force_all_quotas'                  => true,
        'needs_qualification'               => true,
        'background_check'                  => false,
        'qualification_question_ids'        => [22],
        'required_target_hits'              => 4, // MUST BE SAME AMOUNT AS CHECKED IN TARGET HITS!!!
        'quota_count_method'                => 'handleProjectQuota',
        'inflow_incentive_package_id'       => 1,
        'default_incentive_package_id'      => 1,
        'exclude_respondents_from_projects' => [
            'dynata_051_ng',
            'dynata_051_ng_wave_q2',
            'dynata_051_ng_wave_q3',
            'dynata_051_ng_wave_2_month_3',
            'dynata_051_ng_wave_3_month_1',
            'dynata_051_ng_wave_3_month_2',
            'dynata_051_ng_wave_3_month_3',
            'dynata_051_ng_wave_4_month_1',
            'dynata_051_ng_wave_4_month_2',
            'dynata_051_ng_wave_4_month_3',
        ],
        'device_restrictions'               => [DataPointAttribute::MOBILE],
        'survey_link_live'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=tZ31J1ech7Wsk2b1K04JFA**&id={RID}',
        'survey_link_test'                  => 'https://dkr1.ssisurveys.com/projects/estart?ekey=tZ31J1ech7Wsk2b1K04JFA**&id={RID}&testMode=true',
    ],
];

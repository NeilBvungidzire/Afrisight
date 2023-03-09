<?php

return [
    'title'        => 'Norstat',
    'description'  => 'Ivory Coast, N=100, Estimations (IR=100%, LOI=8 minutes, CPI=€9.50, Start=21-06-2022, End=10-07-2022), DA=Yes',
    'live'         => true,
    'sample_id'    => 'mnec6e7j5krrcmnc',
    'source_ids'   => [
        'kj311' => 'general',
    ],
    'country_code' => 'CI',
    'config'       => [
        'survey_link_live' => 'https://web.norstatsurveys.com/survey/selfserve/53c/2206677?unid={RID}',
        'survey_link_test' => 'https://web.norstatsurveys.com/survey/selfserve/53c/2206677?unid={RID}',
    ],
];

<?php

return [
    "description"         => null,
    "live"                => null, // bool
    "enabled_via_web_app" => null, // bool
    "enabled_for_admin"   => null, // bool
    "incentive_packages"  => [
        1 => [
            "loi"            => null, // int
            "usd_amount"     => null, // float
            "local_amount"   => null, // float
            "local_currency" => null, // string
        ],
    ],
    "targets"             => [
        "country"     => [
            "COUNTRY_CODE_TO_MATCH",
        ],
        "TARGET_NAME" => [
            "VALUE_TO_MATCH",
        ],
    ],
    "targets_relation"    => [
        "country" => [
            "VALUE_TO_MATCH" => null,
        ],
    ],
    "configs"             => [
        "customized_qualification"          => null, // Route name for the controller
        "needs_qualification"               => null, // Boolean
        "background_check"                  => null, // Boolean
        "qualification_question_ids"        => [], // IDs for the qualification questions
        "required_target_hits"              => null, // Integer, number of targets to hit.
        "quota_count_method"                => "handleProjectQuota", // String, containing name of the method to handle the quotas.
        "inflow_incentive_package_id"       => null, // Integer
        "default_incentive_package_id"      => null, // Integer
        "survey_link_live"                  => null, // String
        "survey_link_test"                  => null, // String
        "subtle_rewarding"                  => null, // Boolean
        "exclude_respondents_from_projects" => [], // Array of project codes from which this project respondents should be denied.
        "exclude_respondents_by_status"     => [], // Array with statuses by which the respondents from other project should be ignored.
        "language_restrictions"             => [], // Array of language codes. This will be used for deciding whether potential respondent can participate and get invited.
        "force_all_quotas"                  => null, // Boolean
        "invitation_type_handler"           => [], // Array of key-value pairs referring to the handler for type of invite.
        "customized_survey_link"            => null, // Boolean
        "device_restrictions"               => [], // Array of devices supported. Look in DataPointAttribute for device types.
    ],
];

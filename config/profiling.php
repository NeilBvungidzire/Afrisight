<?php

return [

    'enabled' => env('PROFILING_ENABLE', false),

    /**
     * List of question types available for this project
     */
    'supported_question_types' => [
        'MULTIPLE_CHOICE',
        'CHECKBOXES',
        'SINGLE_TEXT_BOX',
        'DROPDOWN',
    ],

    /**
     * List of question types you can use
     */
    'question_types'           => [
        'MULTIPLE_CHOICE' => [
            'view' => 'profiling.question-types.multiple-choice',
        ],
        'CHECKBOXES'      => [
            'view' => 'profiling.question-types.checkboxes',
        ],
        'SINGLE_TEXT_BOX' => [
            'view' => 'profiling.question-types.single-text-box',
        ],
        'DROPDOWN'        => [
            'view' => 'profiling.question-types.dropdown',
        ],
        'STAR_RATING',
        'COMMENT_BOX',
        'MATRIX',
        'RANKING',
        'SLIDER',
        'DATETIME',
        'MULTIPLE_TEXT_BOXES',
    ],

];

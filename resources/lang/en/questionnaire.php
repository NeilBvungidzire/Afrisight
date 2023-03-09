<?php

return [
    'choose-option'     => 'Choose an option',
    'is-required-field' => 'Answer required',
    'profiling'         =>
        [
            'check-and-fill-in-all-required-fields' => 'Some questions where not answered as we expected and are not valid. Please go through the profiling questions and update the answer according to the description. Near each invalid question you will see an error message with instructions on what went wrong and what we expect.',
            'heading'                               =>
                [
                    'invalid-fields' => 'Question not answered correctly',
                ],
        ],
    'validation'        =>
        [
            'in'       => 'The selected option is invalid.',
            'max'      =>
                [
                    'string' => 'The answer for this question may not be greater than :max characters.',
                ],
            'required' => 'This question can\'t be left empty. Please answer this question.',
            'string'   => 'The answer must be a string.',
        ],
];

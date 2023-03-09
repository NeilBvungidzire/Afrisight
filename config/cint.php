<?php

return [

    // Basic URL paths.
    'base_url'       => env('CINT_BASE_URL', 'https://api.cint.com'),
    'panel_url_path' => env('CINT_URL_PATH_PANEL', '/panels'),

    // Caching
    'enable_cache'   => env('CINT_CACHE_ENABLE', false),
    'cache_duration' => env('CINT_CACHE_DURATION', (60 * 60 * 24 * 30)),

    // Panels
    'panels'         => [
        'egypt'        => [
            'key'     => env('CINT_PANEL_EGYPT_KEY', null),
            'secret'  => env('CINT_PANEL_EGYPT_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'EG',
            ],
        ],
        'ethiopia'     => [
            'key'     => env('CINT_PANEL_ETHIOPIA_KEY', null),
            'secret'  => env('CINT_PANEL_ETHIOPIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'ET',
            ],
        ],
        'ghana'        => [
            'key'     => env('CINT_PANEL_GHANA_KEY', null),
            'secret'  => env('CINT_PANEL_GHANA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'GH',
            ],
        ],
        'kenya'        => [
            'key'             => env('CINT_PANEL_KENYA_KEY', null),
            'secret'          => env('CINT_PANEL_KENYA_SECRET', null),
            'country'         => [
                'iso_alpha_2' => 'KE',
            ],
            'payment_methods' => [
                'paypal' => [
                    'id'              => 1399,
                    'name'            => 'PayPal',
                    'active'          => true,
                    'threshold_money' => 4,
                ],
            ],
        ],
        'namibia'      => [
            'key'     => env('CINT_PANEL_NAMIBIA_KEY', null),
            'secret'  => env('CINT_PANEL_NAMIBIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'NA',
            ],
        ],
        'nigeria'      => [
            'key'             => env('CINT_PANEL_NIGERIA_KEY', null),
            'secret'          => env('CINT_PANEL_NIGERIA_SECRET', null),
            'country'         => [
                'iso_alpha_2' => 'NG',
            ],
            'payment_methods' => [
                'paypal' => [
                    'id'              => 1402,
                    'name'            => 'PayPal',
                    'active'          => false,
                    'threshold_money' => 5,
                ],
            ],
        ],
        'rwanda'       => [
            'key'     => env('CINT_PANEL_RWANDA_KEY', null),
            'secret'  => env('CINT_PANEL_RWANDA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'RW',
            ],
        ],
        'south_africa' => [
            'key'             => env('CINT_PANEL_SOUTH_AFRICA_KEY', null),
            'secret'          => env('CINT_PANEL_SOUTH_AFRICA_SECRET', null),
            'country'         => [
                'iso_alpha_2' => 'ZA',
            ],
            'payment_methods' => [
                'paypal' => [
                    'id'              => 353,
                    'name'            => 'PayPal',
                    'active'          => true,
                    'threshold_money' => 4,
                ],
            ],
        ],
        'tanzania'     => [
            'key'     => env('CINT_PANEL_TANZANIA_KEY', null),
            'secret'  => env('CINT_PANEL_TANZANIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'TZ',
            ],
        ],
        'uganda'       => [
            'key'     => env('CINT_PANEL_UGANDA_KEY', null),
            'secret'  => env('CINT_PANEL_UGANDA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'UG',
            ],
        ],
        'zambia'       => [
            'key'     => env('CINT_PANEL_ZAMBIA_KEY', null),
            'secret'  => env('CINT_PANEL_ZAMBIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'ZM',
            ],
        ],
        'liberia'      => [
            'key'     => env('CINT_PANEL_LIBERIA_KEY', null),
            'secret'  => env('CINT_PANEL_LIBERIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'LR',
            ],
        ],
        'sierra_leone' => [
            'key'     => env('CINT_PANEL_SIERRA_LEONE_KEY', null),
            'secret'  => env('CINT_PANEL_SIERRA_LEONE_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'SL',
            ],
        ],
        'botswana'     => [
            'key'     => env('CINT_PANEL_BOTSWANA_KEY', null),
            'secret'  => env('CINT_PANEL_BOTSWANA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'BW',
            ],
        ],
        'cameroon'     => [
            'key'     => env('CINT_PANEL_CAMEROON_KEY', null),
            'secret'  => env('CINT_PANEL_CAMEROON_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'CM',
            ],
        ],
        'ivory_coast'     => [
            'key'     => env('CINT_PANEL_IVORY_COAST_KEY', null),
            'secret'  => env('CINT_PANEL_IVORY_COAST_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'CI',
            ],
        ],
        'algeria'     => [
            'key'     => env('CINT_PANEL_ALGERIA_KEY', null),
            'secret'  => env('CINT_PANEL_ALGERIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'DZ',
            ],
        ],
        'angola'     => [
            'key'     => env('CINT_PANEL_ANGOLA_KEY', null),
            'secret'  => env('CINT_PANEL_ANGOLA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'AO',
            ],
        ],
        'drc'     => [
            'key'     => env('CINT_PANEL_DRC_KEY', null),
            'secret'  => env('CINT_PANEL_DRC_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'CD',
            ],
        ],
        'gabon'     => [
            'key'     => env('CINT_PANEL_GABON_KEY', null),
            'secret'  => env('CINT_PANEL_GABON_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'GA',
            ],
        ],
        'lesotho'     => [
            'key'     => env('CINT_PANEL_LESOTHO_KEY', null),
            'secret'  => env('CINT_PANEL_LESOTHO_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'LS',
            ],
        ],
        'morocco'     => [
            'key'     => env('CINT_PANEL_MOROCCO_KEY', null),
            'secret'  => env('CINT_PANEL_MOROCCO_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'MA',
            ],
        ],
        'mozambique'     => [
            'key'     => env('CINT_PANEL_MOZAMBIQUE_KEY', null),
            'secret'  => env('CINT_PANEL_MOZAMBIQUE_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'MZ',
            ],
        ],
        'swaziland'     => [
            'key'     => env('CINT_PANEL_SWAZILAND_KEY', null),
            'secret'  => env('CINT_PANEL_SWAZILAND_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'SZ',
            ],
        ],
        'tunisia'     => [
            'key'     => env('CINT_PANEL_TUNISIA_KEY', null),
            'secret'  => env('CINT_PANEL_TUNISIA_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'TN',
            ],
        ],
        'zimbabwe'     => [
            'key'     => env('CINT_PANEL_ZIMBABWE_KEY', null),
            'secret'  => env('CINT_PANEL_ZIMBABWE_SECRET', null),
            'country' => [
                'iso_alpha_2' => 'ZW',
            ],
        ],
    ],

];

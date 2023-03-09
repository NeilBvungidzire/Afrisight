<?php

return [

    // Cint
    'cint'            => [
        'public_id' => '4HA93JW1',
        'name'      => 'Cint',
        'active'    => true,
    ],

    // Lucid
    'lucid'            => [
        'public_id' => '08IGKRBS',
        'name'      => 'Lucid',
        'active'    => true,
    ],

    // Peanut Labs
    'peanut_labs'      => [
        'public_id' => 'RCW8AC4J',
        'name'      => 'Peanut Labs',
        'active'    => true,
        'params'    => [
            'application_id' => env('PL_APPLICATION_ID', null),
            'security_key'   => env('PL_SECURITY_KEY', null),
        ],
    ],

    // Universum Global
    'universum_global' => [
        'public_id' => 'UG34E8H3',
        'name'      => 'Universum Global',
        'active'    => true,
    ],

    // Dynata
    'dynata'           => [
        'public_id' => 'DY9F450R',
        'name'      => 'Dynata',
        'active'    => true,
    ],

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret'  => env('SPARKPOST_SECRET'),
        'options' => [
            'endpoint' => 'https://api.eu.sparkpost.com/api/v1/transmissions',
        ],
    ],

    'stripe' => [
        'model'   => App\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Oauth platforms
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'Laravel'),

    'facebook' => [
        'enabled'       => env('FACEBOOK_ENABLED', 0),
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT'),
    ],

    'google' => [
        'enabled'       => env('GOOGLE_ENABLED', 0),
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),
    ],

    'ipdata' => [
        'api_key' => env('IP_DATA_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment service providers
    |--------------------------------------------------------------------------
    */

    'flutterwave' => [
        'base_uri'   => env('FLUTTERWAVE_API_BASE_URI'),
        'version'    => env('FLUTTERWAVE_VERSION'),
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS service providers
    |--------------------------------------------------------------------------
    */

    'cm' => [
        'api_key' => env('CM_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audience Profile Service
    |--------------------------------------------------------------------------
    */

    'audience_profile_service' => [
        'base_uri' => env('AUDIENCE_PROFILE_SERVICE_BASE_URI'),
        'token'    => env('AUDIENCE_PROFILE_SERVICE_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | APILayer
    |--------------------------------------------------------------------------
    */

    'api_layer' => [
        'base_uri' => env('API_LAYER_SERVICE_BASE_URI'),
        'key'      => env('API_LAYER_SERVICE_KEY'),
    ],
];

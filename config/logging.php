<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        'flutterwave' => [
            'driver' => 'daily',
            'path' => storage_path('logs/flutterwave.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'flutterwave_transfer_response' => [
            'driver' => 'daily',
            'path' => storage_path('logs/flutterwave_transfer_response.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'reloadly' => [
            'driver' => 'daily',
            'path' => storage_path('logs/reloadly.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'cint' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cint.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'cint_payout_requests' => [
            'driver' => 'daily',
            'path' => storage_path('logs/cint_payout_requests.log'),
            'level' => 'debug',
        ],

        'survey_links' => [
            'driver' => 'daily',
            'path' => storage_path('logs/survey_links.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'api_layer' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api_layer.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'testing' => [
            'driver' => 'daily',
            'path' => storage_path('logs/testing.log'),
            'level' => 'debug',
            'days' => 14,
        ],
    ],

];

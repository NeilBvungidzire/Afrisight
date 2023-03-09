<?php

return [

    'hosts' => [
        env('ELASTIC_HOST'),
    ],

    'basic_auth' => [
        'username' => env('ELASTIC_USERNAME'),
        'password' => env('ELASTIC_PASSWORD'),
    ],

];

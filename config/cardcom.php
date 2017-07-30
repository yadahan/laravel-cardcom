<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cardcom Terminal Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the terminal below you wish to use as
    | your default terminal.
    |
    */

    'terminal' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Terminals Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the terminal settings used by your application.
    |
    */

    'terminals' => [
        'default' => [
            'terminal'     => env('CARDCOM_TERMINAL'),
            'username'     => env('CARDCOM_USERNAME'),
            'api_name'     => env('CARDCOM_API_NAME'),
            'api_password' => env('CARDCOM_API_PASSWORD'),
        ],
    ],
];

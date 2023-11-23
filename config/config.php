<?php

return [
    // routes config
    'routes' => [
        'api' => [
            'prefix' => env('API_PREFIX', 'api'),
            'middleware' => 'api',
            'namespace' => '\Api',
            'as' => 'api.',
        ],
        'admin' => [
            'prefix' => env('ADMIN_PREFIX', 'admin'),
            'middleware' => 'admin',
            'namespace' => '\Admin',
            'as' => 'admin.',
        ],
        'web' => [
            'prefix' => env('WEB_PREFIX', '/'),
            'middleware' => 'web',
            'namespace' => '\Web',
            'as' => 'web.',
        ],
    ],
];

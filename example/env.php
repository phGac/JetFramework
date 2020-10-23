<?php

return [
    'mode' => 'development',

    'autoload' => [
        __DIR__ . '/src/middlewares',
        __DIR__ . '/src/entities',
        __DIR__ . '/src/controllers',
    ],

    'security' => [
        'secret' => 'Tmcl0qp48-wc3.opw',
        'request_sanitizer' => true, // sanitize request body and query
    ],

    'views' => [
        'path' => __DIR__ . '/src/views',
        'cache' => [
            'allow' => false,
            'time' => '+1 month',
            'path' => __DIR__ . '/cache',
        ]
    ],

    'db' => [

        'development' => [
            'driver' => 'pdo_mysql',
            'user'     => 'root',
            'password' => null,
            'dbname'   => 'jet_db',
            'host'   => '127.0.0.1',
            'port' => null
        ],

        'production' => [
            'driver' => 'pdo_mysql',
            'user'     => 'jet_user',
            'password' => 'Jet@db.cl-45',
            'dbname'   => 'jet_db',
            'host'   => '127.0.0.1',
            'port' => 1025
        ],

    ]
];
<?php

return [
    // Enverioments Vars ($_ENV)
    'gloval' => [
        'mode' => 'development',
        # 'mail_email' => 'contact@test.net',
        # 'mail_username' => 'Contact'

        # Currency converter
        'rapidapi_host' => 'currencyconverter.p.rapidapi.com',
        'rapidapi_key' => 'xxxxxx'
    ],

    'autoload' => [
        __DIR__ . '/src/middlewares',
        __DIR__ . '/src/App/Entities',
        __DIR__ . '/src/App/Services',
        __DIR__ . '/src/App/Controllers',
    ],

    'services' => [
        # [  id,     className,    arguments ]
        # [ 'Mail', 'MailService', [ '$gloval.mail_email', '$gloval.mail_username' ] ], # `$gloval` uses gloval array
        [ 'Currency', 'CurrencyService', [ '$gloval.rapidapi_host', '$gloval.rapidapi_key' ] ]
    ],

    'security' => [
        'secret' => 'se5V4al6XAxBSsT',
        'request_sanitizer' => false, // auto sanitize request body and query
    ],

    'db' => [
        'development' => [
            'driver'   => 'pdo_mysql',  // PDO driver
            'user'     => 'root',
            'password' => null,
            'dbname'   => 'jet_db',
            'host'     => '127.0.0.1',
            'port'     => null          // default port
        ],
        'testing' => [
        ],
        'production' => [
        ]
    ]
];

<?php

$router = new AltoRouter();

$router->map('GET', '/currency', [ '\App\Controllers\CurrencyController@index' ], 'currency.home');
$router->map('POST', '/currency/new', [ '\App\Controllers\CurrencyController@create' ]);
$router->map('GET', '/currency/new', [ '\App\Controllers\CurrencyController@new_' ]);

return $router;
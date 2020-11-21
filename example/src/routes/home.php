<?php

$router = new AltoRouter();

$router->map('GET', '/', [ '\App\Controllers\HomeController@index' ], 'home');

return $router;
<?php

global $env;

$homeRoutes = require __DIR__ . '/routes/home.php';
$currencyRoutes = require __DIR__ . '/routes/currency.php';

$app = new Jet\App();
$app->configure($env);

$app->addRoutes($homeRoutes);
$app->addRoutes($currencyRoutes);

$app->ready();

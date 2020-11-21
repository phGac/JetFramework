<?php

require_once __DIR__ . '/../vendor/autoload.php';

$env = require_once __DIR__ .'/env.php';

$db = new Jet\Db\Connection($env['db'][ $env['gloval']['mode'] ]['driver']);
if(! $db->connect(
    $env['db'][ $env['gloval']['mode'] ]['dbname'],
    $env['db'][ $env['gloval']['mode'] ]['user'],
    $env['db'][ $env['gloval']['mode'] ]['password'],
    $env['db'][ $env['gloval']['mode'] ]['host'],
    $env['db'][ $env['gloval']['mode'] ]['port']
))
{
    $error = $db->getLastException();
    echo 'Connection error: ' . $error->getMessage();
    exit(1);
}

require_once __DIR__ . '/src/app.php';

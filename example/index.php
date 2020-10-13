<?php

require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/Home.php';

use Jet\Request\Request;
use Jet\Request\Response;

function callme(Request $req, Response $res)
{
    $res->send('function:callme,');
}

$app = new Jet\App();

$app->use(
    '/',
    function(Request $req, Response $res, callable $next) {
        $res->send('uno');
        $next();
    },
    function(Request $req, Response $res, callable $next) {
        $res->send('dos');
        $next();
    }
);

$app->get('/', function(Request $req, Response $res, callable $next) {
    $res->send('Hola')
        ->end();
});

$app->get('/hi', array( 'callme', function(Request $req, Response $res) {
    $res->send('function:anonymous')
        ->end();
}), 'hello');

$app->get('/google', function(Request $req, Response $res) {
    $res->redirect('https://www.google.com');
});

$app->get('/redirect', function(Request $req, Response $res) {
    $res->redirect('[hello]'); // => path: /hi
});

$app->get('/home', '\Controller\Home@index', 'home');

$app->get('/exception', function(Request $req, Response $res) {
    throw new Exception('Something');
});

$app->ready();
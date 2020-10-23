<?php

require_once __DIR__ . '/../vendor/autoload.php';
$env = require_once __DIR__ .'/env.php';

$db = new Jet\Db\Connection($env['db'][ $env['mode'] ]['driver']);
if(! $db->connect(
    $env['db'][ $env['mode'] ]['dbname'],
    $env['db'][ $env['mode'] ]['user'],
    $env['db'][ $env['mode'] ]['password'],
    $env['db'][ $env['mode'] ]['host'],
    $env['db'][ $env['mode'] ]['port']
))
{
    $error = $db->getLastException();
    echo 'Connection error: ' . $error->getMessage();
}

$app = new Jet\App();
$app->configure($env);
unset($env);

$app->use(
    '/',
    function(Jet\Request\Request $req, Jet\Request\Response $res, callable $next) {
        $res->send('uno');
        $next();
    },
    function(Jet\Request\Request $req, Jet\Request\Response $res, callable $next) {
        $res->send('dos');
        $next();
    }
);

$app->get('/', function(Jet\Request\Request $req, Jet\Request\Response $res, callable $next) {
    $res->send('Hola')
        ->end();
});

$app->get('/hi', array( 'isNotLoggedIn', function(Jet\Request\Request $req, Jet\Request\Response $res) {
    $res->send('function:anonymous')
        ->end();
}), 'hello');

$app->get('/google', function(Jet\Request\Request $req, Jet\Request\Response $res) {
    $res->redirect('https://www.google.com');
});

$app->get('/redirect', function(Jet\Request\Request $req, Jet\Request\Response $res) {
    $res->redirect('[hello]'); // => path: /hi
});

$app->get('/home', '\Controllers\Home@index', 'home');

$app->get('/exception', function(Jet\Request\Request $req, Jet\Request\Response $res) {
    throw new Exception('Something');
});

$app->get('/private', array('isLoggedIn', '\Controllers\Home@index'), 'private');

$app->get('/view', function(Jet\Request\Request $req, Jet\Request\Response $res) {
    $res->render('index.php', [ 'title' => 'My Title' ]);
});

$app->ready();
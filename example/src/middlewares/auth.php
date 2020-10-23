<?php

function isLoggedIn(Jet\Request\Request $req, Jet\Request\Response $res, callable $next)
{
    $session = $req->getSession();
    $session->start();
    if ($session->get('user') === null) {
        $res->redirect('[home]');
    }
    $next();
}

function isNotLoggedIn(Jet\Request\Request $req, Jet\Request\Response $res, callable $next)
{
    $session = $req->getSession();
    $session->start();
    if ($session->get('user') !== null) {
        $res->redirect('[home]');
    }
    $next();
}
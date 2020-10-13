<?php

namespace Jet\Controller;

use Jet\Request\Request;
use Jet\Request\Response;

interface Controller
{
    function index(Request $req, Response $res);
    function create(Request $req, Response $res);
    function show(Request $req, Response $res);
    function edit(Request $req, Response $res);
    function update(Request $req, Response $res);
    function destroy(Request $req, Response $res);
}
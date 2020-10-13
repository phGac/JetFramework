<?php

namespace Controller;

class Home implements \Jet\Controller\Controller
{

    function index(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement index() method.
        $res->send('Home')
            ->end();
    }

    function create(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement create() method.
    }

    function show(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement show() method.
    }

    function edit(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement edit() method.
    }

    function update(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement update() method.
    }

    function destroy(\Jet\Request\Request $req, \Jet\Request\Response $res)
    {
        // TODO: Implement destroy() method.
    }
}
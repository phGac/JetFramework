<?php

namespace App\Controllers;

use CurrencyService;
use Jet\Controller\Controller;
use Jet\Request\Request;
use Jet\Request\Response;

class HomeController implements Controller
{
	function index(Request $req, Response $res)
	{
	    $res->render('home/index.php', [ 'title' => 'Funciona! :D' ]);
	}

    function new_(Request $req, Response $res)
    {
        // TODO: Implement new_() method.
    }

	function create(Request $req, Response $res)
	{
		// TODO: Implement create() method.
	}

	function show(Request $req, Response $res)
	{
		// TODO: Implement show() method.
	}

	function edit(Request $req, Response $res)
	{
		// TODO: Implement edit() method.
	}

	function update(Request $req, Response $res)
	{
		// TODO: Implement update() method.
	}

	function destroy(Request $req, Response $res)
	{
		// TODO: Implement destroy() method.
	}

}

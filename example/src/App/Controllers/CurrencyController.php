<?php

namespace App\Controllers;

use CurrencyService;
use Jet\Controller\Controller;
use Jet\Request\Request;
use Jet\Request\Response;

class CurrencyController implements Controller
{
	function index(Request $req, Response $res)
	{
        global $app;
        /** @var CurrencyService $currency */
        $currency = $app->get('Currency');

		$res->render('currency/index.php', [
		    'actual' => $currency->getActual()
        ]);
	}

    function new_(Request $req, Response $res)
    {
        $session = $req->getSession();
        $session->start();

        $res->render('currency/new.php', [
            'csrf' => $req->setCsrf('currency:new')
        ]);
    }

	function create(Request $req, Response $res)
	{
        $session = $req->getSession();
        $session->start();

		$res->json([
		    'csrf' => $req->isValidCsrf('currency:new', $req->body['csrf'])
        ]);
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

<?php


namespace Jet;


use AltoRouter;
use Exception;
use Jet\Middleware\Route;

class App
{
    /**
     * @var Request\Request
     */
    private $request;
    /**
     * @var Request\Response
     */
    private $response;
    /**
     * @var null
     */
    private $router;
    /**
     * @var Middleware\Middleware
     */
    private $middleware;
    /**
     * @var callable
     */
    private $error404;

    function __construct()
    {
        $this->router = new AltoRouter();
        $this->request = new Request\Request();
        $this->response = new Request\Response($this->router);
        $this->middleware = new Middleware\Route();
        $this->error404 = null;
    }

    /**
     * @param callable $func
     * @throws Exception
     */
    function on404(callable $func)
    {
        if(! is_callable($func)) throw new Exception('Handler Error: is not callable');
        $this->error404 = $func;
    }

    /**
     * @param string $path
     * @param mixed ...$handlers
     */
    function use($path, ...$handlers)
    {
        $this->middleware->on($path);
        foreach ($handlers as $handler) {
            $this->middleware->use($handler);
        }
    }

    /**
     * @param AltoRouter $router
     * @throws Exception
     */
    function addRoutes($router)
    {
        $this->router->addRoutes($router->getRoutes());
    }

    /**
     * Map a route to a target
     *
     * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    function request($method, $route, $target, $name = null)
    {
        $this->router->map($method, $route, $target, $name);
    }

    /**
     * Map a route to a target
     *
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    function get($route, $target, $name = null)
    {
        $this->router->map('GET', $route, $target, $name);
    }

    /**
     * Map a route to a target
     *
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    function post($route, $target, $name = null)
    {
        $this->router->map('POST', $route, $target, $name);
    }

    /**
     * Map a route to a target
     *
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    function put($route, $target, $name = null)
    {
        $this->router->map('PUT', $route, $target, $name);
    }

    /**
     * Map a route to a target
     *
     * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
     * @param mixed $target The target where this route should point to. Can be anything.
     * @param string $name Optional name of this route. Supply if you want to reverse route this url in your application.
     * @throws Exception
     */
    function delete($route, $target, $name = null)
    {
        $this->router->map('DELETE', $route, $target, $name);
    }

    function ready()
    {
        global $env;

        $match = $this->router->match();
        if(is_array($match)) {
            $this->request->params = $match['params'];
            $handlers = (is_array($match['target'])) ? $match['target'] : array( $match['target'] );

            $this->middleware->on($this->request->path);
            foreach ($handlers as $handler)
            {
                if(is_callable($handler)) {
                    $this->middleware->use($handler);
                    //call_user_func($handler, $this->request, $this->response);
                }
                else {
                    $handler = explode('@', $handler);
                    if(count($handler) < 2 || ! class_exists($handler[0])) throw new Exception("Handler Error: is not callable on path ({$this->request->path}) with handler ({$match['target']})");
                    $this->middleware->use([ (new $handler[0]()), $handler[1] ]);
                    //call_user_func([ (new $handler[0]()), $handler[1] ], $this->request, $this->response);
                }
            }
            if(isset($env['security']) && is_array($env['security']) && $env['security']['request_sanitizer']) {
                $this->request->body = $this->request->sanitize( $this->request->body );
                $this->request->query = $this->request->sanitize( $this->request->query );
            }

            $this->middleware->go($this->request, $this->response);
        }
        else {
            if(is_callable($this->error404)) {
                call_user_func($this->error404, $this->request, $this->response);
            }
            else {
                $this->response
                    ->status(404)
                    ->send('404 Not Found')
                    ->end();
            }
        }
    }
}
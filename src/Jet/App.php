<?php


namespace Jet;


use AltoRouter;
use Exception;
use Jet\Service\Service;
use Jet\Service\ServiceContainer;
use stdClass;

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
     * @var AltoRouter
     */
    private $router;
    /**
     * @var Middleware\Middleware
     */
    private $middleware;
    /**
     * @var ServiceContainer
     */
    private $container;


    /**
     * @var callable
     */
    private $error404;
    /**
     * @var bool
     */
    private $request_sanitize;

    function __construct()
    {
        $this->router = new AltoRouter();
        $this->request = new Request\Request();
        $this->response = new Request\Response($this->router);
        $this->middleware = new Middleware\Route();
        $this->container = new ServiceContainer();
        $this->error404 = null;
        $this->request_sanitize = true;
    }

    /**
     * @param array $paths
     * @throws Exception
     */
    private function autoload(array $paths)
    {
        foreach ($paths as $path) {
            if(is_file($path)) {
                include $path;
            }
            else if(is_dir($path)) {
                foreach (glob($path . '/*.php') as $file) {
                    include $file;
                }
            }
            else {
                throw new Exception('File or Folder not found');
            }
        }
    }

    function configure(array $config)
    {
        if(isset($config['gloval']) && is_array($config['gloval'])) {
            Env::load($config['gloval']);
        }
        if(isset($config['autoload']) && is_array($config['autoload'])) {
            $this->autoload($config['autoload']);
        }
        if(isset($config['views']) && is_array($config['views'])) {
            if(isset($config['views']['path']) && isset($config['views']['cache']) && isset($config['views']['cache']['path'])) {
                $views_directory = $config['views']['path'];
                $cache_directory = $config['views']['cache']['path'];
                $time = (isset($config['views']['cache']['allow']) && $config['views']['cache']['allow']) ? $config['views']['cache']['time'] : null;
                $this->response->setViews($views_directory, $cache_directory, $time);
            }
        }
        if(isset($config['security'])) {
            if(isset($config['security']['secret'])) {
                $this->request->setSecret($config['security']['secret']);
            }
            if(isset($config['security']['request_sanitize'])) {
                $this->request_sanitize = $config['security']['request_sanitize'];
            }
        }
        if(isset($config['services']) && is_array($config['services'])) {
            foreach ($config['services'] as $service) {
                $id = $service; $name = $service; $arguments = [];
                if( is_array($service) ) {
                    $id = $service[0];
                    $name = $service[1];
                    $arguments = (isset($service[2]) && is_array($service[2])) ? $service[2] : [];
                }

                $this->add($id, $name, $arguments);
            }
        }
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
     * @param callable ...$handlers
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
    function map($method, $route, $target, $name = null)
    {
        $this->router->map($method, $route, $target, $name);
    }

    /**
     * Get a service
     *
     * @param string $id
     * @return stdClass|Service
     * @throws Exception
     */
    function get($id)
    {
        return $this->container->get($id);
    }

    /**
     * Add a service
     *
     * @param string $id
     * @param string $class_name
     * @param array $arguments
     * @return void
     * @throws Exception
     */
    function add($id, $class_name, $arguments = [])
    {
        $this->container->set($id, $class_name, $arguments);
    }

    function ready()
    {
        $match = $this->router->match();
        if(is_array($match)) {
            $this->request->params = $match['params'];
            $handlers = (is_array($match['target'])) ? $match['target'] : array( $match['target'] );

            $this->middleware->on($this->request->path);
            foreach ($handlers as $index => $handler)
            {
                if(is_callable($handler)) {
                    $this->middleware->use($handler);
                }
                else {
                    $handler = explode('@', $handler);
                    if(count($handler) < 2 || ! class_exists($handler[0])) throw new Exception("Handler Error: is not callable on path ({$this->request->path}) with handler ({$handlers[$index]})");
                    $this->middleware->use([ (new $handler[0]()), $handler[1] ]);
                }
            }
            if($this->request_sanitize) {
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
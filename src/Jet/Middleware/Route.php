<?php


namespace Jet\Middleware;


class Route extends Middleware
{
    private $path;

    function on($path)
    {
        $this->path = ($path == '*') ? '/.+/' : $path;
        return $this;
    }

    function use(callable $use)
    {
        $this->callables[$this->path][] = $use;
    }

    function go(...$params)
    {
        $request = $params[0];
        $response = $params[1];

        foreach ($this->callables as $path => $use) {
            if(preg_match('/^(\/).+(\/)$/', $path)) {
                if(! $this->isValidRegex($path))
                    throw new \Exception("La expresión regular de la ruta [$path] del míddleware no es válida");
                else if(preg_match($path, $request->path) == 0)
                    continue;
            }
            else if($path != $request->path) {
                continue;
            }

            $pipeline = array_reduce(
                array_reverse($this->callables[$path]), $this->carry()
            );
            $pipeline($request, $response);
        }
    }

    protected function carry()
    {
        return function($stack, $pipe) {
            return function($request, $response) use ($stack, $pipe) {
                if(is_callable($pipe)) {
                    $pipe($request, $response, function() use ($stack, $request, $response) {
                        $stack($request, $response);
                    });
                }
            };
        };
    }

    /**
     * @param string $str
     * @return bool
     */
    private function isValidRegex($str) {
        return @preg_match($str, '') !== FALSE;
    }
}
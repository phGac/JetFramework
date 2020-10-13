<?php

namespace Jet\Middleware;

class Middleware {
    protected $callables;

    function __construct($callables = [])
    {
        $this->callables = $callables;
    }

    function use(callable $use) {
        $this->callables[] = $use;
        return $this;
    }

    function go(...$params) {
        $pipeline = array_reduce(
            array_reverse($this->callables), $this->carry()
        );
        return $pipeline($params);
    }

    protected function carry()
    {
        return function($stack, $pipe) {
            return function($params) use ($stack, $pipe) {
                if(is_callable($pipe)) {
                    $pipe($params, function() use ($stack, $params) {
                        $stack($params);
                    });
                }
            };
        };
    }
}
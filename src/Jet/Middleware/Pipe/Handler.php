<?php


namespace Jet\Middleware\Pipe;


use Closure;

interface Handler
{
    /**
     * @param $value
     * @param Closure $next
     * @return mixed
     */
    function handle($value, Closure $next);
}
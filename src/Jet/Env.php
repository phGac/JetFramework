<?php


namespace Jet;


class Env
{
    static function load(array $envs)
    {
        foreach ($envs as $name => $value) {
            $_ENV["gloval.{$name}"] = $value;
        }
    }

    static function get($name)
    {
        return (isset($_ENV["gloval.{$name}"])) ? $_ENV["gloval.{$name}"] : -1;
    }
}
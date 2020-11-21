<?php


namespace Jet\Service;

use Exception;
use Jet\Env;

class ServiceContainer
{
    private $services;

    private function getGlovalEnv($name)
    {
        if(! is_string($name) || ! preg_match('/^\$gloval\.([A-Za-z0-9-_]+)/', $name, $matches)) return $name;
        return Env::get($matches[1]);
    }

    private function getValueArguments($arguments)
    {
        $args = [];
        foreach ($arguments as $arg) {
            if(($value = $this->getGlovalEnv($arg)) !== -1)
                $args[] = $value;
            else
                $args[] = $arg;
        }
        return $args;
    }

    /**
     * @param string $id
     * @return bool
     */
    function has($id)
    {
        return (isset( $this->services[ $id ] ));
    }

    /**
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    function get($id)
    {
        if(! $this->has($id)) throw new Exception('Service not found');
        if($this->services[ $id ]['instance'] === null) {
            $arguments = $this->getValueArguments($this->services[ $id ]['arguments']);
            $class = $this->services[ $id ]['class'];
            $instance = new $class( ...$arguments );
            $this->services[ $id ]['is_service'] = ($instance instanceof Service);
            if($this->services[ $id ]['is_service'])  $instance->onCreate();
            $this->services[ $id ]['instance'] = $instance;
        }

        if($this->services[ $id ]['is_service']) {
            /** @var Service $instance */
            $instance->onCall();
        }

        return $this->services[ $id ]['instance'];
    }

    /**
     * @param string $id
     * @param string $class_name
     * @param array $arguments
     * @return void
     * @throws Exception
     */
    function set($id, $class_name, $arguments = [])
    {
        if($this->has($id)) throw new Exception('Id already used');
        if(! class_exists($class_name)) throw new Exception('Class not found');

        $this->services[ $id ] = [
            'instance' => null,
            'class' => $class_name,
            'is_service' => false,
            'arguments' => $arguments
        ];
    }
}
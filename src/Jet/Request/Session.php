<?php

namespace Jet\Request;

use Exception;
use Jet\Security\Encrypt;

class Session
{
    private $started;
    private $encrypt;
    private $data;

    function __construct()
    {
        $this->started = false;
        $this->encrypt = true;
    }

    function start($options = [])
    {
        if($this->started) return;
        $this->started = true;
        session_start([
            'name' => $options['name'] ?? 'KukiS',
            'cookie_httponly' => true
        ]);
        $this->data = [];
        if(isset($_SESSION['_d'])) {
            $data = Encrypt::decrypt($_SESSION['_d'], 'AfgDrt6v35p7');
            $this->data = json_decode($data);
        }
    }

    /**
     * @param string $name
     * @param $value
     */
    function set($name, $value)
    {
        if(! $this->started) throw new Exception('Start session with `start` method');
        if(gettype($value) !== 'object')
            $this->data[$name] = $value;
        else
            $this->data[$name] = serialize($value);
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws Exception
     */
    function get($name)
    {
        if(! $this->started) throw new Exception('Start session with `start` method');
        if(! isset($this->data[$name])) return null;

        $data = $this->data[$name];
        if(gettype($data) == 'string')  {
            $value = @unserialize($data);
            if($value !== false) $data = $value;
        }
        return $data;
    }

    function save()
    {
        $secret = (isset($_ENV['env']['encrypt']['secret'])) ? $_ENV['env']['encrypt']['secret'] : 'AfgDrt6v35p7';
        $data = json_encode($this->data, true);
        $_SESSION['_d'] = Encrypt::encrypt($data, $secret);
    }

    function destroy()
    {
        if(! $this->started) return;
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(), '', time() - 3600, '/');
        @session_regenerate_id(true);
    }
}
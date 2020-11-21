<?php

namespace Jet\Request;

use Exception;
use Jet\Security\Encrypt;
use stdClass;

class Session
{
    /**
     * @var bool
     */
    private $started;
    /**
     * @var bool
     */
    private $encrypt;
    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $secret;

    /**
     * Session constructor.
     * @param bool $encrypt
     * @param string|null $secret
     */
    function __construct($encrypt, $secret)
    {
        $this->started = false;
        $this->data = new stdClass();
        $this->encrypt = $encrypt;
        $this->secret = $secret;
    }

    function start($options = [])
    {
        if($this->started) return;
        $this->started = true;
        session_start([
            'name' => $options['name'] ?? 'KukiS',
            'cookie_httponly' => true
        ]);
        if(isset($_SESSION['_d'])) {
            $data = Encrypt::decrypt($_SESSION['_d'], $this->secret);
            $this->data = json_decode($data);
        }
    }

    /**
     * @param string $name
     * @param $value
     * @throws Exception
     */
    function set($name, $value)
    {
        if(! $this->started) throw new Exception('Start session with `start` method');
        if($value !== null) {
            if(gettype($value) !== 'object' && ! is_array($value))
                $this->data->{$name} = $value;
            else
                $this->data->{$name} = serialize($value);
        }
        else if(isset($this->data->{$name})) {
            unset($this->data->{$name});
        }
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws Exception
     */
    function get($name)
    {
        if(! $this->started) throw new Exception('Start session with `start` method');
        if(! isset($this->data->{$name})) return null;

        $data = $this->data->{$name};
        if(is_string($data))  {
            $value = @unserialize($data);
            if($value !== false) $data = $value;
        }
        return $data;
    }

    function save()
    {
        $data = json_encode($this->data, true);
        $_SESSION['_d'] = Encrypt::encrypt($data, $this->secret);
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
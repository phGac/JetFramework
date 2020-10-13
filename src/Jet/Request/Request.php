<?php

namespace Jet\Request;

use Jet\Request\Client\Ip;

class Request {
    public $body;
    public $query;
    public $cookies;
    public $hostname;
    public $params;
    public $ip;
    public $path;
    public $protocol;
    public $originalUrl;
    public $method;

    private $session;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->body = $this->getBody();
        $this->query = $_GET;
        $this->cookies = $_COOKIE;
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->params = [];
        $this->ip = Ip::getIpClient();
        $this->path = strtok($_SERVER['REQUEST_URI'], '?');
        $this->protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $this->originalUrl = $this->protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    private function getBody() {
        switch ($this->method) {
            case 'POST':
            case 'post':
                return $_POST;
            case 'PUT':
            case 'put':
            case 'DELETE':
            case 'delete':
                parse_str(file_get_contents('php://input'), $_PUT_DELETE);
                return $_PUT_DELETE;
            default:
                return [];
        }
    }

    /**
     * @param string $method
     * @param string $name
     * @param bool $deleteOnMatch
     */
    public function setPathWithParameter($method, $name, $deleteOnMatch = true)
    {
        $path = '/';
        switch ($method) {
            case 'GET':
            case 'get':
                if(isset($this->query[$name])) {
                    $path = $this->query[$name];
                    if($deleteOnMatch) unset($this->query[$name]);
                }
                break;
            case 'POST':
            case 'post':
            case 'PUT':
            case 'put':
                if(isset($this->body[$name])) {
                    $path = $this->body[$name];
                    if($deleteOnMatch) unset($this->body[$name]);
                }
                break;
        }

        $this->path = $path;
    }

    public function getSession()
    {
        if(! $this->session) $this->session = new Session();
        return $this->session;
    }
}
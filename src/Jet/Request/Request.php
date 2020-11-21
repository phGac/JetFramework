<?php

namespace Jet\Request;

use Exception;
use Jet\Request\Client\Ip;
use Jet\Security\Token;
use voku\helper\AntiXSS;

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

    private $secret;
    private $session;
    private $antiXss;

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
        $this->antiXss = new AntiXSS();
        $this->secret = null;
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

    /**
     * @param string $secret
     */
    function setSecret($secret)
    {
        $this->secret = $secret;
    }

    public function getSession()
    {
        if(! $this->session) $this->session = new Session(true, $this->secret);
        return $this->session;
    }

    public function getAntiXss()
    {
        return $this->antiXss;
    }

    /**
     * @param string|string[] $sanitize
     * @return array|mixed|string
     */
    public function sanitize($sanitize)
    {
        return $this->antiXss->xss_clean($sanitize);
    }

    function isXssFound()
    {
        return $this->antiXss->isXssFound();
    }

    /**
     * @param string $formId
     * @return string
     * @throws Exception
     */
    function setCsrf($formId)
    {
        $session = $this->getSession();
        $csrf = Token::csrf();
        $session->set('form:csrf:' . $formId, [ $csrf, time() ]);
        $session->save();
        return $csrf;
    }

    /**
     * @param string $formId
     * @param string $csrf
     * @param int $limit expira en X segundos, por defecto 1 Hora (3600 segundos)
     * @return bool
     * @throws Exception
     */
    function isValidCsrf($formId, $csrf, $limit = 3600)
    {
        $session = $this->getSession();
        $data = $session->get('form:csrf:' . $formId);
        if($data == null) throw new Exception('FormId not found');
        if($data[0] !== $csrf) return false;
        if(time() >= ($data[1] + $limit)) {
            $session->set('form:csrf:' . $formId, null);
            $session->save();
            return false;
        }

        return true;
    }
}
<?php

namespace Jet\Request;

//use Jenssegers\Blade\Blade;

use AltoRouter;
use Exception;

class Response {
    /**
     * @var string absolute directory public path
     */
    private $public;
    /**
     * @var string absolute directory resources path
     */
    private $resources;
    /**
     * @var string data text
     */
    private $text;
    /**
     * @var string[] HTTP HEADERS responses
     */
    private $headers;
    /**
     * @var int status code
     */
    private $status;
    /**
     * @var string file path
     */
    private $filepath;
    /**
     * @var Blade Maneja el renderizado de vistas
     */
    private $blade;
    private $router;

    /**
     * Response constructor.
     * @param AltoRouter $router
     * @param string|null $public
     * @param string|null $resources
     */
    public function __construct(&$router, $public = null, $resources = null)
    {
        $this->router = $router;
        $this->public = $public;
        $this->resources = $resources;
        $this->text = '';
        $this->headers = [];
        $this->status = 0;
        $this->filepath = null;
        $this->blade = null;
    }

    /**
     * @param string $views
     * @param string $cache
     */
    public function setViews($views, $cache)
    {
        if(! is_dir($views) || ! is_dir($cache)) {
            throw new Exception('El directorio no existe');
        }
        $this->blade = new Blade($views, $cache);
    }

    /**
     * @param int $status_code
     * @return $this
     */
    public function status($status_code)
    {
        $this->status = $status_code;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $to
     * @param array $params
     * @throws Exception
     */
    public function redirect($to, $params = [])
    {
        if(preg_match('/^\[.+\]$/', $to)) {
            $name = preg_replace('/[\[|\]]/', '', $to);
            $to = $this->router->generate($name, $params);
        }
        $this->headers[] = "Location: $to";
        $this->end();
    }

    public function json($data)
    {
        $this->headers[] = 'Content-type: application/json';
        $this->text .= json_encode($data);
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function send($text)
    {
        $this->text .= $text;
        return $this;
    }

    /**
     * @param string $filepath
     * @param string|null $name
     * @return $this
     */
    public function sendFile($filepath, $name = null)
    {
        $attachment_location = $this->resources . '/' . $filepath;
        $name = ($name != null) ? $name : basename($filepath);
        if (file_exists($attachment_location)) {
            array_push(
                $this->headers,
                $_SERVER["SERVER_PROTOCOL"] . ' 200 OK',
                'Cache-Control: public', // needed for internet explorer
                'Content-Type: application/zip',
                'Content-Transfer-Encoding: Binary',
                'Content-Length:' . filesize($attachment_location),
                "Content-Disposition: attachment; filename=$name"
            );
            $this->filepath = $attachment_location;
        } else {
            throw new Exception('El archivo no existe');
        }

        return $this;
    }

    public function render(string $path, array $params = [], callable $callback = null) {
        if($this->blade == null)
            throw new Exception('Debe indicar el directorio de vistas con setViews()');
        $text = $this->blade->render($path, $params);
        if($callback != null) {
            $callback($text, $this);
        }
        else {
            $this->text .= $text;
            $this->end();
        }
    }

    public function header(string $name, string $value) {
        $this->headers[] = "$name: $value";
        return $this;
    }

    public function cookie(string $name, $value, $expires = null) {
        /// setcookie($name, $value);
        /// return $this;
        throw new Exception('Not Working');
    }

    public function clear(string $only = null)
    {
        switch ($only) {
            case null:
            case '':
                $this->headers = [];
                $this->text = '';
                $this->status = 0;
                break;
            case 'HEADERS':
                $this->headers = [];
                break;
            case 'TEXT':
                $this->text = '';
                break;
            case 'STATUS':
                $this->status = 0;
                break;
        }

        return $this;
    }

    public function end() {
        if($this->text != '')
            echo $this->text;
        if($this->status > 0)
            http_response_code($this->status);
        if($this->filepath != null)
            readfile($this->filepath);

        foreach ($this->headers as $value) {
            header($value);
        }

        die();
    }
}
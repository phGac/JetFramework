<?php


namespace Jet\Config;


abstract class AppConfiguration
{
    protected $paths = [
        'App\\Controllers' => null,
        'App\\Entities' => null,
        'App\\Services' => null,
        'Util' => null
    ];
    protected $security = [
        'secret' => null,
        'request_sanitizer' => true
    ];
    protected $cache = [
        'views' => [
            'allow' => true,
            'time' => '+1 day'
        ]
    ];
    protected $db = [
        'development' => [],
        'testing' => [],
        'production' => []
    ];

    function loadFromArray(array $data)
    {
        $this->paths = $data['paths'];
        $this->security = $data['security'];
        $this->db = $data['db'];
    }
}
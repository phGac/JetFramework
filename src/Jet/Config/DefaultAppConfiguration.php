<?php


namespace Jet\Config;


class DefaultAppConfiguration extends AppConfiguration
{
    function __construct($home_path)
    {
        $this->paths = [
            'App\Controllers' => $home_path . '/src/App/Controllers',
            'App\Entities' => $home_path . '/src/App/Entities',
            'App\\Services' => $home_path . '/src/App/Services',
        ];
        $this->security['request_sanitizer'] = true;
    }
}
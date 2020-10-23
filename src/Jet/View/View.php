<?php


namespace Jet\View;


use DateTime;
use Exception;

class View
{
    /**
     * @var string
     */
    private $template;
    /**
     * @var bool
     */
    private $cached;

    /**
     * @var string
     */
    private $output;
    /**
     * @var ViewRender
     */
    private $renderer;

    /**
     * @var string
     */
    private $views_folder;
    /**
     * @var string
     */
    private $cache_folder;
    /**
     * @var int
     */
    private $cache_time;

    /**
     * View constructor.
     * @param $template
     * @param string $views_folder
     * @param string $cache_folder
     */
    function __construct($template, $views_folder, $cache_folder)
    {
        $this->template = $template;
        $this->cached = null;
        $this->output = '';
        $this->renderer = new ViewRender($views_folder, $cache_folder);
        $this->views_folder = $views_folder;
        $this->cache_folder = $cache_folder;
        $this->cache_time = 0;
        $this->isCached();
    }

    /**
     * @return bool
     */
    function isCached()
    {
        if($this->cached === null) {
            $template_filename = basename($this->template);
            $template_filename_regex = preg_quote($template_filename);
            $files = glob($this->cache_folder . DIRECTORY_SEPARATOR . '*');
            $filename = null;
            foreach ($files as $file) {
                $filename = basename($file);
                if(preg_match("/^({$template_filename_regex})\.([0-9]+)/", $filename, $matches)) {
                    $this->cache_time = intval($matches[2]);
                    break;
                }
            }

            $this->cached = (time() < $this->cache_time);
            return $this->cached;
        }
        else {
            return $this->cached;
        }
    }

    /**
     * @param array $attributes
     * @return false|string
     * @throws Exception
     */
    function render($attributes = [])
    {
        $template = $this->template;
        if($this->cached) $template .= '.' . $this->cache_time;
        return $this->renderer->render($template, $attributes, $this->cached);
    }

    /**
     * @param string $max_time
     * @throws Exception
     */
    function save($max_time)
    {
        $datetime = new DateTime();
        $datetime->modify($max_time);
        $time = strtotime($datetime->format('Y-m-d H:i:s'));
        $output = $this->renderer->renderAsCacheFile($this->template);

        $last_cache_file = $this->cache_folder . DIRECTORY_SEPARATOR . $this->template . '.' . $this->cache_time;
        $new_cache_file = $this->cache_folder . DIRECTORY_SEPARATOR . $this->template . '.' . $time;

        if($this->cache_time !== 0)
            if(! unlink($last_cache_file)) return;

        file_put_contents($new_cache_file, $output);
    }
}
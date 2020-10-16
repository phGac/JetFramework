<?php


namespace Jet\View;


use Exception;

class View
{
    /**
     * @var string
     */
    protected $views_folder;
    /**
     * @var string|null
     */
    protected $layout;

    /**
     * View constructor.
     * @param string $views_folder
     */
    function __construct($views_folder = '')
    {
        $this->views_folder = $views_folder;
        $this->layout = null;
    }

    /**
     * @param string $folder
     */
    function setViewsFolder($folder)
    {
        $this->views_folder = $folder;
    }

    function getViewsFolder()
    {
        return $this->views_folder;
    }

    /**
     * @param string|null $layout
     * @throws Exception
     */
    function setLayout($layout)
    {
        if($layout === '' || $layout === null) {
            $this->layout = $layout;
        }
        else {
            $path = $this->views_folder . DIRECTORY_SEPARATOR . $layout;
            if(! is_file($path)) {
                throw new Exception('Layout File not found');
            }
            $this->layout = $layout;
        }
    }

    /**
     * @param string $template
     * @param array $attributes
     * @return false|string
     * @throws Exception
     */
    function render($template, array $attributes = [])
    {
        try {
            if(isset($attributes['template_content'])) throw new Exception('Attribute `template` can not be set');
            $base_path = $this->views_folder . DIRECTORY_SEPARATOR;
            $output = $this->renderTemplate($base_path . $template, $attributes);
            if(! empty($this->layout)) {
                $attributes['template_content'] = $output;
                $output = $this->renderTemplate($base_path . $this->layout, $attributes);
            }
            return $output;
        }
        catch(Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * @param string $template
     * @param array $attributes
     * @return false|string
     */
    protected function renderTemplate($template, array $attributes)
    {
        extract($attributes);
        ob_start();
        include $template;
        return ob_get_clean();
    }
}
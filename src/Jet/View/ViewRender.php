<?php


namespace Jet\View;


use Exception;
use Jet\View\Html\HtmlParser;
use Jet\View\Html\HtmlTag;

class ViewRender
{
    private $views_folder;
    private $cache_folder;

    function __construct($views_folder, $cache_folder)
    {
        $this->views_folder = $views_folder;
        $this->cache_folder = $cache_folder;
    }

    private function getFileCode($path)
    {
        return file_get_contents($path);
    }

    private function renderTemplate($path, $attributes)
    {
        extract($attributes);
        ob_start();
        include $path;
        return ob_get_clean();
    }

    /**
     * @param HtmlParser $template
     * @param array $attributes
     * @throws Exception
     */
    private function addInlcudes(& $template, $attributes)
    {
        /** @var HtmlTag[] $tags */
        $tags = $template->findByTagNames('jet-include');
        foreach ($tags as $tag) {
            $path = $this->views_folder . DIRECTORY_SEPARATOR . $tag->attributes['path'];
            $include_output = $this->renderTemplate($path, $attributes); // new Html\HtmlParser(...)
            $template->replace($tag->toHtml(), $include_output);
        }
    }

    /**
     * @param Html\HtmlParser $layout
     * @param Html\HtmlParser $template
     * @throws Exception
     */
    private function melt(&$layout, $template)
    {
        $tags = $layout->findByTagNames('jet-container');
        /** @var Html\HtmlTag $container */
        foreach ($tags as $container)
        {
            try {
                $tag_content = $template->find(['tagname' => 'jet-container', 'attributes' => ['name' => $container->attributes['name']], 'first' => true]);
                $layout->replace($container->toHtml(), $tag_content->content);
            } catch (Exception $e) {}
        }
    }

    /**
     * @param $template
     * @param $attributes
     * @param $cache
     * @return false|string|null
     * @throws Exception
     */
    function render($template, $attributes, $cache)
    {
        $folder = ($cache) ? $this->cache_folder : $this->views_folder;
        $output = $this->renderTemplate($folder . DIRECTORY_SEPARATOR . $template, $attributes);
        if($cache) return $output;

        $parser_template = new Html\HtmlParser($output);
        $this->addInlcudes($parser_template, $attributes);
        /** @var Html\HtmlTag|null $layout */
        try {
            $layout = $parser_template->find(['tagname' => 'jet-extends', 'first' => true]);
        } catch (Exception $e) {}

        if($layout === null) return $parser_template->getHTMLFinal(false);
        $parser_layout = new Html\HtmlParser( $this->renderTemplate($this->views_folder . DIRECTORY_SEPARATOR . $layout->attributes['path'], $attributes) );
        $this->addInlcudes($parser_layout, $attributes);

        $this->melt($parser_layout, $parser_template);

        return $parser_layout->getHTMLFinal(true);
    }

    /**
     * @param string $template
     * @return string|null
     * @throws Exception
     */
    function renderAsCacheFile($template)
    {
        $code = $this->getFileCode($this->views_folder . DIRECTORY_SEPARATOR . $template);
        $parser_template = new Html\HtmlParser($code);
        /** @var Html\HtmlTag|null $layout */
        try {
            $layout = $parser_template->find(['tagname' => 'jet-extends', 'first' => true]);
        } catch (Exception $e) {}
        if($layout === null) return $parser_template->getHTMLFinal(true);
        $parser_layout = new Html\HtmlParser( $this->getFileCode($this->views_folder . DIRECTORY_SEPARATOR . $layout->attributes['path']) );

        $this->melt($parser_layout, $parser_template);

        return $parser_layout->getHTMLFinal(true);
    }
}
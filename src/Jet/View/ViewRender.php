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
        if(is_file($path))
        {
            extract($attributes);
            ob_start();
            include $path;
            return ob_get_clean();
        }

        throw new Exception('File not found: ' . $path);
    }

    /**
     * @param HtmlParser $parser
     * @param array $attributes
     * @throws Exception
     */
    private function addInlcudes(& $parser, $attributes)
    {
        /** @var HtmlTag[] $tags */
        $tags = $parser->findByTagNames('jet-include');
        foreach ($tags as $tag) {
            $attr_path = $tag->getAttr('path');
            if(empty($attr_path)) throw new Exception('La etiqueta jet-include require del atributo `path`');

            $path = $this->views_folder . '/' . $attr_path;
            $include_output = $this->renderTemplate($path, $attributes);
            $tag->setTagName('div', false);
            $tag->innerHTML( $include_output );
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
            $tag_content = $template->findBy(['tagname' => 'jet-container', 'attr' => [ 'name' => $container->getAttr('name') ], 'first' => true]);
            if(! $tag_content) continue;

            $container->setTagName('div', false);
            $container->content( $tag_content->content() );
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
        $output = $this->renderTemplate($folder . '/' . $template, $attributes);
        if($cache) return $output;

        $parser_template = new Html\HtmlParser($output);
        $this->addInlcudes($parser_template, $attributes);
        /** @var Html\HtmlTag|null $layout */
        try {
            $layout = $parser_template->findBy([ 'tagname' => 'jet-extends', 'first' => true ]);
        } catch (Exception $e) {}

        if($layout === null) return $parser_template->toHTML(false);
        $parser_layout = new Html\HtmlParser( $this->renderTemplate($this->views_folder . '/' . $layout->getAttr('path'), $attributes) );
        $this->addInlcudes($parser_layout, $attributes);

        $this->melt($parser_layout, $parser_template);

        return $parser_layout->toHTML(false);
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
            $layout = $parser_template->findBy(['tagname' => 'jet-extends', 'first' => true]);
        } catch (Exception $e) {}
        if($layout === null) return $parser_template->toHTML(true);
        $parser_layout = new Html\HtmlParser( $this->getFileCode($this->views_folder . DIRECTORY_SEPARATOR . $layout->getAttr('path')) );

        $this->melt($parser_layout, $parser_template);

        return $parser_layout->toHTML(true);
    }
}
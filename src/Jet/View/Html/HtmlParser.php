<?php


namespace Jet\View\Html;


use DOMDocument;
use DomXpath;

class HtmlParser {
    /** @var DOMDocument */
    private $doc;

    /** @var DomXpath */
    private $xpath;

    /**
     * HtmlParser constructor.
     * @param string $html
     * @param string $encoding
     */
    function __construct($html, $encoding = 'utf-8')
    {
        $this->doc = new DOMDocument();
        @$this->doc->loadHTML("<?xml encoding=\"$encoding\"?>" . $html); // <?xml encoding=\"$encoding\"
        $this->xpath = new DomXpath($this->doc);
    }

    /**
     * @param string $xpath
     * @return HtmlTag[]|false
     */
    function find($xpath)
    {
        $nodes = $this->xpath->query($xpath);
        if(! $nodes) return false;

        $tags = [];
        foreach ($nodes as $node) {
            $tags[] = new HtmlTag($node);
        }

        return $tags;
    }

    function findBy(array $options)
    {
        $xpath = '//';
        $first = false;
        if(isset($options['path']) && is_array($options['path']) && count($options['path']) > 0) {
            $xpath .= implode('/', $options['path']) . '/';
        }
        $xpath .= (isset($options['tagname'])) ? $options['tagname'] : '*';
        if(isset($options['attr']) && is_array($options['attr']) && count($options['attr']) > 0) {
            $name = array_keys ($options['attr'])[0];
            $xpath .= "[@$name='{$options['attr'][$name]}']";
        }
        if(isset($options['first']) && $options['first']) {
            $xpath .= '[1]';
            $first = true;
        }
        if(isset($options['parent']) && $options['parent']) {
            $xpath .= '/..';
        }

        $elements = $this->find($xpath);
        return (! $first) ? $elements : ((count($elements) > 0) ? $elements[0] : null);
    }

    function findById($value)
    {
        return $this->findBy([ 'attr' => [ 'id' => $value ], 'first' => true ]);
    }

    function findByTagNames($name)
    {
        return $this->findBy([ 'tagname' => $name ]);
    }

    function toHTML($slim = true)
    {
        $html = $this->doc->saveHTML();
        return (! $slim) ? $html : preg_replace('/\s+</', '<', $html);
    }
}
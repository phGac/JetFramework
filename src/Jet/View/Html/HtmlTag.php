<?php


namespace Jet\View\Html;


use DOMElement;
use DOMNode;
use DOMText;
use DOMNodeList;

class HtmlTag {
    /** @var DOMElement|DOMNode */
    private $node;

    /**
     * HtmlTag constructor.
     * @param DOMElement|DOMNode $node
     */
    function __construct($node)
    {
        $this->node = $node;
    }

    /**
     * Clone node with same document (ownerDocument)
     *
     * @param DOMElement|DOMNode|DOMText $node
     * @return DOMElement|DOMText
     */
    private function cloneNode($node)
    {
        if($node instanceof DOMText)
            return $this->node->ownerDocument->createTextNode($node->data);

        $clone = $this->node->ownerDocument->createElement($node->tagName);

        $children = $node->childNodes;
        for ($i = 0; $i < $children->length; $i++) {
            $clone->appendChild( $this->node->ownerDocument->importNode($children->item($i), true) );
        }
        foreach ($node->attributes as $attr) {
            $clone->setAttribute($attr->name, $attr->value);
        }

        return $clone;
    }

    /**
     * @return DOMElement|DOMNode
     */
    function getNode()
    {
        return $this->node;
    }

    /**
     * @param string $name
     * @return string
     */
    function getAttr($name)
    {
        return $this->node->getAttribute($name);
    }

    /**
     * @param string $name
     * @param string|int $value
     */
    function setAttr($name, $value)
    {
        $this->node->setAttribute($name, $value);
    }

    /**
     * Change TagName
     *
     * @param string $tagname
     * @param false $keep_attrs
     */
    function setTagName($tagname, $keep_attrs = false)
    {
        $node =& $this->node;
        $newnode = $node->ownerDocument->createElement($tagname);

        $children = $node->childNodes;
        while ($children->length > 0){
            $newnode->appendChild( $node->ownerDocument->importNode($children->item(0), true) );
        }
        if($keep_attrs) {
            foreach ($node->attributes as $attr) {
                $newnode->setAttribute($attr->name, $attr->value);
            }
        }

        $node->parentNode->replaceChild($newnode, $node);
        $node = $newnode;
    }

    /**
     * Remove all attributes
     */
    function removeAttrs()
    {
        $attributes = $this->node->attributes;
        while ($attributes->length) {
            $this->node->removeAttribute($attributes->item(0)->name);
        }
    }

    /**
     * Remove all content
     */
    function removeChildren()
    {
        while($this->node->hasChildNodes()) {
            $this->node->removeChild($this->node->firstChild);
        }
    }

    /**
     * @return mixed
     */
    function outerHTML()
    {
        return $this->node->ownerDocument->saveHTML($this->node);
    }

    /**
     * Get or set as string
     *
     * @param string|null $set
     * @return string|void
     */
    function innerHTML($set = null)
    {
        if($set === null) {
            $innerHTML = "";
            foreach ($this->node->childNodes as $child) {
                $innerHTML .= $this->node->ownerDocument->saveHTML($child);
            }
            return $innerHTML;
        }
        else if(is_string($set)) {
            $parser = new HtmlParser($set);
            $body = $parser->find('//body[1]')[0];
            $this->content($body->content());
        }
    }

    /**
     * @param DOMNodeList|DOMNode|DOMText $set
     */
    function append($set)
    {
        if($set instanceof DOMNodeList) {
            for ($i = 0; $i < $set->length; $i++) {
                $node = $this->cloneNode( $set->item($i) );
                $this->node->appendChild($node);
            }
            return;
        }

        if($set instanceof HtmlTag) $set = $set->getNode();

        $node = $this->cloneNode($set);
        $this->node->appendChild($node);
    }

    /**
     * Get or set content as Nodes
     *
     * @param DOMNodeList|DOMNode|DOMText|null $set
     * @return DOMNodeList|void
     */
    function content($set = null)
    {
        if($set === null) return $this->node->childNodes;

        $this->removeChildren();
        $this->append($set);
    }
}
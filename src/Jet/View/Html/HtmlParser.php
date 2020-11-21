<?php


namespace Jet\View\Html;


use Exception;

class HtmlParser
{
    private $original;
    private $final;

    /**
     * HtmlParser constructor.
     * @param string $html
     */
    function __construct($html)
    {
        $this->original = $html;
        $this->final = $html;
    }

    function getHTMLOriginal()
    {
        return $this->original;
    }

    /**
     * @param bool $slim
     * @return string|null
     */
    function getHTMLFinal($slim = true)
    {
        return (! $slim) ? $this->final : preg_replace(HtmlRegex::SLIM, HtmlRegex::SLIM_REPLACE, $this->final);
    }

    function save()
    {
        $this->original = $this->final;
    }

    /**
     * @param array $options
     * @return HtmlTag[]|HtmlTag|null
     * @throws Exception
     */
    function find($options)
    {
        $ATTRIBUTES = HtmlRegex::ATTRIBUTES;
        $BLANKS = HtmlRegex::BLANKS;
        $CONTENT = HtmlRegex::CONTENT;

        $attrs = '';
        if(isset($options['attributes'])) {
            $attrs_array = [];
            foreach ($options['attributes'] as $key => $value) {
                $attrs_array[] = "$key=\\\"$value\\\"";
            }
            $attrs = implode(' ', $attrs_array);
        }

        if(isset($options['tagname'])) {
            $regex = "/(<{$options['tagname']}{$BLANKS}({$ATTRIBUTES}{$BLANKS}){0,}{$attrs}\/>)|((<{$options['tagname']}{$BLANKS}({$ATTRIBUTES}{$BLANKS}){0,}{$attrs}>)({$CONTENT})(<\/{$options['tagname']}>))/u";
        }
        else if(! empty($attrs)) {
            $regex = "/<[A-Za-z-]+ {0,}( [A-Za-z]+=\"[A-Za-z0-9-_:#=, \/\\\.]{0,}\"){0,}{$attrs} {0,}\/?>/u";
        }
        else {
            throw new Exception('Invalid Options');
        }

        if(! isset($options['first']) || ! $options['first']) {
            preg_match_all($regex, $this->original, $matches);

            $tags = [];
            if(count($matches[0]) > 0) {
                if(isset($options['tagname'])) {
                    foreach ($matches[0] as $html) {
                        $tags[] = new HtmlTag($html);
                    }
                }
                else {
                    foreach ($matches[0] as $html) {
                        $tagname = HtmlTag::getTagName($html);
                        $attributes = HtmlTag::getPrefixAttributes($html);
                        $tags[] = $this->find([ 'tagname' => $tagname, 'attributes' => $attributes, 'first' => true ]);
                    }
                }
            }

            return $tags;
        }
        else if(preg_match($regex, $this->original, $matches)) {
            if(isset($options['tagname'])) {
                return new HtmlTag($matches[0]);
            }
            else {
                $tagname = HtmlTag::getTagName($matches[0]);
                $attributes = HtmlTag::getPrefixAttributes($matches[0]);
                return $this->find([ 'tagname' => $tagname, 'attributes' => $attributes, 'first' => true ]);
            }
        }

        return  null;
    }

    /**
     * @param string $name
     * @return HtmlTag[]
     * @throws Exception
     */
    function findByTagNames($name)
    {
        return $this->find([ 'tagname' => $name ]);
    }

    /**
     * @param string $id
     * @return HtmlTag|null
     * @throws Exception
     */
    function findById($id)
    {
        return $this->find([ 'attributes' => [ 'id' => $id ], 'first' => true ]);
    }

    /**
     * @param string $toFind
     * @param string|int|null $toReplace
     */
    function replace($toFind, $toReplace)
    {
        $this->final = str_replace($toFind, $toReplace, $this->final);
    }
}
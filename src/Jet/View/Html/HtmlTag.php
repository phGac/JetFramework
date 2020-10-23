<?php


namespace Jet\View\Html;

use Jet\View\Html\HtmlRegex;

class HtmlTag
{
    public $tagname;
    public $content;
    public $attributes;

    private $full;
    private $prefix;
    private $suffix;

    /**
     * @param string|null $full
     */
    function __construct($full = null)
    {
        $this->full = $full;
        $this->prefix = null;
        $this->suffix = null;
        $this->tagname = null;
        $this->content = null;
        $this->attributes = [];
        if(is_string($full)) $this->loadHtml($full);
    }

    static function getTagName($html)
    {
        $TAG_NAME = HtmlRegex::TAG_NAME;
        return (preg_match("/^{$TAG_NAME}/", $html, $matches)) ? $matches[1] : null;
    }

    static function getPrefixAttributes($html)
    {
        $ATTRIBUTES = HtmlRegex::ATTRIBUTES;
        if(preg_match_all("/{$ATTRIBUTES}/", $html, $matches)) {
            $attrs = [];
            foreach ($matches[0] as $key => $value) {
                $attrs[ $matches[1][ $key ] ] = $matches[2][ $key ];
            }
            return $attrs;
        }

        return null;
    }

    /**
     * @param string $html
     * @param string $tagname
     * @return array|null
     */
    static function getHtmlElementInfo($html, $tagname)
    {
        $data = [
            'prefix' => null,
            'suffix' => null,
            'content' => null,
            'attributes' => []
        ];

        $ATTRIBUTES = HtmlRegex::ATTRIBUTES;
        $CONTENT = HtmlRegex::CONTENT;
        $BLANKS = HtmlRegex::BLANKS;

        $regex = "/(<{$tagname}{$BLANKS}({$ATTRIBUTES}){$BLANKS}\/>)|((<{$tagname}{$BLANKS}({$ATTRIBUTES}){$BLANKS}>)({$CONTENT})(<\/{$tagname}>))/";
        preg_match($regex, $html, $info);
        if(count($info) == 0) return null;

        $data['prefix'] = $info[8];
        $data['suffix'] = (empty($info[15])) ? null : $info[15];
        $data['content'] = (empty($info[14])) ? null : $info[14];

        $attributes = [];
        preg_match_all("/{$ATTRIBUTES}/", $data['prefix'], $attrs);
        if($attrs && is_array($attrs[0])) {
            foreach ($attrs[0] as $key => $value) {
                $attributes[ $attrs[1][$key] ] = $attrs[2][$key];
            }
            $data['attributes'] = $attributes;
        }

        return $data;
    }

    /**
     * @param string $html
     */
    function loadHtml($html)
    {
        $this->full = $html;
        $this->tagname = self::getTagName($html);
        $parts = self::getHtmlElementInfo($html, $this->tagname);
        $this->prefix = $parts['prefix'];
        $this->suffix = $parts['suffix'];
        $this->content = $parts['content'];
        $this->attributes = $parts['attributes'];
    }

    private function update()
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            $attributes[] = "$key=\"$value\"";
        }
        $attributes_str = implode(' ', $attributes);
        if($this->content != null || $this->suffix != null) {
            $this->prefix = "<{$this->tagname} $attributes_str>";
            $this->suffix = "</{$this->tagname}>";
            $this->full = "{$this->prefix}{$this->content}{$this->suffix}";
        }
        else {
            $this->prefix = "<{$this->tagname} $attributes_str/>";
            $this->suffix = null;
            $this->full = $this->prefix;
        }
    }

    /**
     * @return string|null
     */
    function toHtml()
    {
        $this->update();
        return $this->full;
    }
}
<?php


namespace Jet\View\Html;


class HtmlRegex
{
    const ATTRIBUTES = "([A-Za-z]+)=\"([A-Za-z0-9-_:#=,; \/\\\\.]{0,})\"";
    const BLANKS = '( ){0,}';
    const TAG_NAME = '<([A-Za-z-]+)';
    const CONTENT = '[\sA-Za-z0-9-_ <>\/=¿?!¡$\'"’,:.()\[\]]{0,}';

    const SLIM = '/\s+</';
    const SLIM_REPLACE = '<';
}
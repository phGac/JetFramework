<?php

namespace Jet\Exception\Format;

use Jet\Exception\BaseError;
use Jet\Exception\BaseErrorLevel;

class FormatError extends BaseError
{
    private $unformated;

    /**
     * FormatError constructor.
     * @param string $unformat
     * @param string $message
     */
    public function __construct($unformat, $message)
    {
        parent::__construct('Format', BaseErrorLevel::WARNING, $message);
        $this->unformat = $unformat;
    }

    public function getUnformat()
    {
        return $this->unformat;
    }
}
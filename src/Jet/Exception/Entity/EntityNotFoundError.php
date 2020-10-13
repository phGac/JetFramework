<?php

namespace Jet\Exception\Entity;

use Jet\Exception\BaseError;
use Jet\Exception\BaseErrorLevel;

class EntityNotFoundError extends BaseError
{
    /**
     * EntityNotFoundError constructor.
     * @param string $message
     * @param string $level
     */
    public function __construct($message, $level = BaseErrorLevel::WARNING)
    {
        parent::__construct('NotFound', $level, $message);
    }
}
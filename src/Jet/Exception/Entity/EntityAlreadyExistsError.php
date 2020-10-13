<?php

namespace Jet\Exception\Entity;

use Jet\Exception\BaseError;
use Jet\Exception\BaseErrorLevel;

class EntityAlreadyExistsError extends BaseError
{
    /**
     * EntityAlreadyExistsError constructor.
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct('AlreadyExists', BaseErrorLevel::WARNING, $message);
    }
}
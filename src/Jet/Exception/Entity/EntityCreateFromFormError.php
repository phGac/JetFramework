<?php

namespace Jet\Exception\Entity;

use Jet\Exception\BaseError;
use Jet\Exception\BaseErrorLevel;

class EntityCreateFromFormError extends BaseError
{
    private $errors;

    /**
     * EntityCreateFromFormError constructor.
     * @param array $errors
     * @param string $message
     */
    public function __construct($errors, $message = '')
    {
        parent::__construct('CreateFromForm', BaseErrorLevel::WARNING, $message);
        $this->errors = $errors;
    }

    function getErrors()
    {
        return $this->errors;
    }

    function getErrorsAsMessageArray()
    {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
}
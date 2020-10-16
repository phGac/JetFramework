<?php

namespace Jet\Exception;

use Exception;

abstract class BaseError extends Exception {
    private $type;
    private $level;

    /**
     * BaseError constructor.
     * @param string $type
     * @param string $level
     * @param string $message
     */
    public function __construct($type, $level, $message)
    {
        parent::__construct($message);
        $this->type = $type;
        $this->level = $level;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function toArray()
    {
        return [
            'type' => $this->type,
            'level' => $this->level,
            'message' => $this->message
        ];
    }
}
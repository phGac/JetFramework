<?php

namespace Jet\Db;

interface Validator
{
    /**
     * @param array $data
     * @return array
     */
    function validate(array $data);
}
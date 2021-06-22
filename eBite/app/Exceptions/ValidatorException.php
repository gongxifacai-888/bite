<?php

namespace App\Exceptions;

use Exception;

class ValidatorException extends Exception
{
    protected $field;

    public function setField($field_name)
    {
        $this->field = $field_name;
        return $this;
    }

    public function getField()
    {
        return $this->field;
    }
}

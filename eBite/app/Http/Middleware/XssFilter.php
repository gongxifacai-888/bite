<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class XssFilter extends TransformsRequest
{

    protected $except = [
        'password',
    ];

    protected function transform($key, $value)
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) ? strip_tags($value) : $value;
    }
}

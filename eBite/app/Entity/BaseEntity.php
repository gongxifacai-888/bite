<?php


namespace App\Entity;

use ArrayAccess;
use Illuminate\Support\Str;

class BaseEntity implements ArrayAccess
{
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset} ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    public function __set($name, $value)
    {
        $method = "set_{$name}_attribute";
        $method = Str::camel($method);
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], [$value]);
        } else {
            $this->$name = $value;
        }
    }

    /**注意,设置过的属性不会被调用这个方法
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        $method = "get_{$name}_attribute";
        $method = Str::camel($method);
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method]);
        }
        return $this->$name ?? null;
    }

}

<?php

namespace App\Dto;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class BaseDto implements ArrayAccess
{
    const SUCCESS_CODE = 0;

    public function Assign($items) {
        if (!$items) return;
        if (is_object($items)) $items = collect($items)->toArray();
        foreach ($items as $property => $value) {
            if (null !== $value && property_exists($this, $property))
                $this->$property = $value;
        }
    }

    /**
     * @see ArrayAccess::offsetExists
     * @param int $offset
     */
    public function offsetExists($offset)
    {
        return isset($offset);
    }

    /**
     * @see ArrayAccess::offsetGet
     * @param int $offset
     */
    public function offsetGet($offset)
    {
        return $offset;
    }

    /**
     * @see ArrayAccess::offsetSet
     * @param int $offset
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception(__CLASS__ . ' is read only');
    }

    /**
     * @see ArrayAccess::offsetUnset
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        throw new \Exception(__CLASS__ . ' is read only');
    }

}
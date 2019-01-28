<?php

namespace App\Dto;

class BaseDto
{
    public function Assign($items) {
        if (!$items) return;
        if (is_object($items)) $items = collect($items)->toArray();
        foreach ($items as $property => $value) {
            if (null !== $value && property_exists($this, $property))
                $this->$property = $value;
        }
    }
}
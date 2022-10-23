<?php

namespace Joalvm\Utils\Schematic\Types;

class UnknownType extends Type
{
    public $searchable = false;

    public function parse($value)
    {
        return $value;
    }
}

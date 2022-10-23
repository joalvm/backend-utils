<?php

namespace Joalvm\Utils\Schematic\Types\Natives;

use Joalvm\Utils\Schematic\Types\Type;

class IntType extends Type
{
    public function parse($value): ?int
    {
        return to_int($value);
    }
}

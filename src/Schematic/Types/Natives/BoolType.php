<?php

namespace Joalvm\Utils\Schematic\Types\Natives;

use Joalvm\Utils\Schematic\Types\Type;

class BoolType extends Type
{
    public function parse($value): ?bool
    {
        return to_bool($value);
    }
}

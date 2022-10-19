<?php

namespace Joalvm\Utils\Schematic\Types\Natives;

use Joalvm\Utils\Schematic\Types\Type;

class JsonType extends Type
{
    public function parse($value): ?array
    {
        return json_decode($value, true);
    }
}

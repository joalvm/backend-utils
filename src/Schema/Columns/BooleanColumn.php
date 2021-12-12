<?php

namespace Joalvm\Utils\Schema\Columns;

use Joalvm\Utils\Cast;

class BooleanColumn extends AbstractColumn
{
    public function parse($value): ?bool
    {
        if (is_null($value) or is_bool($value)) {
            return $value;
        }

        if (is_string($value) || is_numeric($value)) {
            return Cast::toBool($value);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'The value %s is not a boolean',
                $value
            )
        );
    }
}

<?php

namespace Joalvm\Utils\Schema\Columns;

class IntegerColumn extends AbstractColumn
{
    /**
     * @param null|float|int|string $value Numeric value
     */
    public function parse($value): ?int
    {
        if (is_null($value) or is_integer($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return intval($value);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'El valor "%s" no es un numero entero o numerico.',
                $value
            )
        );
    }
}

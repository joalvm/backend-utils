<?php

namespace Joalvm\Utils\Schema\Columns;

class JsonColumn extends AbstractColumn
{
    public function raw(string $raw, array $bindings = [])
    {
        $raw = "'{$raw}'::jsonb";

        return parent::raw($raw, $bindings);
    }

    public function parse($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            return json_decode($value, true);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'The value %s is not a string json',
                $value
            )
        );
    }
}

<?php

namespace Joalvm\Utils\Schema;

use Joalvm\Utils\Schema\Columns\BooleanColumn;
use Joalvm\Utils\Schema\Columns\FloatColumn;
use Joalvm\Utils\Schema\Columns\IntegerColumn;
use Joalvm\Utils\Schema\Columns\JsonColumn;
use Joalvm\Utils\Schema\Columns\StringColumn;

class Type
{
    public static function int($column = null, array $bindings = []): IntegerColumn
    {
        return new IntegerColumn($column, $bindings);
    }

    public static function str($column = null, array $bindings = []): StringColumn
    {
        return new StringColumn($column, $bindings);
    }

    public static function float($column = null, array $bindings = []): FloatColumn
    {
        return new FloatColumn($column, $bindings);
    }

    public static function bool($column = null, array $bindings = []): BooleanColumn
    {
        return new BooleanColumn($column, $bindings);
    }

    public static function json($column = null, array $bindings = []): JsonColumn
    {
        return new JsonColumn($column, $bindings);
    }
}

<?php

namespace Joalvm\Utils\Schematic;

use Joalvm\Utils\Schematic\Types\Natives\BoolType;
use Joalvm\Utils\Schematic\Types\Natives\FloatType;
use Joalvm\Utils\Schematic\Types\Natives\IntType;
use Joalvm\Utils\Schematic\Types\Natives\JsonType;
use Joalvm\Utils\Schematic\Types\Natives\StrType;

class Item
{
    /**
     * Instancia un tipo usando un alias.
     *
     * @param null|\Closure|object|string $column
     */
    final public static function int($column = null, array $bindings = []): IntType
    {
        return new IntType($column, $bindings);
    }

    /**
     * Instancia un tipo usando el nombre de columna.
     *
     * @param null|\Closure|object|string $column
     */
    final public static function str($column = null, array $bindings = []): StrType
    {
        return new StrType($column, $bindings);
    }

    /**
     * Instancia un tipo usando el nombre de columna.
     *
     * @param null|\Closure|object|string $column
     */
    final public static function float($column = null, array $bindings = []): FloatType
    {
        return new FloatType($column, $bindings);
    }

    /**
     * Instancia un tipo usando el nombre de columna.
     *
     * @param null|\Closure|object|string $column
     */
    final public static function bool($column = null, array $bindings = []): BoolType
    {
        return new BoolType($column, $bindings);
    }

    /**
     * Instancia un tipo usando el nombre de columna.
     *
     * @param null|\Closure|object|string $column
     */
    final public static function json($column = null, array $bindings = []): JsonType
    {
        return new JsonType($column, $bindings);
    }
}

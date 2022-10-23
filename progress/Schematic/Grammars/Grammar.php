<?php

namespace Joalvm\Utils\Schematic\Grammars;

use Joalvm\Utils\Schematic\Types\Type;

abstract class Grammar
{
    public const WRAP_SYMBOL_BEGIN = '`';
    public const WRAP_SYMBOL_END = '`';

    /**
     * Envuelve la declaración de una entidad(columna, esquema, alias de table, etc.)
     * respetando la seperación por punto.
     *
     * @param string|string[] $value
     */
    public function wrapEntity($value): string
    {
        return implode('.', array_map([$this, 'wrap'], to_list($value, false, '.')));
    }

    public function wrap(string $value): string
    {
        return sprintf(
            '%s%s%s',
            static::WRAP_SYMBOL_BEGIN,
            $value,
            static::WRAP_SYMBOL_END
        );
    }

    /**
     * Compila una sentencia para el listado de columnas en query.
     *
     * @return string|string[]
     */
    public function compileSelect(Type $type)
    {
        $as = $this->wrap($type->as);

        if (Type::TYPE_SUBQUERY === $type->type) {
            return [$type->column, $as];
        }

        if (Type::TYPE_EXPRESSION === $type->type) {
            return [
                sprintf('(%s) as %s', $type->column, $as),
                $type->bindings,
            ];
        }

        $column = !$type->tableAs ? '' : "{$type->tableAs}." . $type->column;

        return sprintf('%s as %s', $this->wrapEntity($column), $as);
    }
}

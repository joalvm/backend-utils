<?php

namespace Joalvm\Utils\Schematic\Types;

use Joalvm\Utils\Schematic\Contracts\TypeContract;

abstract class Type implements TypeContract
{
    public const TYPE_COLUMN = 'column';
    public const TYPE_SUBQUERY = 'subquery';
    public const TYPE_EXPRESSION = 'expression';

    public $type = self::TYPE_COLUMN;

    /**
     * Nombre de la columna, la expresión o la subconsulta.
     *
     * @var null|\Closure|string
     */
    public $column;

    /**
     * @var null|string
     */
    public $tableAs;

    /**
     * Alias de la columna.
     *
     * @var null|string
     */
    public $as;

    /**
     * Valores usados por el tipo expression.
     *
     * @var float[]|int[]|null[]|string[]
     */
    public $bindings = [];

    /**
     * le indica al esquema si la columna puede ser usada para ordenamiento.
     *
     * @var bool
     */
    public $sortable = true;

    /**
     * le indica al esquema si la columna puede ser usada para busqueda.
     *
     * @var bool
     */
    public $searchable = true;

    /**
     * le indica al esquema si la columna puede ser filtrada o no.
     *
     * @var bool
     */
    public $filterable = true;

    /**
     * @param null|\Closure|string $column
     */
    public function __construct($column = null, array $bindings = [])
    {
        if ($column instanceof \Closure or is_object($column)) {
            $this->setSub($column);

            return;
        }

        if (!$this->isColumnable(strval($column))) {
            $this->setRaw($column, $bindings);

            return;
        }

        $this->setColumn($column);
    }

    /**
     * Establece el nombre de columna, la expresión o la subconsulta.
     *
     * @param null|\Closure|string $column
     *
     * @return static
     */
    public function setColumn($column)
    {
        $this->type = self::TYPE_COLUMN;
        $this->column = $column;

        if (str_contains($this->column, '.')) {
            $parts = explode('.', $this->column);

            $this->column = to_str(array_pop($parts));
            $this->tableAs = to_str(array_pop($parts));
        }

        return $this;
    }

    /**
     * Establece la columna como una subconsulta.
     *
     * @param \Closure|object $subQuery
     *
     * @return static
     */
    public function setSub($subQuery)
    {
        $this->type = self::TYPE_SUBQUERY;
        $this->column = $subQuery;

        return $this;
    }

    /**
     * Establece la columna como una expresión.
     *
     * @param object|string $expression
     *
     * @return static
     */
    public function setRaw($expression, array $bindings = [])
    {
        $this->type = self::TYPE_EXPRESSION;
        $this->column = $expression;
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * Establece el alias de la columna.
     *
     * @return static
     */
    public function setAs(?string $as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * Establece el alias de la tabla de la columna.
     *
     * @return static
     */
    public function setTableAs(?string $tableAs)
    {
        if (self::TYPE_COLUMN === $this->type) {
            $this->tableAs = $tableAs;
        }

        return $this;
    }

    public function hasTableAs(): bool
    {
        return !is_null($this->tableAs);
    }

    public function hasColumn(): bool
    {
        return !is_null($this->column);
    }

    /**
     * Establece los valores usados por el tipo expression.
     *
     * @return static
     */
    public function setBindings(array $bindings)
    {
        $this->bindings = $bindings;

        return $this;
    }

    /**
     * Establece si la columna puede ser usada para ordenamiento.
     *
     * @return static
     */
    public function setSortable(bool $sortable = true)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Establece si la columna puede ser usada para busqueda.
     *
     * @return static
     */
    public function setSerchable(bool $searchable = true)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Establece si la columna puede ser filtrada o no.
     *
     * @return static
     */
    public function setFilterable(bool $filterable = true)
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function isColumnable(string $column): bool
    {
        return preg_match('/^[a-z](([a-z0-9_]+)?\.{1})?[a-z0-9_]+$/i', $column);
    }
}

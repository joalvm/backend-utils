<?php

namespace Joalvm\Utils\Schema\Columns;

use Closure;
use Exception;

abstract class AbstractColumn implements ColumnInterface
{
    public const TYPE_COLUMN_NAME = 'columnName';
    public const TYPE_SUBQUERY = 'subquery';
    public const TYPE_EXPRESSION = 'expression';

    /**
     * Tipo de columna, columnName, subquery, expression.
     */
    public $type = self::TYPE_COLUMN_NAME;

    /**
     * Guarda el nombre de la columna, expresion o subquery.
     *
     * @var null|Closure|string
     */
    public $column;

    /**
     * Alias que usará la columna.
     *
     * @var string
     */
    public $columnAs;

    /**
     * Alias de la tabla definida en el clausula `from` o `join`.
     *
     * @var string
     */
    public $tableAs;

    /**
     * Valores usados en las columnas de tipo Expresion.
     *
     * @var mixed[]
     */
    public $bindings = [];

    /**
     * Indica si la columna puede ser usada para ordenamiento.
     *
     * @var bool
     */
    private $sortable = true;

    /**
     * Indica si la columna puede ser usada para busqueda.
     *
     * @var bool
     */
    private $searchable = true;

    /**
     * Indica si la columna puede ser filtrada o no.
     *
     * @var bool
     */
    private $filterable = true;

    /**
     * Indica si la columna a sido añadida al select.
     */
    private $addedToSelect = false;

    /**
     * Driver de la base de datos.
     *
     * @var ('pgsql'|'mysql'|'sqlite'|'sqlsrv')
     */
    private $driverName = 'mysql';

    /**
     * @param null|Closure|string $column   nombre de la columna, expresion o subquery
     * @param array               $bindings [optional] En caso de que la columna sea un raw,
     *                                      se pueden pasar los bindings
     */
    public function __construct($column = null, array $bindings = [])
    {
        if ($column instanceof Closure) {
            $this->sub($column);
        } elseif (is_string($column)) {
            if (!$this->isColumnable($column)) {
                $this->raw($column, $bindings);
            } else {
                $this->setColumnName($column);
            }
        } elseif (!is_null($column)) {
            throw new Exception('Invalid column type');
        }
    }

    /**
     * Define el nombre de la columna.
     *
     * @return static
     */
    public function setColumnName(string $columnName)
    {
        if (self::TYPE_COLUMN_NAME !== $this->type) {
            return $this;
        }

        $splitted = explode('.', $columnName);

        if (2 === count($splitted) and is_null($this->tableAs)) {
            $this->setTableAs($splitted[0]);
        }

        $this->column = $splitted[1] ?? $splitted[0];
        $this->type = self::TYPE_COLUMN_NAME;

        if (is_null($this->columnAs)) {
            $this->setColumnAs($this->column);
        }

        return $this;
    }

    public function setColumnAs(string $columnAs)
    {
        $this->columnAs = $columnAs;

        return $this;
    }

    public function sort(bool $value = true)
    {
        $this->sortable = $value;

        return $this;
    }

    public function search(bool $value = true)
    {
        $this->searchable = $value;

        return $this;
    }

    public function filter(bool $value = true)
    {
        $this->filterable = $value;

        return $this;
    }

    public function getColumn(bool $withAs = false)
    {
        if (self::TYPE_COLUMN_NAME === $this->type and !$withAs) {
            return sprintf(
                '%s.%s',
                $this->wrapColumn($this->tableAs),
                $this->wrapColumn($this->column)
            );
        }

        if (self::TYPE_COLUMN_NAME === $this->type and $withAs) {
            return sprintf(
                '%s.%s as %s',
                $this->wrapColumn($this->tableAs),
                $this->wrapColumn($this->column),
                $this->getColumnAs()
            );
        }

        if (self::TYPE_EXPRESSION === $this->type and $withAs) {
            return sprintf(
                '(%s) as %s',
                $this->column,
                $this->getColumnAs()
            );
        }

        return $this->column;
    }

    public function getColumnAs(): string
    {
        return $this->wrapColumn($this->columnAs);
    }

    public function setTableAs(?string $tableAs = null)
    {
        $this->tableAs = $tableAs;

        return $this;
    }

    public function sub(Closure $sub)
    {
        $this->type = self::TYPE_SUBQUERY;

        $this->column = $sub;
        $this->tableAs = null;

        return $this;
    }

    public function raw(string $raw, array $bindings = [])
    {
        $this->type = self::TYPE_EXPRESSION;

        $this->column = $raw;
        $this->bindings = $bindings;
        $this->tableAs = null;

        return $this;
    }

    public function setDriverName($driverName)
    {
        $this->driverName = $driverName;

        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * @return static
     */
    public function setAddedToSelect(bool $value)
    {
        $this->addedToSelect = $value;

        return $this;
    }

    public function getAddedToSelect(): bool
    {
        return $this->addedToSelect;
    }

    private function wrapColumn($value)
    {
        switch ($this->driverName) {
            case 'pgsql':
            case 'sqlite':
                return sprintf('"%s"', $value);

            case 'mysql':
                return sprintf('`%s`', $value);

            case 'sqlsrv':
                return sprintf('[%s]', str_replace(']', ']]', $value));

            default:
                return $value;
        }
    }

    /**
     * Verifica que el nombre de una columna tenga la estructura
     * correcta de nombramiento.
     *
     * @param mixed $column
     */
    private function isColumnable($column): bool
    {
        if (!is_string($column)) {
            return false;
        }

        return preg_match(
            '/^(([a-zA-Z])(\\w+)?\\.)?([a-zA-Z]\\w+|_)$/i',
            str_replace(['"', '[', ']', '`'], ['', '', '', ''], $column)
        );
    }
}

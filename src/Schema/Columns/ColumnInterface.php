<?php

namespace Joalvm\Utils\Schema\Columns;

use Closure;

interface ColumnInterface
{
    /**
     * Establece el alias con el que la tabla fue nombrada.
     *
     * @return static
     */
    public function setTableAs(string $tableAs);

    /**
     * Estable el nombre real de la columna.
     *
     * @return static
     */
    public function setColumnName(string $columnName);

    /**
     * Establece el alias de la columna.
     *
     * @return static
     */
    public function setColumnAs(string $columnAs);

    /**
     * Establece el nombre del driver de la conexión.
     *
     * @param ("pgsql"|'mysql'|'sqlite'|'sqlsrv') $driverName
     */
    public function setDriverName($driverName);

    /**
     * Devuelve la estructura de la columna `TableAs.columnName`,
     * expresion o subquery.
     *
     * @return Closure|string
     */
    public function getColumn(bool $withAs = false);

    /**
     * Devuelve el alias de la columna.
     */
    public function getColumnAs(): string;

    /**
     * Convierte un valor al tipo de dato especificado.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function parse($value);

    /**
     * Define una subquery como select_expr.
     *
     * @return static
     */
    public function sub(Closure $subquery);

    /**
     * Define una expresión como select_expr.
     */
    public function raw(string $raw);

    public function isSortable(): bool;

    public function isFilterable(): bool;

    public function isSearchable(): bool;

    public function setAddedToSelect(bool $addedToSelect);

    public function getAddedToSelect(): bool;
}

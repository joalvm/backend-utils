<?php

namespace Joalvm\Utils\Schema;

use Illuminate\Support\Arr;
use Joalvm\Utils\Schema\Columns\ColumnInterface;

class Schema
{
    /**
     * @var ('pgsql'|'mysql'|'sqlite'|'sqlsrv')
     */
    protected $driverName;

    /**
     * @var null|string[]
     */
    protected $parents = [];

    /**
     * @var ColumnInterface[]
     */
    protected $columns;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var null|string
     */
    protected $tableAs;

    /**
     * @var bool
     */
    private $booted = false;

    /**
     * Convierte a null, un array asociativo, cuyos todos sus valores son null.
     *
     * @var bool
     */
    private $flattenNulls = true;

    /**
     * @param array|string $name    nombre del esquema o columnas
     * @param null|array   $columns Columnas del esquema
     */
    public function __construct($name, ?array $columns = null)
    {
        if (is_array($name)) {
            $this->columns = $name;
        } elseif (is_string($name)) {
            $this->name = $name;
            $this->columns = $columns ?? [];
        }
    }

    public static function create(array $columns): self
    {
        return new static($columns);
    }

    public static function join(string $tableAs, array $columns): self
    {
        return (new self($columns))->setTableAs($tableAs);
    }

    /**
     * Obtiene la lista de columnas registradas.
     *
     * @return ColumnInterface[]
     */
    public function getColumns()
    {
        if (!$this->booted) {
            $this->boot();
        }

        return $this->columns;
    }

    public function getColumn(string $name): ?ColumnInterface
    {
        if (!$this->booted) {
            $this->boot();
        }

        return Arr::get($this->columns, $name);
    }

    /**
     * Devuelve la lista filtrada de columnas.
     *
     * @param string[] $names
     *
     * @return ColumnInterface[]
     */
    public function getColumnsIn(array $names)
    {
        return Arr::only($this->getColumns(), $names);
    }

    /**
     * Construye la estructura del esquema y castea los datos.
     *
     * @param array|object $data
     * @param mixed        $rows
     *
     * @return array|object
     */
    public function schematize($rows)
    {
        if (is_array($rows) and array_is_list($rows)) {
            return array_map(function ($row) {
                return $this->schematizeRow($row);
            }, $rows);
        }

        return $this->schematizeRow($rows);
    }

    public function schematizeRow($row): array
    {
        $result = [];

        foreach ($row as $alias => $column) {
            if (!key_exists($alias, $this->columns)) {
                continue;
            }

            Arr::set($result, $alias, $this->columns[$alias]->parse($column));
        }

        return $this->flattenNulls
            ? $this->flattenAssocNulls($result)
            : $result;
    }

    public function boot(): self
    {
        $columns = [];

        if ($this->booted) {
            return $this;
        }

        foreach ($this->columns as $name => $column) {
            $as = $this->keyName($name);

            if ($column instanceof ColumnInterface) {
                $column->setDriverName($this->driverName);

                if (is_null($column->tableAs)) {
                    $column->setTableAs($this->tableAs);
                }

                if (is_null($column->column)) {
                    $column->setColumnName($name);
                }

                $column->setColumnAs($as);
            } elseif (is_array($column) and !array_is_list($column)) {
                $column = self::join($this->tableAs, $column);
            }

            if ($column instanceof self) {
                $column->setDriverName($this->driverName);

                $column->setName($name);
                $column->registerParent($this);

                $columns = array_merge(
                    $columns,
                    $column->boot()->getColumns(false)
                );

                continue;
            }

            $columns[$as] = $column;
        }

        $this->columns = $columns;
        $this->booted = true;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setTableAs(?string $tableAs): self
    {
        if (is_null($this->tableAs)) {
            $this->tableAs = $tableAs;

            foreach ($this->columns as $column) {
                if (method_exists($column, 'setTableAs')) {
                    $column->setTableAs($tableAs);
                }
            }
        }

        return $this;
    }

    public function registerParent(Schema $parent): self
    {
        $this->parents = array_merge($parent->parents, [$parent->name]);

        if (is_null($this->tableAs)) {
            $this->tableAs = $parent->tableAs;
        }

        foreach ($this->columns as $column) {
            if ($column instanceof self) {
                $column->registerParent($parent);
            }
        }

        return $this;
    }

    /**
     * Establece el nombre del driver de la conexión.
     *
     * @param ("pgsql"|'mysql'|'sqlite'|'sqlsrv') $driverName
     */
    public function setDriverName($driverName): self
    {
        $this->driverName = $driverName;

        return $this;
    }

    public function setFlattenNullsOption(bool $value): self
    {
        $this->flattenNulls = $value;

        return $this;
    }

    private function keyName(?string $alias): ?string
    {
        return implode('.', array_filter(
            array_merge($this->parents, [$this->name, $alias])
        ));
    }

    /**
     * Analiza todos los objetos asociativos en busca de nulls
     * en caso de hallar todos los valores de las keys en null,
     * convierte todo el array a null.
     *
     * @param array|\stdClass $data
     */
    private function flattenAssocNulls($data)
    {
        return array_map(function ($val) {
            if (is_array_assoc($val) || is_object($val)) {
                return (
                    count(
                        array_filter(
                            array_values($val = $this->flattenAssocNulls($val)),
                            function ($item) {
                                return !is_null($item);
                            }
                        )
                    ) > 0
                ) ? $val : null;
            }

            return $val;
        }, $data);
    }
}

<?php

namespace Joalvm\Utils;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Traits\Paginatable;
use Joalvm\Utils\Traits\Schematizable;

class Builder extends BaseBuilder
{
    use Schematizable, Paginatable {
        Schematizable::boot insteadof Paginatable;
        Paginatable::boot insteadof Schematizable;
        Schematizable::boot as SchematizableBoot;
        Paginatable::boot as PaginatableBoot;
    }

    /**
     * Callback que contiene los filtros.
     *
     * @var null|Closure
     */
    private $filters;

    /**
     * Indica que se ejecute el callback de filtros.
     *
     * @var bool
     */
    private $enableFilters = true;

    /**
     * Callback para el casteo de los datos a su tipo de dato original.
     *
     * @var null|Closure
     */
    private $casts;

    /**
     * Initialize class.
     *
     * @param null|ConnectionInterface|string $connection
     */
    public function __construct($connection = null)
    {
        parent::__construct($this->normalizeConnection($connection));

        $this->SchematizableBoot();
        $this->PaginatableBoot();
    }

    public static function connection(string $connectionName): self
    {
        return new static($connectionName);
    }

    public static function table(string $tableName, string $alias): self
    {
        return (new static())->from($tableName, $alias);
    }

    /**
     * Obtiene elementos en base al proceso de esquematización.
     */
    public function collection(): Collection
    {
        if ($this->enableFilters and !is_null($this->filters)) {
            call_user_func($this->filters, $this);
        }

        return new Collection(
            $this->paginate
                ? $this->paginate($this->perPage, ['*'], 'page', $this->page)
                : $this->get(),
            array_keys($this->schema),
            $this->casts
        );
    }

    public function item(): Item
    {
        $this->limit(1);

        return new Item($this->collection()->first() ?? []);
    }

    public function setFilters(Closure $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function setCasts(Closure $casts): self
    {
        $this->casts = $casts;

        return $this;
    }

    public function disableFilters(): self
    {
        $this->enableFilters = false;

        return $this;
    }

    /**
     * Normaliza la conección pasada a la clase.
     *
     * @param null|ConnectionInterface|string $connection
     */
    private function normalizeConnection($connection): ConnectionInterface
    {
        if (is_string($connection) || is_null($connection)) {
            return DB::connection($connection ?? DB::getDefaultConnection());
        }

        if ($connection instanceof ConnectionInterface) {
            return $connection;
        }
    }
}

<?php

namespace Joalvm\Utils;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    /**
     * Contiene la estructura para cada item de la colección.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * @var null|Closure
     */
    protected $casts;

    private $isPaginate = false;

    private $paginate;

    /**
     * @param BaseCollection|LengthAwarePaginator $items
     * @param null|Closure                        $casts
     */
    public function __construct($items, array $schema = [], $casts = null)
    {
        $this->schema = $schema;
        $this->casts = $casts;

        $this->isPaginate = $items instanceof LengthAwarePaginator;

        parent::__construct(
            $this->isPaginate
                ? $items->items()
                : (is_array($items) ? $items : $items->items)
        );

        if ($this->isPaginate) {
            $this->paginate = $items->setCollection(collect());
        }
    }

    public function all()
    {
        return $this->isPaginate
            ? Arr::except(
                $this->paginate->setCollection(
                    collect($this->getAll(parent::all()))
                )->toArray(),
                ['links']
            )
            : $this->getAll(parent::all());
    }

    public function first(callable $callback = null, $default = null)
    {
        return $this->schematize(
            Arr::first($this->items, $callback, $default),
            $this->casts
        );
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $key => &$item) {
            $callback($item, $key);
        }

        return $this;
    }

    public function isPagination(): bool
    {
        return $this->isPaginate;
    }

    /**
     * Reconstruye la estructura del array en base al esquema.
     *
     * @param array|object $item
     *
     * @return array|object
     */
    public function schematize($item, Closure $callback = null)
    {
        $data = [];

        if (is_null($item)) {
            return null;
        }

        foreach ($item as $key => $value) {
            Arr::set($data, $key, $value);
        }

        if (!is_null($callback)) {
            return $this->clean($callback($data));
        }

        return $this->clean($data);
    }

    private function getAll(array $items): array
    {
        return array_map(function ($item) {
            return $this->schematize($item, $this->casts);
        }, $items);
    }

    /**
     * Analiza todos los objetos asociativos en busca de nulls
     * en caso de hallar todos los valores null, convierte el array en null.
     *
     * @param mixed $data
     */
    private function clean($data)
    {
        return array_map(function ($val) {
            if (is_array($val) || is_object($val)) {
                return (
                    count(
                        array_filter(
                            array_values($val = $this->clean($val)),
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

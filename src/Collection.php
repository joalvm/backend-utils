<?php

namespace Joalvm\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
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
     * @var null|\Closure
     */
    protected $casts;

    /**
     * Indica si la colección está paginada.
     *
     * @var bool
     */
    private $isPaginate = false;

    /**
     * Contiene la metadata de la colección cuando está paginada.
     *
     * @var array
     */
    private $metadata;

    /**
     * @param BaseCollection|LengthAwarePaginator $items
     */
    public function __construct($items, array $schema = [])
    {
        parent::__construct($this->normalizeData($items));

        $this->schema = $schema;
    }

    public function normalizeData($items): array
    {
        if ($items instanceof LengthAwarePaginator) {
            $this->isPaginate = true;
            $this->metadata = [
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'from' => $items->firstItem(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'to' => $items->lastItem(),
                'total' => $items->total(),
            ];

            return $items->getCollection()->all();
        }

        if ($items instanceof BaseCollection) {
            return $items->all();
        }

        if ($items instanceof \ArrayAccess or is_array($items)) {
            return $items;
        }

        return [];
    }

    public function all()
    {
        return $this->getAll(parent::all());
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->all();
    }

    public function first(callable $callback = null, $default = null)
    {
        return (
            new Item(Arr::first($this->items, $callback, $default))
        )->schematize($this->casts);
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $key => &$item) {
            $callback($item, $key);
        }

        return $this;
    }

    /**
     * Set the value of casts.
     *
     * @return self
     */
    public function setCasts(?callable $casts)
    {
        $this->casts = $casts;

        return $this;
    }

    public function isPagination(): bool
    {
        return $this->isPaginate;
    }

    private function getAll(array $items): array
    {
        foreach ($items as &$item) {
            $item = new Item($item);

            $item->schematize($this->casts);
        }

        return $items;
    }
}

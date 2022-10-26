<?php

namespace Joalvm\Utils;

use Closure;
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
     * @var null|Closure
     */
    protected $casts;

    private $isPaginate = false;

    private $paginate;

    /**
     * @param BaseCollection|LengthAwarePaginator $items
     */
    public function __construct($items, array $schema = [])
    {
        $this->schema = $schema;

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
                [
                    'links',
                    'first_page_url',
                    'last_page_url',
                    'path',
                    'next_page_url',
                    'prev_page_url',
                ]
            )
            : $this->getAll(parent::all());
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

    protected function normalizeData()
    {
        return $this->isPaginate
            ? Arr::except(
                $this->paginate->setCollection(collect($this->getAll(parent::all())))->toArray(),
                [
                    'links',
                    'first_page_url',
                    'last_page_url',
                    'path',
                    'next_page_url',
                    'prev_page_url',
                ]
            )
            : $this->getAll(parent::all());
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

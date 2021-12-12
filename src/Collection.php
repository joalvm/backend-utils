<?php

namespace Joalvm\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Joalvm\Utils\Schema\Schema;

class Collection extends BaseCollection
{
    /**
     * Contiene la estructura para cada item de la colección.
     *
     * @var Schema
     */
    protected $schema;

    protected $paginationInfo = [];

    private $isPaginate = false;

    /**
     * @param BaseCollection|LengthAwarePaginator $items
     * @param Schema                              $schema
     */
    public function __construct($items, Schema $schema = null)
    {
        $this->isPaginate = $items instanceof LengthAwarePaginator;

        if ($this->isPaginate) {
            $this->assignPaginationInfo($items);
            parent::__construct($items->items());
        } elseif ($items instanceof BaseCollection) {
            parent::__construct($items->all());
        } elseif (is_array($items)) {
            parent::__construct($items);
        }

        $this->schema = $schema;
    }

    public function all()
    {
        return $this->isPaginate
            ? array_merge(
                ['data' => $this->schematize(parent::all())],
                $this->paginationInfo
            )
            : $this->schematize(parent::all());
    }

    public function first(callable $callback = null, $default = null)
    {
        return $this->schematize(Arr::first($this->items, $callback, $default));
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
     * @param array|object $data
     *
     * @return array|object
     */
    private function schematize($data)
    {
        if (is_array($data)) {
            if (array_is_list($data)) {
                return array_map(function ($item) {
                    return $this->schema->schematize($item);
                }, $data);
            }
        }

        return $this->schema->schematize($data);
    }

    private function assignPaginationInfo(LengthAwarePaginator $pagination)
    {
        $this->paginationInfo = [
            'current_page' => $pagination->currentPage(),
            'first_page_url' => $pagination->url(1),
            'from' => $pagination->firstItem(),
            'last_page' => $pagination->lastPage(),
            'last_page_url' => $pagination->url($pagination->lastPage()),
            'next_page_url' => $pagination->nextPageUrl(),
            'per_page' => $pagination->perPage(),
            'prev_page_url' => $pagination->previousPageUrl(),
            'to' => $pagination->lastItem(),
            'total' => $pagination->total(),
        ];
    }
}

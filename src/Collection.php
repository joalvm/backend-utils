<?php

namespace Joalvm\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as BaseCollection;

/**
 * @template TKey of array-key
 * @template TValue of \Invian\Components\Item
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements \Illuminate\Support\Enumerable<TKey, TValue>
 */
class Collection extends BaseCollection
{
    /**
     * Indica si la colección está paginada.
     */
    protected bool $paginate = false;

    /**
     * Contiene la metadata de la colección cuando está paginada.
     */
    private array $metadata = [
        'per_page' => 0,
        'current_page' => null,
        'from' => null,
        'last_page' => null,
        'per_page' => null,
        'to' => null,
        'total' => 0,
    ];

    /**
     * @param BaseCollection|LengthAwarePaginator $items
     */
    public function __construct($items = [])
    {
        parent::__construct($this->handleItems($items));
    }

    public function schematize(?callable $fnCasts = null): self
    {
        $original = $this->items;

        $this->items = [];

        foreach ($original as &$item) {
            $this->items[] = Item::make($item)->schematize($fnCasts);
        }

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function perPage(): ?int
    {
        return $this->metadata['per_page'] ?? null;
    }

    public function currentPage(): ?int
    {
        return $this->metadata['current_page'] ?? null;
    }

    public function from(): ?int
    {
        return $this->metadata['from'] ?? null;
    }

    public function lastPage(): ?int
    {
        return $this->metadata['last_page'] ?? null;
    }

    public function to(): ?int
    {
        return $this->metadata['to'] ?? null;
    }

    public function total(): int
    {
        return $this->metadata['total'] ?? count($this->items) ?? 0;
    }

    public function isPaginate(): bool
    {
        return true === $this->paginate;
    }

    protected function handleItems($items): array
    {
        if ($items instanceof LengthAwarePaginator) {
            $this->paginate = true;
            $this->metadata = [
                'per_page' => $items->perPage(),
                'current_page' => $items->currentPage(),
                'from' => $items->firstItem(),
                'last_page' => $items->lastPage(),
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
}

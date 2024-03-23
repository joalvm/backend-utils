<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Contracts\Database\Query\Expression;
use Joalvm\Utils\Request\Parameters\Paginate;
use Joalvm\Utils\Request\Parameters\Search;
use Joalvm\Utils\Request\Parameters\Sort;

trait QueryParams
{
    /**
     * Paginación de la colección.
     */
    private ?Paginate $paginate;

    /**
     * Búsqueda de la colección.
     */
    private ?Search $search;

    /**
     * Ordenamiento de la colección.
     */
    private ?Sort $sort;

    private function prepare(): self
    {
        $this->prepareSearch();
        $this->prepareSorter();

        return $this;
    }

    private function prepareSearch(): void
    {
        $this->where(function (self $query) {
            foreach ($this->search->getValues($this->schema) as $item) {
                if ($item['column'] instanceof Expression) {
                    $item['column'] = $item['column']->getValue($this->grammar);
                }

                if ($this->isQueryable($item['column'])) {
                    continue;
                }

                $query->orWhereRaw(
                    sprintf('(%s)::text ilike (?)::text', $item['column']),
                    $item['text']
                );
            }
        });
    }

    private function prepareSorter(): void
    {
        foreach ($this->sort->getValues($this->schema) as $item) {
            $this->orderBy($item['column'], $item['order']);
        }
    }
}

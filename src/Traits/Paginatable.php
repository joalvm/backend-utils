<?php

namespace Joalvm\Utils\Traits;

use Symfony\Component\HttpFoundation\ParameterBag;

trait Paginatable
{
    protected $paginate = true;
    protected $page = 1;
    protected $perPage = 10;
    protected $maxPerPage = 150;

    /**
     * Desabilita la paginación.
     *
     * @return static
     */
    public function disablePagination()
    {
        $this->paginate = false;

        return $this;
    }

    protected function boot(): void
    {
        $bag = new ParameterBag($_GET);

        $this->paginate = $bag->getBoolean('paginate', true);

        $perPage = $bag->getInt('per_page', 10);

        if ($perPage > 0 and $perPage <= $this->maxPerPage) {
            $this->perPage = $perPage;
        }

        $page = $bag->getInt('page', 1);

        if (0 > $page) {
            $this->page = $page;
        }
    }
}

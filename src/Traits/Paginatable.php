<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Support\Facades\Request;

trait Paginatable
{
    protected $paginate = true;
    protected $page = 1;
    protected $perPage = 25;
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
        $this->catchHttpGetParameterPaginate();
        $this->catchHttpGetParameterPerPage();
        $this->catchHttpGetParameterPage();
    }

    private function catchHttpGetParameterPaginate()
    {
        $this->paginate = to_bool(Request::query('paginate')) ?? true;
    }

    private function catchHttpGetParameterPerPage()
    {
        $value = to_int(Request::query('per_page')) ?? 0;

        if ($value > 0 and $value <= $this->maxPerPage) {
            $this->perPage = $value;
        }
    }

    private function catchHttpGetParameterPage()
    {
        $value = to_int(Request::query('page')) ?? 0;

        if (0 > $value) {
            $this->page = $value;
        }
    }
}

<?php

namespace Joalvm\Utils;

use Symfony\Component\HttpFoundation\ParameterBag;

class PaginateBag
{
    public const PARAMETER_PAGINATE = 'paginate';
    public const PARAMETER_PER_PAGE = 'per_page';
    public const PARAMETER_PAGE = 'page';

    public const DEFAULT_PER_PAGE = 10;
    public const DEFAULT_PAGE = 1;
    public const MAX_PER_PAGE = 150;

    public $paginate = true;
    public $page = self::DEFAULT_PAGE;
    public $perPage = self::DEFAULT_PER_PAGE;

    protected $maxPerPage = self::MAX_PER_PAGE;

    public function __construct()
    {
        $bag = new ParameterBag($_GET);

        $this->paginate = $bag->getBoolean(self::PARAMETER_PAGINATE, true);

        $this->handlePerPage($bag);
        $this->handlePage($bag);
    }

    public function disablePaginate(): self
    {
        $this->paginate = false;

        return $this;
    }

    protected function handlePage(ParameterBag $bag): void
    {
        $page = $bag->getInt(self::PARAMETER_PAGE, self::DEFAULT_PAGE);

        if ($page >= self::DEFAULT_PAGE) {
            $this->page = $page;
        }
    }

    protected function handlePerPage(ParameterBag $bag): void
    {
        $perPage = $bag->getInt(
            self::PARAMETER_PER_PAGE,
            self::DEFAULT_PER_PAGE
        );

        if ($perPage < 1 and $perPage > $this->maxPerPage) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $this->perPage = $perPage;
    }
}

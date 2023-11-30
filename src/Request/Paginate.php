<?php

namespace Joalvm\Utils\Request;

use Symfony\Component\HttpFoundation\ParameterBag;

class Paginate
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

    public function __construct(array $parameters)
    {
        $bag = new ParameterBag($parameters);

        $this->setPaginate($bag->getBoolean(self::PARAMETER_PAGINATE, true));

        $this->handlePerPage($bag);
        $this->handlePage($bag);
    }

    public function setPaginate(bool $value = true): self
    {
        $this->paginate = $value;

        return $this;
    }

    public function setPage(int $page = self::DEFAULT_PAGE): self
    {
        if ($page >= self::DEFAULT_PAGE) {
            $this->page = $page;
        }

        return $this;
    }

    public function setPerPage(int $perPage = self::DEFAULT_PER_PAGE): self
    {
        if ($perPage > 0 and $perPage <= $this->maxPerPage) {
            $this->perPage = $perPage;
        }

        return $this;
    }

    protected function handlePage(ParameterBag $bag): void
    {
        $page = $bag->getInt('offset', self::DEFAULT_PAGE);

        if ($bag->has(self::PARAMETER_PAGE)) {
            $page = $bag->getInt(self::PARAMETER_PAGE, self::DEFAULT_PAGE);
        }

        $this->setPage($page);
    }

    protected function handlePerPage(ParameterBag $bag): void
    {
        $perPage = $bag->getInt('limit', self::DEFAULT_PER_PAGE);

        if ($bag->has(self::PARAMETER_PER_PAGE)) {
            $perPage = $bag->getInt(
                self::PARAMETER_PER_PAGE,
                self::DEFAULT_PER_PAGE
            );
        }

        $this->setPerPage($perPage);
    }
}

<?php

namespace Joalvm\Utils\Request\Parameters;

use Illuminate\Support\Arr;

class Paginate
{
    public const PARAMETER_PAGINATE_NAME = 'paginate';
    public const PARAMETER_PER_PAGE_NAME = 'per_page';
    public const PARAMETER_PAGE_NAME = 'page';

    public const DEFAULT_PER_PAGE = 10;
    public const DEFAULT_PAGE = 1;
    public const MAX_PER_PAGE = 150;

    protected array $values = [
        self::PARAMETER_PAGINATE_NAME => true,
        self::PARAMETER_PER_PAGE_NAME => self::DEFAULT_PAGE,
        self::PARAMETER_PAGE_NAME => self::DEFAULT_PER_PAGE,
    ];

    protected $maxPerPage = self::MAX_PER_PAGE;

    public function __construct(array $parameters)
    {
        $this->setPaginate(Arr::get($parameters, self::PARAMETER_PAGINATE_NAME));
        $this->setPerPage(Arr::get($parameters, self::PARAMETER_PER_PAGE_NAME));
        $this->setPage(Arr::get($parameters, self::PARAMETER_PAGE_NAME));
    }

    public function setPaginate(mixed $paginate): self
    {
        $this->values[self::PARAMETER_PAGINATE_NAME] = to_bool($paginate) ?? true;

        return $this;
    }

    public function getPaginate(): bool
    {
        return $this->values[self::PARAMETER_PAGINATE_NAME];
    }

    public function setPage(mixed $value): self
    {
        $page = to_int($value) ?? self::DEFAULT_PAGE;

        if ($page >= self::DEFAULT_PAGE) {
            $this->values[self::PARAMETER_PAGE_NAME] = $page;
        }

        return $this;
    }

    public function getPage(): int
    {
        return $this->values[self::PARAMETER_PAGE_NAME];
    }

    public function setPerPage(mixed $value): self
    {
        $perPage = to_int($value) ?? self::DEFAULT_PER_PAGE;

        if ($perPage > 0 and $perPage <= $this->maxPerPage) {
            $this->values[self::PARAMETER_PER_PAGE_NAME] = $perPage;
        }

        return $this;
    }

    public function getPerPage(): int
    {
        return $this->values[self::PARAMETER_PER_PAGE_NAME];
    }

    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->maxPerPage = $maxPerPage;

        return $this;
    }

    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }
}

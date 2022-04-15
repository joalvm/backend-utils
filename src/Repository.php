<?php

namespace Joalvm\Utils;

use Joalvm\Utils\Contracts\Repository as RepositoryContract;

class Repository implements RepositoryContract
{
    /**
     * @var array
     */
    protected $options = [
        Builder::OPTION_FORCE_PAGINATION => false,
    ];

    public function forcePagination()
    {
        $this->options[Builder::OPTION_FORCE_PAGINATION] = true;

        return $this;
    }

    public function builder(): Builder
    {
        $connection = Cast::toStr(func_get_args()[0] ?? null);

        return (new Builder($connection))->setOptions($this->options);
    }
}

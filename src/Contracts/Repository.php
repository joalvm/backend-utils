<?php

namespace Joalvm\Utils\Contracts;

use Joalvm\Utils\Builder;

interface Repository
{
    /**
     * Indica si la lista de recursos está paginada.
     *
     * @return static
     */
    public function forcePagination();

    /**
     * Crea una nueva instancia de la clase builder con la conexión
     * y agrega las opciones.
     *
     * @param null|string $connection
     */
    public function builder(): Builder;
}

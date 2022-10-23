<?php

namespace Joalvm\Utils\Schematic\Contracts;

interface TypeContract
{
    /**
     * Convierte el valor al tipo de dato especificado.
     *
     * @param mixed $value
     *
     * @return static
     */
    public function parse($value);
}

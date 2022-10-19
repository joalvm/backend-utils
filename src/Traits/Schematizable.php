<?php

namespace Joalvm\Utils\Traits;

use Closure;
use Illuminate\Support\Arr;

trait Schematizable
{
    /**
     * Reconstruye la estructura del array en base al esquema.
     *
     * @param array|object $item
     *
     * @return array|object
     */
    public function schematize($item, Closure $callback = null)
    {
        $data = [];

        if (is_null($item)) {
            return null;
        }

        foreach ($item as $key => $value) {
            Arr::set($data, $key, $value);
        }

        if (!is_null($callback)) {
            $callback($data);
        }

        return $this->clean($data);
    }

    /**
     * Analiza todos los objetos asociativos en busca de nulls
     * en caso de hallar todos los valores null, convierte el array en null.
     *
     * @param mixed $data
     */
    private function clean($data)
    {
        return array_map(function ($val) {
            if (is_array($val) || is_object($val)) {
                return (
                    count(
                        array_filter(
                            array_values($val = $this->clean($val)),
                            function ($item) {
                                return !is_null($item);
                            }
                        )
                    ) > 0
                ) ? $val : null;
            }

            return $val;
        }, $data);
    }
}

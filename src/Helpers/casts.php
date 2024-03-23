<?php

use Illuminate\Support\Arr;
use Joalvm\Utils\Item;

if (!function_exists('cast_assoc_int')) {
    /**
     * Castea a enteros los valores de un array asociativo.
     *
     * @param array|Item $array
     * @param string[]   $keys
     */
    function cast_assoc_int(&$array, array $keys = [])
    {
        if ($array instanceof Item) {
            $array->intValues($keys);

            return;
        }

        foreach ($keys as $key) {
            if (Arr::has($array, $key)) {
                Arr::set($array, $key, to_int(Arr::get($array, $key)));
            }
        }
    }
}

if (!function_exists('cast_assoc_float')) {
    /**
     * Castea a float los valores de un array asociativo.
     *
     * @param array|Item $array
     * @param string[]   $keys
     */
    function cast_assoc_float(
        &$array,
        array $keys = [],
        int $precision = -1,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        if ($array instanceof Item) {
            $array->floatValues($keys, $precision, $mode);

            return;
        }

        foreach ($keys as $key) {
            if (Arr::has($array, $key)) {
                Arr::set(
                    $array,
                    $key,
                    to_float(Arr::get($array, $key), $precision, $mode)
                );
            }
        }
    }
}

if (!function_exists('cast_assoc_json')) {
    /**
     * Castea un json a array asociativo, los valores de un array asociativo.
     *
     * @param array|Item $array
     * @param string[]   $keys
     */
    function cast_assoc_json(&$array, array $keys = [], bool $associative = true)
    {
        if ($array instanceof Item) {
            $array->jsonValues($keys);

            return;
        }
        foreach ($keys as $key) {
            if (Arr::has($array, $key)) {
                $val = Arr::get($array, $key);
                Arr::set(
                    $array,
                    $key,
                    '{}' === $val ? new stdClass() : json_decode($val, $associative)
                );
            }
        }
    }
}

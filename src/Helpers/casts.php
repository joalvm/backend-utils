<?php

use Illuminate\Support\Arr;

if (!function_exists('cast_assoc_int')) {
    /**
     * Castea a enteros los valores de un array asociativo.
     *
     * @param array|ArrayAccess $item
     * @param string[]          $keys
     */
    function cast_assoc_int(&$item, array $keys = [])
    {
        foreach ($keys as $key) {
            if (Arr::has($item, $key)) {
                Arr::set($item, $key, to_int(Arr::get($item, $key)));
            }
        }
    }
}

if (!function_exists('cast_assoc_float')) {
    /**
     * Castea a float los valores de un array asociativo.
     *
     * @param array|ArrayAccess $item
     * @param string[]          $keys
     */
    function cast_assoc_float(
        &$item,
        array $keys = [],
        int $precision = -1,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        foreach ($keys as $key) {
            if (Arr::has($item, $key)) {
                Arr::set(
                    $item,
                    $key,
                    to_float(Arr::get($item, $key), $precision, $mode)
                );
            }
        }
    }
}

if (!function_exists('cast_assoc_numeric')) {
    /**
     * Castea a numerico los valores de un array asociativo.
     *
     * @param array|ArrayAccess $item
     * @param string[]          $keys
     */
    function cast_assoc_numeric(
        &$item,
        array $keys = [],
        int $precision = -1,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        foreach ($keys as $key) {
            if (Arr::has($item, $key)) {
                Arr::set($item, $key, to_numeric(
                    Arr::get($item, $key),
                    $precision,
                    $mode
                ));
            }
        }
    }
}

if (!function_exists('cast_assoc_bool')) {
    /**
     * Castea a boleano los valores de un array asociativo.
     *
     * @param array|ArrayAccess $item
     * @param string[]          $keys
     */
    function cast_assoc_bool(&$item, array $keys = [])
    {
        foreach ($keys as $key) {
            if (Arr::has($item, $key)) {
                Arr::set($item, $key, to_bool(Arr::get($item, $key)));
            }
        }
    }
}

if (!function_exists('cast_assoc_json')) {
    /**
     * Castea un json a array asociativo, los valores de un array asociativo.
     *
     * @param array|ArrayAccess $item
     * @param string[]          $keys
     */
    function cast_assoc_json(&$item, array $keys = [])
    {
        foreach ($keys as $key) {
            if (Arr::has($item, $key)) {
                Arr::set($item, $key, json_decode(Arr::get($item, $key), true));
            }
        }
    }
}

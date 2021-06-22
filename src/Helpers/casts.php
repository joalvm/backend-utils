<?php

if (!function_exists('cast_int')) {
    /**
     * castea valores numericos, de tipo entero, de un objeto.
     *
     * @param array $row    Objeto a castear
     * @param array $fields Keys cuyos valores serán casteados (acepta notación DOT)
     */
    function cast_int(array &$row, array $fields = [])
    {
        foreach ($fields as $field) {
            if (\Illuminate\Support\Arr::has($row, $field)) {
                \Illuminate\Support\Arr::set(
                    $row,
                    $field,
                    (int) \Illuminate\Support\Arr::get($row, $field)
                );
            }
        }
    }
}

if (!function_exists('cast_float')) {
    /**
     * castea valores numericos, de tipo flotante, de un objeto.
     *
     * @param array $row    Objeto a castear
     * @param array $fields Keys cuyos valores serán casteados (acepta notación DOT)
     */
    function cast_float(array &$row, array $fields = [])
    {
        foreach ($fields as $field) {
            if (\Illuminate\Support\Arr::has($row, $field)) {
                \Illuminate\Support\Arr::set(
                    $row,
                    $field,
                    to_float(\Illuminate\Support\Arr::get($row, $field))
                );
            }
        }
    }
}

if (!function_exists('cast_json')) {
    /**
     * castea texto, de tipo json, de un objeto.
     *
     * @param array $row    Objeto a castear
     * @param array $fields Keys cuyos valores serán casteados (acepta notación DOT)
     */
    function cast_json(array &$row, array $fields = [])
    {
        foreach ($fields as $field) {
            if (\Illuminate\Support\Arr::has($row, $field)) {
                \Illuminate\Support\Arr::set(
                    $row,
                    $field,
                    json_decode(\Illuminate\Support\Arr::get($row, $field), true)
                );
            }
        }
    }
}

if (!function_exists('cast_point')) {
    /**
     * castea texto, de tipo point postgresql, de un objeto.
     *
     * @param array $row    Objeto a castear
     * @param array $fields Keys cuyos valores serán casteados (acepta notación DOT)
     */
    function cast_point(array &$row, array $fields = [])
    {
        foreach ($fields as $field) {
            if (\Illuminate\Support\Arr::has($row, $field)) {
                \Illuminate\Support\Arr::set(
                    $row,
                    $field,
                    point_to_array(\Illuminate\Support\Arr::get($row, $field))
                );
            }
        }
    }
}

<?php

if (!function_exists('to_str')) {
    /**
     * Castea un valor a string.
     *
     * @param mixed $value
     */
    function to_str($value): ?string
    {
        return !strlen($value = trim(strval($value))) ? null : $value;
    }
}

if (!function_exists('to_int')) {
    /**
     * Castea un valor a entero.
     *
     * @param mixed $value
     */
    function to_int($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value = is_string($value) ? to_str($value) : $value)) {
            return intval($value);
        }

        return null;
    }
}

if (!function_exists('to_float')) {
    /**
     * Castea un valor a flotante.
     *
     * @param mixed $value
     */
    function to_float($value, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): ?float
    {
        if (is_float($value)) {
            return 0 === $precision ? $value : round($value, $precision, $mode);
        }

        if (is_numeric($value = is_string($value) ? to_str($value) : $value)) {
            return 0 === $precision
                ? floatval($value)
                : round(floatval($value), $precision, $mode);
        }

        return null;
    }
}

if (!function_exists('to_numeric')) {
    /**
     * Castea un valor a entero o flotante.
     *
     * @param mixed $value
     *
     * @return null|float|int
     */
    function to_numeric($value, int $precision = 0, int $mode = PHP_ROUND_HALF_UP)
    {
        if (is_numeric($value)) {
            return is_float($value + 0)
                ? to_float($value, $precision, $mode)
                : to_int($value);
        }

        return null;
    }
}

if (!function_exists('to_bool')) {
    /**
     * Castea un valor a booleano.
     *
     * @param mixed $value
     */
    function to_bool($value): ?bool
    {
        if (is_bool($value)) {
            return boolval($value);
        }

        if (is_string($value) or is_int($value)) {
            if (preg_match('/^(true|1|yes|on|y|t)$/i', to_str($value))) {
                return true;
            }

            if (preg_match('/^(false|0|no|n|off|f)$/i', to_str($value))) {
                return false;
            }
        }

        return null;
    }
}

if (!function_exists('to_list')) {
    /**
     * Castea un valor a una lista, si es un string se separa por comas.
     *
     * @param mixed $values
     */
    function to_list(
        $values,
        bool $keepNulls = false,
        string $separator = ','
    ): array {
        if (is_string($values)) {
            $values = explode($separator, $values);
            foreach ($values as $index => $value) {
                $values[$index] = to_str($value);
            }
        }

        if (!is_array($values)) {
            $values = [];
        }

        return array_values(
            array_filter($values, function ($value) use ($keepNulls) {
                return is_null($value) ? $keepNulls : true;
            })
        );
    }
}

if (!function_exists('to_list_int')) {
    /**
     * Castea un valor a una lista de enteros, si es un string se separa por comas.
     *
     * @param mixed $values
     */
    function to_list_int($values, bool $keepNulls = false, string $separator = ','): array
    {
        $array = [];

        foreach (to_list($values, $keepNulls, $separator) as $value) {
            if (is_null($value) and $keepNulls) {
                $array[] = null;

                continue;
            }

            if (is_numeric($value = to_int($value))) {
                $array[] = $value;
            }
        }

        return $array;
    }
}

if (!function_exists('to_list_float')) {
    /**
     * Castea un valor a una lista de flotantes, si es un string se separa por comas.
     *
     * @param mixed $values
     */
    function to_list_float(
        $values,
        bool $keepNulls = false,
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP,
        string $separator = ','
    ): array {
        $array = [];

        foreach (to_list($values, $keepNulls, $separator) as $value) {
            if (is_null($value) and $keepNulls) {
                $array[] = null;

                continue;
            }

            if (is_numeric($value = to_float($value, $precision, $mode))) {
                $array[] = $value;
            }
        }

        return $array;
    }
}

if (!function_exists('to_list_numeric')) {
    /**
     * Castea un valor a una lista de enteros o flotantes, si es un string se separa por comas.
     *
     * @param mixed $values
     */
    function to_list_numeric(
        $values,
        bool $keepNulls = false,
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP,
        string $separator = ','
    ): array {
        $array = [];

        foreach (to_list($values, $keepNulls, $separator) as $value) {
            if (is_null($value) and $keepNulls) {
                $array[] = null;

                continue;
            }

            if (is_numeric($value = to_numeric($value, $precision, $mode))) {
                $array[] = $value;
            }
        }

        return $array;
    }
}

if (!function_exists('to_list_bool')) {
    /**
     * Castea un valor a una lista de booleano, si es un string se separa por comas.
     *
     * @param mixed $values
     */
    function to_list_bool($values, bool $keepNulls = false, string $separator = ','): array
    {
        $array = [];

        foreach (to_list($values, $keepNulls, $separator) as $value) {
            if (is_null($value) and $keepNulls) {
                $array[] = null;

                continue;
            }

            if (is_bool($value = to_bool($value))) {
                $array[] = $value;
            }
        }

        return $array;
    }
}

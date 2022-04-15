<?php

if (!function_exists('to_str')) {
    /**
     * Castea un valor a string.
     *
     * @param mixed $value
     */
    function to_str($value): ?string
    {
        return strlen($str = trim(strval($value))) > 0 ? $str : null;
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
            if ($precision > 0) {
                return round($value, $precision, $mode);
            }

            return $value;
        }

        if (is_numeric($value = is_string($value) ? to_str($value) : $value)) {
            $value = floatval($value);

            if ($precision > 0) {
                return round($value, $precision, $mode);
            }

            return $value;
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
     * @param mixed $value
     */
    function to_list($value, bool $keepNulls = false): array
    {
        if (is_string($value)) {
            $value = array_map(
                function ($item) {
                    return strlen($item = trim($item)) > 0 ? $item : null;
                },
                explode(',', $value)
            );
        }

        if (is_array($value)) {
            return array_filter(
                $value,
                function ($value) use ($keepNulls) {
                    return (!$keepNulls and is_null($value)) ? false : true;
                }
            );
        }

        return [];
    }
}

if (!function_exists('to_list_str')) {
    /**
     * Castea un valor a una lista de strings, si es un string se separa por comas.
     *
     * @param mixed $value
     */
    function to_list_str($value, bool $keepNulls = false): array
    {
        return array_map(
            function ($item) {
                return is_null($item) ? $item : strval($item);
            },
            to_list($value, $keepNulls)
        );
    }
}

if (!function_exists('to_list_int')) {
    /**
     * Castea un valor a una lista de enteros, si es un string se separa por comas.
     *
     * @param mixed $value
     */
    function to_list_int($value, bool $keepNulls = false): array
    {
        return array_values(
            array_filter(
                array_map(
                    function ($item) {
                        return is_null($item) ? $item : to_int($item) ?? '';
                    },
                    to_list($value, $keepNulls)
                ),
                function ($item) {
                    return is_int($item) or is_null($item);
                }
            )
        );
    }
}

if (!function_exists('to_list_float')) {
    /**
     * Castea un valor a una lista de flotantes, si es un string se separa por comas.
     *
     * @param mixed $value
     */
    function to_list_float(
        $value,
        bool $keepNulls = false,
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP
    ): array {
        return array_values(
            array_filter(
                array_map(
                    function ($item) use ($precision, $mode) {
                        return is_null($item)
                            ? $item
                            : to_float($item, $precision, $mode) ?? '';
                    },
                    to_list($value, $keepNulls)
                ),
                function ($item) {
                    return is_float($item) or is_null($item);
                }
            )
        );
    }
}

if (!function_exists('to_list_numeric')) {
    /**
     * Castea un valor a una lista de enteros o flotantes, si es un string se separa por comas.
     *
     * @param mixed $value
     */
    function to_list_numeric(
        $value,
        bool $keepNulls = false,
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP
    ): array {
        return array_values(
            array_filter(
                array_map(
                    function ($item) use ($precision, $mode) {
                        return is_null($item)
                            ? $item
                            : to_numeric($item, $precision, $mode) ?? '';
                    },
                    to_list($value, $keepNulls)
                ),
                function ($item) {
                    return is_numeric($item) or is_null($item);
                }
            )
        );
    }
}

if (!function_exists('to_list_bool')) {
    /**
     * Castea un valor a una lista de booleano, si es un string se separa por comas.
     *
     * @param mixed $value
     */
    function to_list_bool($value, bool $keepNulls = false): array
    {
        return array_values(
            array_filter(
                array_map(
                    function ($item) {
                        return is_null($item) ? $item : to_bool($item) ?? '';
                    },
                    to_list($value, $keepNulls)
                ),
                function ($item) {
                    return is_bool($item) or is_null($item);
                }
            )
        );
    }
}

if (!function_exists('param_str')) {
    /**
     * Filtra y sanitiza un valor de una variable de entrada.
     *
     * @param mixed $value
     */
    function param_str($value): ?string
    {
        return to_str(filter_var($value, FILTER_SANITIZE_STRING));
    }
}

if (!function_exists('param_int')) {
    /**
     * Obtiene un parametro de la petición, si no existe retorna un valor por defecto.
     *
     * @param mixed $value
     */
    function param_int($value): ?int
    {
        return to_int(
            filter_var(
                $value,
                FILTER_SANITIZE_NUMBER_INT,
                [
                    'filter' => FILTER_VALIDATE_INT,
                    'flags' => FILTER_FLAG_ALLOW_FRACTION,
                ]
            )
        );
    }
}

if (!function_exists('param_float')) {
    /**
     * Obtiene un parametro de la petición, si no existe retorna un valor por defecto.
     *
     * @param mixed $value
     */
    function param_float(
        $value,
        int $precision = 0,
        int $mode = PHP_ROUND_HALF_UP
    ): ?float {
        return to_float(
            filter_var(
                $value,
                FILTER_SANITIZE_NUMBER_FLOAT,
                [
                    'filter' => FILTER_VALIDATE_FLOAT,
                    'flags' => FILTER_FLAG_ALLOW_FRACTION,
                ]
            ),
            $precision,
            $mode
        );
    }
}

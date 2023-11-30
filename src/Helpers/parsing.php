<?php

if (!function_exists('to_str')) {
    /**
     * Converts a value to a string.
     *
     * @param mixed $value the value to be converted to a string
     *
     * @return null|string the converted string value, or null if the resulting string has a length of zero
     */
    function to_str($value): ?string
    {
        return !strlen($value = trim(strval($value))) ? null : $value;
    }
}

if (!function_exists('to_int')) {
    /**
     * Converts a value to an integer.
     *
     * @param mixed $value the value to be converted to an integer
     *
     * @return null|int the converted integer value or null if the value cannot be converted
     */
    function to_int($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        $stringValue = to_str($value);

        if (is_numeric($stringValue)) {
            return (int) $stringValue;
        }

        return null;
    }
}

if (!function_exists('to_float')) {
    // @phpcs:disable Generic.Files.LineLength.TooLong
    /**
     * Converts a value to a float.
     *
     * @param mixed $value     the value to be converted to a float
     * @param int   $precision The number of decimal places to round the float to. If negative, no rounding is performed. Default is -1.
     * @param int   $mode      The rounding mode to use. Default is `PHP_ROUND_HALF_UP`.
     *
     * @return null|float the input value converted to a float, rounded if necessary, or null if the input value is not numeric
     */
    function to_float($value, int $precision = -1, int $mode = PHP_ROUND_HALF_UP): ?float
    {
        if (is_float($value)) {
            return $precision < 0 ? $value : round($value, $precision, $mode);
        }

        if (is_numeric($value = to_str($value))) {
            $value = floatval($value);

            return $precision < 0 ? $value : round($value, $precision, $mode);
        }

        return null;
    }
}

if (!function_exists('to_numeric')) {
    /**
     * Castea un valor a entero o flotante.
     *
     * @param mixed $value     el valor a convertir a un tipo numérico
     * @param int   $precision (opcional) El número de decimales a redondear el valor flotante. Por defecto es -1.
     * @param int   $mode      (opcional) El modo de redondeo a utilizar al redondear el valor flotante. Por defecto es `PHP_ROUND_HALF_UP`.
     *
     * @return null|float|int Devuelve null si el valor no es numérico. Devuelve el valor flotante convertido si el valor es un flotante. Devuelve el valor entero convertido si el valor no es un flotante.
     */
    function to_numeric($value, int $precision = -1, int $mode = PHP_ROUND_HALF_UP)
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
     * @param mixed $value the value to be converted to a boolean
     *
     * @return null|bool Returns a boolean value if the input can be converted to true or false. Returns null if the input cannot be converted to a boolean.
     */
    function to_bool($value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            if (preg_match('/^(true|1|yes|on|y|t)$/i', to_str($value))) {
                return true;
            }

            if (preg_match('/^(false|0|no|n|off|f)$/i', to_str($value))) {
                return false;
            }
            dd('is_scalar', to_str($value));
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
        int $precision = -1,
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
        int $precision = -1,
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
     * Returns an array of booleans from a string or array of values.
     * If $keepNulls is true, it will include any null values as well.
     * $separator is the delimiter used to separate values in the string.
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

            if (!is_bool($value = to_bool($value))) {
                continue;
            }

            $array[] = $value;
        }

        return $array;
    }
}

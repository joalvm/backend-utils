<?php

if (!function_exists('to_bool')) {
    /**
     * Valida y formatea un valor de tipo boleano.
     *
     * @param mixed $dta
     */
    function to_bool($dta): ?bool
    {
        if (is_bool($dta)) {
            return (bool) $dta;
        }

        if (is_string($dta)) {
            if (preg_match('/^(true|1|yes|on|y|t)$/m', $dta)) {
                return true;
            }
            if (preg_match('/^(false|0|no|n|off|f)$/m', $dta)) {
                return false;
            }
        }

        return null;
    }
}

if (!function_exists('to_int')) {
    /**
     * Valida y formatea un valor de tipo numerico entero.
     *
     * @param mixed $dta
     */
    function to_int($dta): ?int
    {
        return is_numeric($dta) ? intval($dta) : null;
    }
}

if (!function_exists('to_float')) {
    /**
     * Valida y formatea un valor de tipo numerico flotante.
     *
     * @param mixed $dta
     */
    function to_float($dta): ?float
    {
        return is_numeric($dta) ? floatval($dta) : null;
    }
}

if (!function_exists('to_numeric')) {
    /**
     * Castea un valor numerico a su tipo flotante o entero.
     *
     * @param null|string $value
     *
     * @return null|float|int
     */
    function to_numeric($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if (!is_numeric($value) || is_null($value) || '' === $value) {
            return null;
        }

        return is_float($value + 0) ? (float) $value : (int) $value;
    }
}

if (!function_exists('to_array')) {
    /**
     * Formatea un string separado por comas y lo convierte en array.
     *
     * @param null|array|string $dta
     */
    function to_array($dta): array
    {
        return is_string($dta)
                ? array_filter(
                    explode(
                        ',',
                        str_replace([' ,', ', ', ' , '], [',', ',', ','], $dta)
                    )
                ) : (is_array($dta) ? array_filter($dta) : [])
        ;
    }
}

if (!function_exists('to_array_int')) {
    /**
     * Formatea un string a array y lo filtra por valores numericos.
     *
     * @param null|array|string $dta
     */
    function to_array_int($dta): ?array
    {
        return array_map(
            'intval',
            array_filter(
                to_array($dta),
                'is_numeric'
            )
        );
    }
}

if (!function_exists('to_array_float')) {
    /**
     * Formatea un string a array y lo filtra por valores numericos.
     *
     * @param null|array|string $dta
     */
    function to_array_float($dta): ?array
    {
        return array_map(
            'floatval',
            array_filter(
                to_array($dta),
                'is_numeric'
            )
        );
    }
}

if (!function_exists('to_array_str')) {
    /**
     * Formatea un string separado por comas y lo convierte en array.
     *
     * @param array|string $dta
     */
    function to_array_str($dta): array
    {
        return array_map('trim', array_filter(to_array($dta)));
    }
}

if (!function_exists('is_assoc')) {
    /**
     * Verifica si el array es de tipo associativo.
     *
     * @param mixed $var
     *
     * @return bool
     */
    function is_assoc($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
}

if (!function_exists('point_to_array')) {
    /**
     * Convierte un valor Point de Postgresql a un array con dos valores.
     */
    function point_to_array(?string $dta): ?array
    {
        return array_map(
            'floatVal',
            explode(',', trim(trim(trim($dta), ')'), '('))
        );
    }
}

<?php

namespace Joalvm\Utils;

class Cast
{
    public static function toStr($value): ?string
    {
        if (is_string($value)) {
            if (strlen($value = trim(strval($value)))) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Convierte un valor al tipo de dato entero.
     *
     * @param null|int|string $value
     */
    public static function toInt($value): ?int
    {
        return is_numeric($value) ? intval($value) : null;
    }

    /**
     * Castea un valor flotante.
     *
     * @param null|float|string $value
     */
    public static function toFloat(
        $value,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ): ?float {
        if (is_float($value) || is_numeric($value)) {
            return $precision
                ? round($value = floatval($value), $precision, $mode)
                : $value;
        }

        return null;
    }

    /**
     * Castea un valor numerico a su tipo flotante o entero.
     * Los parametros precision y mode seran omitidos en caso sea un entero.
     *
     * @param null|float|int|string $value
     *
     * @return null|float|int
     */
    public static function toNumeric(
        $value,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        if (is_numeric($value)) {
            return is_float($value + 0)
                ? self::toFloat($value, $precision, $mode)
                : self::toInt($value);
        }

        return null;
    }

    public static function toBool($value): ?bool
    {
        if (is_bool($value)) {
            return boolval($value);
        }

        if (is_string($value)) {
            if (preg_match('/^(true|1|yes|on|y|t)$/m', $value)) {
                return true;
            }
            if (preg_match('/^(false|0|no|n|off|f)$/m', $value)) {
                return false;
            }
        }

        if (is_numeric($value)) {
            if (1 == $value) {
                return true;
            }
            if (0 == $value) {
                return false;
            }
        }

        return null;
    }

    /**
     * Convierte un string separado por comas(,) en un array
     * y lo limpia de valores null's.
     *
     * @param null|array|string $array
     */
    public static function toList($array, bool $keepNulls = false): array
    {
        return array_values(
            array_filter(
                is_array($array)
                    ? $array
                    : array_map(
                        function ($item) {
                            return self::toStr($item);
                        },
                        explode(',', is_string($array) ? $array : '')
                    ),
                function ($item) use ($keepNulls) {
                    return !is_null($item) and !$keepNulls;
                }
            )
        );
    }

    /**
     * Filtra un array con solo valores de tipo string, en caso sea un string
     * separado por comas(,), lo convierte a array.
     *
     * @param null|string|string[] $strList
     *
     * @return string[]
     */
    public static function toListStr($strList, bool $keepNulls = false)
    {
        return array_values(
            array_filter(
                array_map('self::toStr', self::toList($strList, $keepNulls)),
                function (?string $item) use ($keepNulls) {
                    return !is_null($item) and !$keepNulls;
                }
            )
        );
    }

    /**
     * Filtra un array con solo valores enteros, en caso sea un string
     * separado por comas(,), lo convierte a array.
     *
     * @param null|int[]|string $intList
     *
     * @return int[]
     */
    public static function toListInt($intList, bool $keepNulls = false)
    {
        return array_values(
            array_filter(
                array_map('self::toInt', self::toList($intList, $keepNulls)),
                function (?int $item) use ($keepNulls) {
                    return !is_null($item) and !$keepNulls;
                }
            )
        );
    }

    /**
     * Filtra un array con solo valores flotantes, en caso sea un string
     * separado por comas(,), lo convierte a array.
     *
     * @param null|float[]|string $floatList
     *
     * @return float[]
     */
    public static function toListFloat(
        $floatList,
        bool $keepNulls = false,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        return array_values(
            array_filter(
                array_map(
                    function ($value) use ($precision, $mode) {
                        return Cast::toFloat($value, $precision, $mode);
                    },
                    self::toList($floatList, $keepNulls)
                ),
                function ($item) use ($keepNulls) {
                    return !is_null($item) and !$keepNulls;
                }
            )
        );
    }

    /**
     * Filtra un array con solo valores numericos, en caso sea un string
     * separado por comas(,), lo convierte en array.
     *
     * @param null|string|float[|int[] $numericList
     *
     * @return float[]|int[]
     */
    public static function toListNumeric(
        $numericList,
        bool $keepNulls = false,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ) {
        return array_values(
            array_filter(
                array_map(
                    function ($value) use ($precision, $mode) {
                        return Cast::toNumeric($value, $precision, $mode);
                    },
                    self::toList($numericList, $keepNulls)
                ),
                function ($item) use ($keepNulls) {
                    return !is_null($item) and !$keepNulls;
                }
            )
        );
    }

    /**
     * Castea los valores de un array asociativo a valores enteros.
     *
     * @param array    $assoc Array Asociativo a ser evaluado
     * @param string[] $keys  Lista de keys a ser buscados
     */
    public static function toMapInt(array &$assoc, array $keys = []): array
    {
        foreach (array_values($keys) as $key) {
            if (Arr::has($assoc, $key)) {
                Arr::set($assoc, $key, self::toInt(Arr::get($assoc, $key)));
            }
        }

        return $assoc;
    }

    /**
     * Castea los valores de un array asociativo a valores flotantes.
     *
     * @param array    $assoc     Array Asociativo a ser evaluado
     * @param string[] $keys      Lista de keys a ser buscados
     * @param null|int $precision Cantidad de decimales a ser redondeado
     * @param int      $mode      Modo de redondeo de los decimales
     */
    public static function toMapFloat(
        array &$assoc,
        array $keys,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ): array {
        foreach ($keys as $key) {
            if (Arr::has($assoc, $key)) {
                Arr::set(
                    $assoc,
                    $key,
                    self::toFloat(Arr::get($assoc, $key), $precision, $mode)
                );
            }
        }

        return $assoc;
    }

    /**
     * Castea los valores de un array asociativo a valores flotantes.
     *
     * @param array    $assoc     Array Asociativo a ser evaluado
     * @param string[] $keys      Lista de keys a ser buscados
     * @param null|int $precision Cantidad de decimales a ser redondeado
     * @param int      $mode      Modo de redondeo de los decimales
     */
    public static function toMapNumeric(
        array &$assoc,
        array $keys,
        ?int $precision = null,
        int $mode = PHP_ROUND_HALF_UP
    ): array {
        foreach ($keys as $key) {
            if (Arr::has($assoc, $key)) {
                Arr::set(
                    $assoc,
                    $key,
                    self::toFloat(Arr::get($assoc, $key), $precision, $mode)
                );
            }
        }

        return $assoc;
    }

    /**
     * Castea los valores de tipo json de un array asociativo.
     *
     * @param array $assoc      Array Asociativo a ser evaluado
     * @param array $keys       Lista de keys a ser buscados
     * @param bool  $associtive si el json_decode devulve una clase stdClass o un array asociativo
     */
    public static function toMapJson(
        array &$assoc,
        array $keys,
        bool $associtive = true
    ): array {
        foreach ($keys as $key) {
            if (Arr::has($assoc, $key)) {
                Arr::set(
                    $assoc,
                    $key,
                    json_decode(Arr::get($assoc, $key), $associtive)
                );
            }
        }

        return $assoc;
    }
}

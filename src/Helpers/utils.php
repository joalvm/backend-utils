<?php

if (!function_exists('is_array_assoc')) {
    /**
     * Verifica si un valor es de tipo array asociativo.
     *
     * @param mixed $value
     */
    function is_array_assoc($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return !array_is_list($value);
    }
}

if (!function_exists('is_array_list')) {
    /**
     * Verifica si un valor es una lista de elemento (array no asociativo).
     *
     * @param mixed $value
     */
    function is_array_list($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return array_is_list($value);
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Convierte el numero bytes en un formato mas legible.
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots.
     */
    function dot(iterable $iterable, string $prefix = ''): array
    {
        $result = [];

        foreach ($iterable as $key => $value) {
            if (!is_array($value)) {
                $result["{$prefix}{$key}"] = $value;

                continue;
            }

            $result = array_merge($result, dot($value, "{$prefix}{$key}."));
        }

        return $result;
    }
}

if (!function_exists('undot')) {
    /**
     * Convierte un array asociativo plano, cuyas keys contengan
     * la notación dot, a  un array sociativo multinivel.
     */
    function undot(iterable $iterable): array
    {
        $result = [];

        foreach ($iterable as $key => $value) {
            array_set($result, $key, $value);
        }

        return $result;
    }
}

if (!function_exists('array_set')) {
    /**
     * Agrega un valor a un iterable usando la notación dot.
     *
     * @param mixed $value
     */
    function array_set(iterable &$iterable, ?string $key, $value): array
    {
        if (is_null($key)) {
            return $iterable = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (1 === count($keys)) {
                break;
            }

            unset($keys[$i]);

            if (!isset($iterable[$key]) || !is_array($iterable[$key])) {
                $iterable[$key] = [];
            }

            $iterable = &$iterable[$key];
        }

        $iterable[array_shift($keys)] = $value;

        return $iterable;
    }
}

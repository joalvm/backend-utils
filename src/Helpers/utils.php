<?php

if (!function_exists('is_array_assoc')) {
    /**
     * Check if value is associative array.
     *
     * @param mixed $value
     *
     * @return bool
     */
    function is_array_assoc($value)
    {
        if (!is_array($value)) {
            return false;
        }

        return array_keys($value) !== range(0, count($value) - 1);
    }
}

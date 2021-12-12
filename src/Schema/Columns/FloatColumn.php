<?php

namespace Joalvm\Utils\Schema\Columns;

class FloatColumn extends AbstractColumn
{
    private $precision;

    private $mode = PHP_ROUND_HALF_UP;

    public function roundUp(int $precision = 2): self
    {
        $this->precision = $precision;
        $this->mode = PHP_ROUND_HALF_UP;

        return $this;
    }

    public function roundDown(int $precision = 2): self
    {
        $this->precision = $precision;
        $this->mode = PHP_ROUND_HALF_DOWN;

        return $this;
    }

    public function parse($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_float($value) || is_numeric($value)) {
            return $this->precision
                ? round($value = floatval($value), $this->precision, $this->mode)
                : $value;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'El valor "%s" no es un numero flotante o numerico.',
                $value
            )
        );
    }
}

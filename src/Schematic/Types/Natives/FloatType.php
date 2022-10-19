<?php

namespace Joalvm\Utils\Schematic\Types\Natives;

use Joalvm\Utils\Schematic\Types\Type;

class FloatType extends Type
{
    /**
     * @var int
     */
    private $precision = 0;

    /**
     * @var int
     */
    private $mode = PHP_ROUND_HALF_UP;

    public function roundUp(int $precision = 0): self
    {
        $this->precision = $precision;
        $this->mode = PHP_ROUND_HALF_UP;

        return $this;
    }

    public function roundDown(int $precision = 0): self
    {
        $this->precision = $precision;
        $this->mode = PHP_ROUND_HALF_DOWN;

        return $this;
    }

    public function parse($value): ?float
    {
        return to_float($value, $this->precision, $this->mode);
    }
}

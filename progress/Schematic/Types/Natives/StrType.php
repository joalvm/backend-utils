<?php

namespace Joalvm\Utils\Schematic\Types\Natives;

use Joalvm\Utils\Schematic\Types\Type;

class StrType extends Type
{
    /**
     * Caracteres usados para la función trim.
     *
     * @var string
     */
    private $characters;

    /**
     * Convierte a null los string vacios.
     *
     * @var bool
     */
    private $emptyToNull = true;

    public function parse($value): ?string
    {
        if ($this->characters) {
            $value = trim(strval($value), $this->characters);
        }

        if ($this->emptyToNull) {
            return $value ?: null;
        }

        return $value;
    }

    public function trim(string $characters = "\t\n\r\x0B\x00\x0C\x0D"): self
    {
        $this->characters = $characters;

        return $this;
    }

    public function toEmpty(): self
    {
        $this->emptyToNull = false;

        return $this;
    }
}

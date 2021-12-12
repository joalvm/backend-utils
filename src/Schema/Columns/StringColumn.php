<?php

namespace Joalvm\Utils\Schema\Columns;

class StringColumn extends AbstractColumn
{
    /**
     * @var string
     */
    protected $trimCharacters = '';

    /**
     * @var bool
     */
    protected $emptyToNull = false;

    /**
     * @param null|string $value String value
     */
    public function parse($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim(strval($value));

        if ($this->emptyToNull && empty($value)) {
            return null;
        }

        if (!empty($this->trimCharacters)) {
            $value = trim($value, $this->trimCharacters);
        }

        return $value;
    }

    public function customTrim(string $characters = ''): self
    {
        $this->trimCharacters = $characters;

        return $this;
    }

    public function emptyToNull(): self
    {
        $this->emptyToNull = true;

        return $this;
    }
}

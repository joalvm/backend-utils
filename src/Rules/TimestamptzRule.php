<?php

namespace Joalvm\Utils\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class TimestamptzRule implements Rule
{
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
    }

    public function validate($attribute, $value, $args, $validator)
    {
        return $this->passes($attribute, $value);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        $format = sprintf(
            '%s%s%s%s',
            'Y-m-d',
            Str::contains($value, 'T') ? '\T' : ' ',
            'H:i:s',
            Str::contains($value, 'Z')
                ? '\Z'
                : ($this->offsetContainsColon($value) ? 'P' : 'O')
        );

        $date = \DateTime::createFromFormat($format, $value);

        if (preg_match('/[+-]\d{2}$/', $value)) {
            $value .= $this->offsetContainsColon($value) ? ':00' : '00';
        }

        return $date && $date->format($format) === $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'validation.datetime';
    }

    private function offsetContainsColon(string $timestamptz): bool
    {
        // Obtener el offset usando el simbolo + o -
        $offset = Str::contains($timestamptz, '+')
            ? Str::after($timestamptz, '+')
            : Str::after($timestamptz, '-');

        // Verificar si el offset contiene el caracter :
        return Str::contains($offset, ':');
    }
}

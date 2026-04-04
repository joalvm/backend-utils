<?php

namespace Joalvm\Utils\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Validates that the given value contains only alphabetic characters and spaces.
 */
class AlphaSpace implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!preg_match('/^[\p{L}\s]+$/u', to_str($value) ?? '')) {
            $fail(Lang::get('validation.alpha_space', ['attribute' => $attribute]));
        }
    }

    public static function rule(): static
    {
        return new static();
    }
}

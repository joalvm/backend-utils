<?php

namespace Joalvm\Utils\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;

/**
 * Validates that the given value contains only alphabetic characters and spaces.
 */
class AlphaSpaceRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (!preg_match('/^[a-zA-Z]+(?:\s[a-zA-Z]+)*$/', to_str($value) ?? '')) {
            $fail(Lang::get('validation.alpha_space', ['attribute' => $attribute]));
        }
    }
}

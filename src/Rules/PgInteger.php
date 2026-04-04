<?php

namespace Joalvm\Utils\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Validator;

/**
 * Regla de validación para enteros PostgreSQL (smallint, int, bigint).
 *
 * Permite validar si un valor es un entero válido según el tipo de dato PostgreSQL,
 * soportando opciones para enteros sin signo y no nulos.
 *
 * @psalm-type Bits = 2|4|8
 *
 * @implements \Illuminate\Contracts\Validation\ValidationRule
 * @implements \Illuminate\Contracts\Validation\ValidatorAwareRule
 */
class PgInteger implements ValidationRule, ValidatorAwareRule
{
    public const MIN_SMALL_INT = -32768;
    public const MAX_SMALL_INT = 32767;
    public const MIN_INT = -2147483648;
    public const MAX_INT = 2147483647;
    public const MIN_BIG_INT = -9223372036854775808;
    public const MAX_BIG_INT = 9223372036854775807;

    /**
     * @var null|Validator
     */
    protected $validator;

    /**
     * @param Bits $bits     Número de bytes del entero (2=smallint, 4=int, 8=bigint)
     * @param bool $unsigned Si es true, solo acepta valores positivos
     * @param bool $nonZero  Si es true, no acepta el valor cero
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        private readonly int $bits = 4,
        private readonly bool $unsigned = false,
        private readonly bool $nonZero = false
    ) {
        if (!in_array($this->bits, [2, 4, 8])) {
            throw new \InvalidArgumentException('The bit must be 2, 4 or 8');
        }
    }

    /**
     * Crea una regla para enteros positivos (sin signo).
     *
     * @param Bits $bits
     */
    public static function positive(int $bits = 4): static
    {
        return new static($bits, true);
    }

    /**
     * Crea una regla para identificadores (enteros positivos y no nulos).
     *
     * @param Bits $bits
     */
    public static function id(int $bits = 4): static
    {
        return new static($bits, true, true);
    }

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * @param \Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_null($value) and $this->validator->hasRule($attribute, 'nullable')) {
            return;
        }

        if (!is_int($value)) {
            $fail($attribute, 'validation.integer');
        }

        if ($value < $this->min() || $value > $this->max()) {
            $fail($attribute, 'validation.integer_between', [
                'min' => $this->min(),
                'max' => $this->max(),
            ]);
        }
    }

    private function min(): int
    {
        if ($this->unsigned) {
            return $this->nonZero ? 1 : 0;
        }

        return match ($this->bits) {
            2 => self::MIN_SMALL_INT,
            4 => self::MIN_INT,
            8 => self::MIN_BIG_INT,
        };
    }

    private function max(): int
    {
        return match ($this->bits) {
            2 => self::MAX_SMALL_INT,
            4 => self::MAX_INT,
            8 => self::MAX_BIG_INT,
        };
    }
}

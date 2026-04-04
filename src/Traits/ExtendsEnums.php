<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;

/**
 * Trait ExtendsEnums.
 *
 * Proporciona utilidades adicionales para enums respaldados (BackedEnum) en PHP,
 * incluyendo validación, obtención de valores/nombres y reglas de validación para Laravel.
 */
trait ExtendsEnums
{
    /**
     * Verifica si el valor dado corresponde a algún caso del enum.
     *
     * @param int|string $value Valor a verificar
     *
     * @return bool true si el valor existe en el enum, false en caso contrario
     */
    public static function has(mixed $value): bool
    {
        if (!is_string($value) and !is_int($value)) {
            return false;
        }

        return null !== self::tryFrom($value);
    }

    /**
     * Obtiene la instancia del enum correspondiente al valor dado.
     *
     * @param int|string $value valor a buscar
     *
     * @return null|static instancia del enum o null si no existe
     */
    public static function get(mixed $value): ?static
    {
        if (!is_string($value) and !is_int($value)) {
            return null;
        }

        return static::tryFrom($value);
    }

    /**
     * Devuelve un array con todos los valores de los casos del enum.
     *
     * @return array lista de valores (value para BackedEnum, name para casos puros)
     */
    public static function values(): array
    {
        return array_map(
            fn ($enum) => $enum instanceof \BackedEnum
                ? $enum->value
                : $enum->name,
            array_values(self::cases())
        );
    }

    /**
     * Devuelve un array con todos los nombres de los casos del enum.
     *
     * @return array lista de nombres de los casos
     */
    public static function names(): array
    {
        return array_map(fn ($enum) => $enum->name, array_values(self::cases()));
    }

    /**
     * Devuelve una regla de validación de Laravel para el enum.
     *
     * @return Enum regla de validación para usar en formularios Laravel
     */
    public static function rule(): Enum
    {
        return new Enum(static::class);
    }

    /**
     * Devuelve una regla de validación de Laravel que permite solo los valores del enum.
     *
     * @return In regla de validación para usar en formularios Laravel
     */
    public static function ruleIn(): In
    {
        return Rule::in(static::values());
    }
}

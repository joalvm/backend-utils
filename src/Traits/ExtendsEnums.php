<?php

namespace Joalvm\Utils\Traits;

trait ExtendsEnums
{
    /**
     * Verifica si el valor está definido en el enum.
     */
    public static function has(?string $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        return !is_null(self::tryFrom($value));
    }

    /**
     * Obtiene la instancia de un enum, si no existe devuelve null.
     */
    public static function get(mixed $value): ?static
    {
        return static::tryFrom(to_str($value));
    }

    /**
     * Obtiene un valor aleatoriamente.
     */
    public static function random(): static
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }

    public static function values(): array
    {
        return array_map(
            fn ($enum) => $enum instanceof \BackedEnum
                ? $enum->value
                : $enum->name,
            array_values(self::cases())
        );
    }

    public static function names(): array
    {
        return array_map(fn ($enum) => $enum->name, array_values(self::cases()));
    }
}

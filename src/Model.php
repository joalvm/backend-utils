<?php

namespace Joalvm\Utils;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Joalvm\Utils\Traits\ValidatesAttributes;

class Model extends EloquentModel
{
    use ValidatesAttributes;

    /**
     * {@inheritDoc}
     *
     * Añadimos la funcionalidad de devolver el valor tal cual si es null o string.
     * esto sirve para la validación, debido a que usa el string para hacer la busqueda.
     */
    protected function getEnumCaseFromValue($enumClass, $value)
    {
        if (is_subclass_of($enumClass, \BackedEnum::class)) {
            $enum = $enumClass::tryFrom($value);

            if (is_null($enum)) {
                return $value;
            }
        }

        if (is_subclass_of($enumClass, \UnitEnum::class)) {
            return $enumClass::from($value);
        }

        return constant($enumClass . '::' . $value);
    }

    /**
     * {@inheritDoc}
     *
     * Añadimos la funcionalidad en caso sea null o string lo devolvemos tal cual.
     */
    protected function getStorableEnumValue($value)
    {
        if (is_null($value) or is_string($value)) {
            return $value;
        }

        return $value instanceof \BackedEnum
                ? $value->value
                : $value->name;
    }
}

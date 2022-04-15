<?php

namespace Joalvm\Utils;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Model extends EloquentModel
{
    /**
     * Valida el modelo segun las reglas establecidas en el metodo rules.
     *
     * @return static
     */
    public function validate()
    {
        $validator = Validator::make($this->getAttributes(), $this->rules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }

    /**
     * Reglas de validación para los atributos del modelo.
     */
    public function rules(): array
    {
        return [];
    }
}

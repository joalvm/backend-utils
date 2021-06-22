<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait Validatable
{
    /**
     * Valida el modelo segun las reglas establecidas en el metodo rules.
     *
     * @return static
     */
    public function validate()
    {
        $rules = $this->rules() ?? [];

        $validator = Validator::make($this->getAttributes(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this;
    }
}

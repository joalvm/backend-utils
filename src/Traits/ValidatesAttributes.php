<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Joalvm\Utils\Exceptions\UnprocessableEntityException;

trait ValidatesAttributes
{
    /**
     * Define las reglas de validación para los atributos del modelo.
     *
     * @return array las reglas de validación
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Valida los atributos del modelo según las reglas definidas.
     *
     * @throws UnprocessableEntityException si la validación falla
     */
    public function validate(): static
    {
        $this->initValidation(
            Arr::except(
                $this->getAttributes(),
                ['created_at', 'modified_at', 'updated_at']
            ),
            $this->rules()
        );

        return $this;
    }

    /**
     * Inicializa la validación de los atributos del modelo.
     *
     * @param array $data  los datos a validar
     * @param array $rules las reglas de validación
     *
     * @throws UnprocessableEntityException si la validación falla
     */
    protected function initValidation(array $data, array $rules): void
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $this->emitValidateException($validator->errors()->toArray());
        }
    }

    protected function emitValidateException(array $errors): void
    {
        throw new UnprocessableEntityException();
    }
}

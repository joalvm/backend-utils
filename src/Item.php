<?php

namespace Joalvm\Utils;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use JsonSerializable;
use Stringable;

class Item implements Arrayable, ArrayAccess, Jsonable, JsonSerializable, Stringable
{
    /**
     * Todos los atributos establecidos en la instancia del Item.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Crea una nueva instancia Item.
     *
     * @param array|\stdClass $attributes
     */
    public function __construct($attributes = [])
    {
        $this->attributes = $this->normalizeAttributes($attributes);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Manejar llamadas dinámicas a la instancia Item para establecer atributos.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return $this
     */
    public function __call($method, $parameters)
    {
        $this->attributes[$method] = count($parameters) > 0 ? $parameters[0] : true;

        return $this;
    }

    /**
     * Recuperar dinámicamente el valor de un atributo.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Establecer dinámicamente el valor de un atributo.
     *
     * @param mixed $value
     */
    public function __set(string $key, $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Verifique dinámicamente si un atributo está establecido.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Remueve dinámicamente un atributo.
     *
     * @param string $key
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Obtener un atributo de la instancia Item.
     *
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (Arr::has($this->attributes, $key)) {
            return Arr::get($this->attributes, $key);
        }

        return value($default);
    }

    /**
     * Actualiza o agrega un atributo a la instancia Item.
     *
     * @param mixed $value
     */
    public function set(string $key, $value): self
    {
        return Arr::set($this->attributes, $key, $value);

        return $this;
    }

    public function has(string $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    public function isEmpty(): bool
    {
        return empty(array_keys($this->attributes));
    }

    /**
     * Obtiene los atributos de la instancia Item.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Convierte la instancia Item en un array asociativo.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convierta el objeto en un JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convierte la instancia Item en un JSON.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determina si un atributo existe.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Obtener el valor de un atributo determinado.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Establecer el valor en el offset dado.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        Arr::set($this->attributes, $offset, $value);
    }

    /**
     * Remueve el valor en el offset dado.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        Arr::forget($this->attributes, $offset);
    }

    private function normalizeAttributes($attributes): array
    {
        $values = [];

        foreach ($attributes as $key => $value) {
            if (
                is_array($value)
                or is_object($value)
                or $value instanceof Enumerable
                or $value instanceof ArrayAccess
            ) {
                $values[$key] = $this->normalizeAttributes($value);

                continue;
            }

            $values[$key] = $value;
        }

        return $values;
    }
}

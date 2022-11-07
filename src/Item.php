<?php

namespace Joalvm\Utils;

use ArrayAccess;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use JsonSerializable;
use stdClass;
use Stringable;

class Item implements Arrayable, ArrayAccess, Jsonable, JsonSerializable, Stringable, Countable
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
        $this->attributes = $attributes;
    }

    public function __toString()
    {
        return $this->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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

    public function count(): int
    {
        return count(array_keys($this->attributes));
    }

    /**
     * Estructurar la consulta.
     */
    public function schematize(callable $callback = null): self
    {
        /** @var array|stdClass */
        $origin = $this->attributes;

        $this->attributes = [];

        foreach ($origin as $key => $value) {
            $this->set($key, $value);

            if ($origin instanceof stdClass) {
                unset($origin->{$key});
            } else {
                unset($origin[$key]);
            }
        }

        $this->attributes = $this->cleanSchematizedValues($this->attributes);

        if (!is_null($callback)) {
            call_user_func($callback, $this);
        }

        return $this;
    }

    /**
     * Permite pasar una funcion para casteo custom.
     */
    public function cast(callable $fn, array $keys): void
    {
        foreach ($keys as $key) {
            if ($this->has($key)) {
                call_user_func($fn, $this->get($key));
            }
        }
    }

    /**
     * Castea todos las keys a valores enteros.
     *
     * @param string[] $keys
     */
    public function ints(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_int($this->get($key)));
        }
    }

    /**
     * Castea todos las keys a valores flotantes.
     *
     * @param string[] $keys
     */
    public function floats(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_float($this->get($key)));
        }
    }

    /**
     * Castea todos las keys a valores boleanos.
     *
     * @param string[] $keys
     */
    public function bools(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_bool($this->get($key)));
        }
    }

    /**
     * Castea todos las keys de un json a array asociativos.
     *
     * @param string[] $keys
     */
    public function jsons(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, json_decode($this->get($key), true));
        }
    }

    /**
     * Retorna la lista de keys de la instancia Item.
     */
    public function keys(): array
    {
        return array_keys($this->attributes);
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
        Arr::set($this->attributes, $key, $value);

        return $this;
    }

    public function has(string $key): bool
    {
        return Arr::has($this->attributes, $key);
    }

    public function isEmpty(): bool
    {
        return empty(array_keys((array) $this->attributes));
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
    public function toJson($options = 0)
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

    /**
     * Analiza todos los objetos asociativos en busca de nulls
     * en caso de hallar todos los valores null, convierte el array en null.
     *
     * @param array|\stdClass $data
     */
    private function cleanSchematizedValues($data)
    {
        return array_map(function ($val) {
            if (is_array($val) || $val instanceof \stdClass) {
                return (
                    count(
                        array_filter(
                            array_values(
                                $val = $this->cleanSchematizedValues($val)
                            ),
                            function ($item) {
                                return !is_null($item);
                            }
                        )
                    ) > 0
                ) ? $val : null;
            }

            return $val;
        }, $data);
    }
}

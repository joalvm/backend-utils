<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;

class Item implements Arrayable, \ArrayAccess, Jsonable, \JsonSerializable, \Stringable, \Countable
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
     * @param array|\ArrayAccess|\stdClass $attributes
     */
    public function __construct($attributes = [])
    {
        if (
            !is_array($attributes)
            and !($attributes instanceof \ArrayAccess)
            and !($attributes instanceof \stdClass)
        ) {
            $attributes = [];
        }

        if ($attributes instanceof \stdClass or $attributes instanceof \ArrayAccess) {
            $attributes = (array) $attributes;
        }

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
     * Verifica dinámicamente si un atributo existe.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    /**
     * Remueve dinámicamente un atributo.
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $this->offsetUnset($name);

        return true;
    }

    public static function make($attributes = []): self
    {
        return new static($attributes);
    }

    public function count(): int
    {
        return count(array_keys($this->attributes));
    }

    /**
     * Elimina un atributo de la instancia Item.
     *
     * @param array|float|int|string $keys
     */
    public function forget(mixed $keys): void
    {
        Arr::forget($this->attributes, $keys);
    }

    /**
     * Estructurar la consulta.
     */
    public function schematize(callable $fnCasts = null): self
    {
        /** @var array|\stdClass */
        $origin = $this->attributes;

        $this->attributes = [];

        foreach ($origin as $key => $value) {
            $this->set($key, $value);

            if ($origin instanceof \stdClass) {
                unset($origin->{$key});
            } else {
                unset($origin[$key]);
            }
        }

        $this->attributes = $this->cleanSchematizedValues($this->attributes);

        if (!is_null($fnCasts)) {
            // Ejecutar la funcion que castea los valores y pasar la misma clase como referencia.
            $newthis = &$this;
            call_user_func($fnCasts, $newthis);
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
                $this->set($key, call_user_func($fn, $this->get($key)));
            }
        }
    }

    /**
     * Castea todos las keys a valores enteros.
     *
     * @param string[] $keys
     */
    public function castIntValues(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_int($this->get($key)));
        }
    }

    /**
     * Alias de castIntValues.
     */
    public function intValues(array $keys): void
    {
        $this->castIntValues($keys);
    }

    /**
     * Castea todos las keys a valores flotantes.
     *
     * @param string[] $keys lista de keys a castear a float
     */
    public function castFloatValues(
        array $keys,
        int $precision = -1,
        int $mode = PHP_ROUND_HALF_UP
    ): void {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_float($this->get($key), $precision, $mode));
        }
    }

    /**
     * Alias de castFloatValues.
     */
    public function floatValues(array $keys, int $precision = -1, int $mode = PHP_ROUND_HALF_UP): void
    {
        $this->castFloatValues($keys, $precision, $mode);
    }

    /**
     * Castea todos las keys a valores boleanos.
     *
     * @param string[] $keys
     */
    public function castBoolValues(array $keys): void
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $this->set($key, to_bool($this->get($key)));
        }
    }

    /**
     * Alias de castBoolValues.
     */
    public function boolValues(array $keys): void
    {
        $this->castBoolValues($keys);
    }

    /**
     * Castea todos las keys de un json a array asociativos.
     *
     * @param string[] $keys
     */
    public function castJsonValues(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                continue;
            }

            $value = $this->get($key);

            if ('{}' === $value) {
                $this->set($key, new \stdClass());

                continue;
            }

            $this->set($key, json_decode($this->get($key), true));
        }
    }

    /**
     * Alias de castJsonValues.
     */
    public function jsonValues(array $keys): void
    {
        $this->castJsonValues($keys);
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
     * @param null|mixed $default
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
    public function jsonSerialize(): mixed
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
     */
    public function offsetGet(mixed $offset): mixed
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

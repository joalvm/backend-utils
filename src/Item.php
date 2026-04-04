<?php

namespace Joalvm\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;

/**
 * Representa un objeto dentro de una colección con funcionalidades adicionales
 * para manipulación de datos, casteo de tipos y serialización.
 *
 * Esta clase extiende Fluent para proporcionar métodos específicos de casteo
 * y manipulación de datos, manteniendo la compatibilidad con arrays y JSON.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @extends \Illuminate\Support\Fluent<TKey, TValue>
 *
 * @implements \Stringable
 * @implements \Countable
 */
class Item extends Fluent implements \Stringable, \Countable
{
    /**
     * Convierte la instancia a string usando representación JSON.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Crea una nueva instancia de Item.
     *
     * @param iterable<TKey, TValue> $attributes
     *
     * @return static
     */
    public static function make($attributes = []): self
    {
        return new static($attributes);
    }

    /**
     * Cuenta el número de atributos en la instancia.
     *
     * @return int
     */
    public function count(): int
    {
        return count(array_keys($this->attributes));
    }

    /**
     * Elimina uno o más atributos de la instancia Item.
     *
     * @param array<TKey>|TKey ...$keys Las claves a eliminar
     *
     * @return void
     */
    public function forget(...$keys): void
    {
        Arr::forget($this->attributes, $keys);
    }

    /**
     * Estructura y reorganiza los datos aplicando transformaciones opcionales.
     *
     * Este método reconstruye los atributos y opcionalmente aplica una función
     * de casteo personalizada para transformar los valores.
     *
     * @param callable|null $fnCasts Función opcional para castear valores
     *
     * @return self
     */
    public function schematize(?callable $fnCasts = null): self
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
     * Aplica una función de casteo personalizada a las claves especificadas.
     *
     * @param callable      $fn   Función que recibe el valor y retorna el valor transformado
     * @param array<string> $keys Lista de claves a procesar
     *
     * @return void
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
     * Castea los valores de las claves especificadas a enteros.
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
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
     * Alias para castIntValues().
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
     */
    public function intValues(array $keys): void
    {
        $this->castIntValues($keys);
    }

    /**
     * Castea los valores de las claves especificadas a números flotantes.
     *
     * @param array<string> $keys      Lista de claves a castear
     * @param int           $precision Número de decimales (-1 para no redondear)
     * @param int           $mode      Modo de redondeo (PHP_ROUND_*)
     *
     * @return void
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
     * Alias para castFloatValues().
     *
     * @param array<string> $keys      Lista de claves a castear
     * @param int           $precision Número de decimales
     * @param int           $mode      Modo de redondeo
     *
     * @return void
     */
    public function floatValues(array $keys, int $precision = -1, int $mode = PHP_ROUND_HALF_UP): void
    {
        $this->castFloatValues($keys, $precision, $mode);
    }

    /**
     * Castea los valores de las claves especificadas a booleanos.
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
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
     * Alias para castBoolValues().
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
     */
    public function boolValues(array $keys): void
    {
        $this->castBoolValues($keys);
    }

    /**
     * Castea los valores de las claves especificadas desde JSON a arrays/objetos.
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
     */
    public function castJsonValues(array $keys): void
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
     * Alias para castJsonValues().
     *
     * @param array<string> $keys Lista de claves a castear
     *
     * @return void
     */
    public function jsonValues(array $keys): void
    {
        $this->castJsonValues($keys);
    }

    /**
     * Retorna todas las claves de la instancia usando notación punto.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        return array_keys(Arr::dot($this->attributes));
    }

    /**
     * Verifica si la instancia está vacía (sin atributos).
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty(array_keys((array) $this->attributes));
    }

    /**
     * Analiza recursivamente los datos para limpiar valores nulos anidados.
     *
     * Si todos los valores de un array/objeto son null, convierte todo el
     * contenedor en null.
     *
     * @param array<mixed>|\stdClass $data Datos a limpiar
     *
     * @return array<mixed>|\stdClass|null
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

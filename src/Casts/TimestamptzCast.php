<?php

namespace Joalvm\Utils\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TimestamptzCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof \DateTimeInterface or is_null($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        return Carbon::parse($this->normalizeStrDateTime($value))->toImmutable();
    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (is_null($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value)->format(\DateTimeInterface::ATOM);
        }

        return Carbon::parse($this->normalizeStrDateTime($value))->format(\DateTimeInterface::ATOM);
    }

    private function normalizeStrDateTime(string $value): string
    {
        $value = str_replace('T', ' ', $value);

        // Si la zona horaria viene con dos puntos, los retira
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/', $value)) {
            return substr($value, 0, -3) . substr($value, -2);
        }

        // Si es una fecha y hora, añade la zona horaria
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
            return $value . '+0000';
        }

        // Si es solo una fecha, añade la hora y la zona horaria
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . ' 00:00:00+0000';
        }

        // Si termina con una Z, reemplaza la Z con +0000
        if ('Z' === substr($value, -1)) {
            return substr($value, 0, -1) . '+0000';
        }

        // Si no se cumple ninguno de los casos anteriores, devuelve el valor tal cual
        return $value;
    }
}

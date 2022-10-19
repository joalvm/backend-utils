<?php

namespace Joalvm\Utils\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Builder;

class Sort
{
    /**
     * Dependecia de la clase Fields que permite obtner
     * la lista de columnas que estan permitidas.
     *
     * @var Fields
     */
    protected $fields;

    /**
     * Los short=>order obtenidos.
     *
     * @var array
     */
    protected $values = [];

    public function __construct(Fields $fields)
    {
        $this->fields = $fields;

        $this->values = $this->init(Request::query('sort'));
    }

    public function getValues()
    {
        return $this->filterValues();
    }

    public function run(Builder &$builder)
    {
        foreach ($this->filterValues() as $order) {
            $builder->orderBy($order->column, $order->direction);
        }
    }

    protected function filterValues(): array
    {
        $values = [];

        foreach ($this->values as $field => $order) {
            if (!$this->fields->exists($field)) {
                continue;
            }

            $nfield = $this->fields->getDefaults()[$field];

            $isObject = is_object($nfield) or is_callable($nfield);

            array_push($values, (object) [
                'column' => $isObject ? DB::raw("\"{$field}\"") : $nfield,
                'direction' => $order,
                'is_object' => $isObject,
            ]);
        }

        return $values;
    }

    /**
     * Inicia la captura de todos los ordenamientos.
     *
     * @param [type] $params
     */
    protected function init($params)
    {
        $values = [];

        if (!is_array_assoc($params)) {
            return $values;
        }

        foreach ($params as $field => $order) {
            if (is_numeric($field)) {
                if (!is_string($order)) {
                    continue;
                }

                $parts = explode(' ', trim($order));

                $values[$parts[0]] = $this->checkOrder($parts[1] ?? '');
            } elseif (is_string($field) and is_string($order)) {
                if (!is_string($order)) {
                    continue;
                }

                $values[$field] = $this->checkOrder(trim($order));
            }
        }

        return $values;
    }

    private function checkOrder($order)
    {
        return in_array($this->toUpper($order), ['ASC', 'DESC'])
            ? ($this->toUpper($order))
            : 'DESC';
    }

    private function toUpper(string $text): string
    {
        return mb_strtoupper($text, 'utf-8');
    }
}

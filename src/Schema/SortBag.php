<?php

namespace Joalvm\Utils\Schema;

use Illuminate\Support\Arr;
use Joalvm\Utils\Cast;
use Symfony\Component\HttpFoundation\ParameterBag;

class SortBag extends ParameterBag
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    public const DIRECTION = [self::ASC, self::DESC];

    public function __construct()
    {
        parent::__construct($this->getParameters());
    }

    /**
     * Obtiene la lista de columnas a ordenar.
     *
     * @return array
     */
    public function getColumns(Schema $schema)
    {
        $values = [];

        foreach ($this->parameters as $columnAs => $direction) {
            $column = $schema->getColumn($columnAs);

            if (!$column or !$column->isSortable()) {
                continue;
            }

            array_push($values, [
                'column' => $column,
                'direction' => $direction,
            ]);
        }

        return $values;
    }

    private function getParameters(): array
    {
        $parameters = Arr::get($_GET, 'sort', []);
        $values = [];

        if (is_string($parameters)) {
            $parameters = Cast::toListStr($parameters);
        }

        foreach ($parameters as $key => $orientation) {
            if (!is_string($orientation)) {
                continue;
            }

            $order = $this->split($key, $orientation);

            if (is_null($order)) {
                continue;
            }

            $values[$order[0]] = $order[1];
        }

        return $values;
    }

    private function split($key = null, $orientation = self::ASC): ?array
    {
        if (is_int($key)) {
            preg_match('/^(.+)\s+(asc|desc)$/i', $orientation, $matches);

            if (!$matches) {
                return null;
            }

            $key = trim($matches[1]);
            $orientation = mb_strtolower(
                trim(strval($matches[2] ?? self::ASC)),
                'UTF-8'
            );
        }

        if (!in_array($orientation, self::DIRECTION)) {
            $orientation = self::ASC;
        }

        return [
            filter_var($key, FILTER_SANITIZE_STRING),
            filter_var($orientation, FILTER_SANITIZE_STRING),
        ];
    }
}

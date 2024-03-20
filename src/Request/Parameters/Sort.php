<?php

namespace Joalvm\Utils\Request\Parameters;

use Illuminate\Support\Arr;

class Sort
{
    public const PARAMETER_NAME = 'sort';

    public const ORDER_DESC = 'DESC';

    public const ORDER_ASC = 'ASC';

    public const DEFAULT_ORDER = self::ORDER_ASC;

    /**
     * Los short=>order obtenidos.
     *
     * @var array
     */
    protected $values = [];

    public function __construct(array $sorts)
    {
        $this->values = $this->normalizeParameter(
            Arr::get($sorts, self::PARAMETER_NAME, [])
        );
    }

    public function getValues(Schema $schema): array
    {
        $values = [];
        $items = $schema->getColumnsOrAlias(array_keys($this->values));

        foreach ($items as $alias => $item) {
            $values[] = [
                'column' => $item,
                'order' => $this->values[$alias],
            ];
        }

        return $values;
    }

    /**
     * Inicia la captura de todos los ordenamientos.
     *
     * @var array|string
     *
     * @param mixed $sorts
     */
    protected function normalizeParameter($sorts): array
    {
        $values = [];

        if (is_string($sorts)) {
            $sorts = to_list($sorts);
        }

        foreach ($sorts as $item => $order) {
            $item = $item;
            $mode = to_str($order);

            // Si el item es numerico entonces se asume que es un array list.
            if (is_numeric($item)) {
                $parts = to_list($mode, true, ' ');

                $item = $parts[0];
                $mode = $this->orderMode($parts[1] ?? '');
            }

            if (is_string($item)) {
                $mode = $this->orderMode($mode);
            }

            $values[$item] = $mode;
        }

        return $values;
    }

    private function orderMode($mode): string
    {
        $mode = mb_strtoupper($mode, 'utf-8');

        if (in_array($mode, [self::ORDER_DESC, self::ORDER_DESC])) {
            return $mode;
        }

        return self::DEFAULT_ORDER;
    }
}

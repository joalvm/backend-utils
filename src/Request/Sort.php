<?php

namespace Joalvm\Utils\Request;

use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

class Sort extends ParameterBag
{
    public const PARAMETER_SORT = 'sort';

    public const ORDER_DESC = 'DESC';

    public const ORDER_ASC = 'ASC';

    public const DEFAULT_ORDER = self::ORDER_ASC;

    /**
     * Los short=>order obtenidos.
     *
     * @var array
     */
    protected $values = [];

    public function __construct()
    {
        parent::__construct(
            $this->normalizeParameter(Request::query(self::PARAMETER_SORT, []))
        );
    }

    public function getValues(Schema $schema): array
    {
        $values = [];
        $items = $schema->getColumnsOrAlias(array_keys($this->parameters));

        foreach ($items as $alias => $item) {
            $values[] = [
                'column' => $item,
                'order' => $this->parameters[$alias],
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

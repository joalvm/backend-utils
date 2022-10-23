<?php

namespace Joalvm\Utils\Request;

use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Builder;
use Symfony\Component\HttpFoundation\ParameterBag;

class Sort extends ParameterBag
{
    public const PARAMETER_SORT = 'sort';

    public const ORDER_DESC = 'DESC';

    public const ORDER_ASC = 'ASC';

    public const DEFAULT_ORDER = self::ORDER_ASC;

    /**
     * Dependecia de la clase Fields que permite obtner
     * la lista de columnas que estan permitidas.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Los short=>order obtenidos.
     *
     * @var array
     */
    protected $values = [];

    /**
     * Grammar del builder.
     *
     * @var Grammar
     */
    private $grammar;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;

        parent::__construct(
            $this->normalizeParameter(Request::query(self::PARAMETER_SORT, []))
        );
    }

    public function loadSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function getValues()
    {
        return $this->filterInSchema();
    }

    public function run(Builder &$builder)
    {
        foreach ($this->filterInSchema() as $order) {
            $builder->orderBy($order['column'], $order['mode']);
        }
    }

    protected function filterInSchema(): array
    {
        $values = [];

        if (!$this->schema) {
            return [];
        }

        foreach ($this->parameters as $item => $order) {
            $sitem = $this->schema->getColumnOrAlias($item);

            if (!$sitem) {
                continue;
            }

            array_push($values, ['column' => $sitem, 'mode' => $order]);
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

        foreach ($sorts as $schemaItem => $order) {
            $item = $schemaItem;
            $mode = to_str($order);

            if (is_numeric($schemaItem)) {
                $parts = explode(' ', $mode);

                $item = $parts[0];
                $mode = $this->orderMode($parts[1] ?? '');
            }

            if (is_string($schemaItem)) {
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

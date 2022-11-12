<?php

namespace Joalvm\Utils\Request;

use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Builder;

class Search
{
    public const PARAMETER_CONTAINS = 'contains';
    public const PARAMETER_STARTS_WITH = 'starts_with';
    public const PARAMETER_ENDS_WITH = 'ends_with';

    public const ALLOWED_PARAMETERS = [
        self::PARAMETER_CONTAINS,
        self::PARAMETER_ENDS_WITH,
        self::PARAMETER_STARTS_WITH,
    ];

    public const KEY_ITEMS = 'items';
    public const KEY_TEXT = 'text';

    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $values = [];

    /**
     * @var Grammar
     */
    private $grammar;

    public function __construct(Grammar $grammar)
    {
        $this->grammar = $grammar;

        $this->catchParameters();
    }

    public function loadSchema(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function run(Builder &$builder)
    {
        if (!$this->values) {
            return false;
        }

        foreach ($this->values as $value) {
            $builder->where(function (Builder $query) use ($value) {
                foreach ($value[self::KEY_ITEMS] as $item) {
                    if (!$item = $this->schema->getColumnableItem($item)) {
                        continue;
                    }

                    $text = $this->grammar->quoteString(
                        $this->wrap($value['type'], $value[self::KEY_TEXT])
                    );
                    $column = "({$item})::text";
                    $operator = 'ilike';

                    // No todas las db tienen la sentencia 'ilike'
                    if (!in_array('ilike', $this->grammar->getOperators())) {
                        $text = $this->grammar->quoteString($this->wrap(
                            $value['type'],
                            mb_strtolower($value[self::KEY_TEXT], 'UTF-8')
                        ));
                        $column = "LOWER({$column})";
                        $operator = 'like';
                    }

                    $query->orWhereRaw(
                        sprintf('%s %s %s', $column, $operator, $text)
                    );
                }
            });
        }
    }

    private function catchParameters(): void
    {
        foreach (self::ALLOWED_PARAMETERS as $parameter) {
            list($items, $text) = $this->getQuery($parameter);

            $text = sanitize_str($text);

            if (!$text or !$items) {
                continue;
            }

            array_push($this->values, [
                'type' => $parameter,
                self::KEY_ITEMS => $items,
                self::KEY_TEXT => $text,
            ]);
        }
    }

    private function wrap(string $filter, $text)
    {
        switch ($filter) {
            case self::PARAMETER_CONTAINS:
                return "%{$text}%";

            case self::PARAMETER_ENDS_WITH:
                return "%{$text}";

            case self::PARAMETER_STARTS_WITH:
                return "{$text}%";
        }
    }

    private function getQuery(string $parameter)
    {
        $data = Request::query($parameter);

        if (!is_array_assoc($data)) {
            return [[], ''];
        }

        if (
            !array_key_exists(self::KEY_ITEMS, $data)
            or !array_key_exists(self::KEY_TEXT, $data)
        ) {
            return [[], ''];
        }

        return [to_list($data[self::KEY_ITEMS]), to_str($data[self::KEY_TEXT])];
    }
}

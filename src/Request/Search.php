<?php

namespace Joalvm\Utils\Request;

use Illuminate\Support\Facades\Request;

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

    private $values = [];

    public function __construct()
    {
        foreach (self::ALLOWED_PARAMETERS as $parameter) {
            list($items, $text) = $this->getQuery($parameter);

            $text = sanitize_str($text ?? '');

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

    public function getValues(Schema $schema)
    {
        $values = [];

        foreach ($this->values as $value) {
            $items = $schema->getColumnsOrValues($value[self::KEY_ITEMS]);

            foreach ($items as $item) {
                $values[] = [
                    'column' => $item,
                    'text' => $this->wrap($value['type'], $value[self::KEY_TEXT]),
                ];
            }
        }

        return $values;
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

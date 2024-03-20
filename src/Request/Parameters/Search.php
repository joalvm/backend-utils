<?php

namespace Joalvm\Utils\Request\Parameters;

class Search
{
    public const PARAMETER_NAME = 'contains';

    public const KEY_ITEMS_NAME = 'items';
    public const KEY_TEXT_NAME = 'text';

    private $values = [];

    public function __construct(mixed $contains)
    {
        list($items, $text) = $this->getQuery($contains);

        $text = sanitize_str($text ?? '');

        if (empty($items)) {
            $items = [];
        }

        $this->values = [
            self::KEY_ITEMS_NAME => $items,
            self::KEY_TEXT_NAME => $text,
        ];
    }

    public function getValues(Schema $schema)
    {
        $values = [];

        dd($this->values);

        foreach ($this->values as $value) {
            $items = $schema->getColumnsOrValues($value[self::KEY_ITEMS_NAME]);

            foreach ($items as $item) {
                $values[] = [
                    'column' => $item,
                    'text' => sprintf('%%s%', $value[self::KEY_TEXT_NAME]),
                ];
            }
        }

        return $values;
    }

    private function getQuery(mixed $data): array
    {
        if (!is_array_assoc($data)) {
            return [[], ''];
        }

        if (
            !array_key_exists(self::KEY_ITEMS_NAME, $data)
            or !array_key_exists(self::KEY_TEXT_NAME, $data)
        ) {
            return [[], ''];
        }

        return [to_list($data[self::KEY_ITEMS_NAME]), to_str($data[self::KEY_TEXT_NAME])];
    }
}

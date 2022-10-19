<?php

namespace Joalvm\Utils\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Builder;
use stdClass;

class Search
{
    /**
     * @var Fields
     */
    private $fields;

    private $values;

    public function __construct(Fields $fields)
    {
        $this->fields = $fields;

        $this->init();
    }

    public function getValues()
    {
        return $this->values;
    }

    public function init()
    {
        $this->startsWith();
        $this->endsWith();
        $this->contains();
    }

    public function run(Builder &$builder)
    {
        if (!$this->values) {
            return false;
        }

        foreach ($this->values as $value) {
            $builder->where(function ($query) use ($value) {
                foreach ($value->fields as $field) {
                    if (!$this->fields->exists($field)) {
                        continue;
                    }

                    $field = $this->sanatizeField($field);

                    $query->orWhere(
                        DB::raw("LOWER({$field}::text)"),
                        'like',
                        DB::raw("LOWER('{$value->text}')")
                    );
                }
            });
        }
    }

    public function startsWith()
    {
        $data = new stdClass();

        list($fields, $text) = $this->getQuery('starts_with');

        if (empty($text)) {
            return false;
        }

        $data->text = "{$text}%";
        $data->fields = $fields;

        $this->values[] = $data;
    }

    public function endsWith()
    {
        $data = new stdClass();

        list($fields, $text) = $this->getQuery('ends_with');

        if (empty($text)) {
            return false;
        }

        $data->text = "%{$text}";
        $data->fields = $fields;

        $this->values[] = $data;
    }

    public function contains()
    {
        $data = new stdClass();

        list($fields, $text) = $this->getQuery('contains');

        if (empty($text)) {
            return false;
        }

        $data->text = "%{$text}%";
        $data->fields = $fields;

        $this->values[] = $data;
    }

    private function getQuery(string $getParam)
    {
        $data = Request::query($getParam);
        $fields = [];
        $text = null;

        if (!is_array($data) and !is_array_assoc($data)) {
            return [[], null];
        }
        $fields = to_list($data['fields'] ?? []);
        $text = $data['text'] ?? null;

        if (trim($text)) {
            $text = str_replace(["'"], ["''"], trim($text));
        }

        return [$fields, $text];
    }

    private function sanatizeField(string $field): string
    {
        return str_replace(
            '""',
            '"',
            '"' . str_replace('.', '"."', $field) . '"'
        );
    }
}

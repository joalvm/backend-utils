<?php

namespace Joalvm\Utils\Request;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Builder;
use Symfony\Component\HttpFoundation\ParameterBag;

class Schema extends ParameterBag
{
    public const PARAMETER_SCHEMA = 'schema';

    protected $items = [];

    protected $filterable = true;

    public function __construct()
    {
        parent::__construct($this->catchParameters());
    }

    public function catchParameters(): array
    {
        $parameters = Arr::get($_GET, self::PARAMETER_SCHEMA, []);

        if (is_string($parameters)) {
            $parameters = to_list($parameters);
        }

        return array_map('sanitize_str', to_list($parameters));
    }

    public function setFilterable(bool $filterable = true): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function loadItems(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function getValues()
    {
        return $this->parameters;
    }

    public function exists(string $field): bool
    {
        return array_key_exists($field, $this->items);
    }

    /**
     * Obtiene un item del schema.
     *
     * @return null|mixed
     */
    public function getItem(string $item)
    {
        if ($this->exists($item)) {
            return $this->items[$item];
        }

        return null;
    }

    public function getColumnOrAlias(string $alias)
    {
        if (!$this->exists($alias)) {
            return null;
        }

        $item = $this->getItem($alias);

        if (is_object($item) or is_callable($item)) {
            return $alias;
        }

        if (is_string($item) and !$this->isColumnable($item)) {
            return $alias;
        }

        return $item;
    }

    public function getColumnableItem(string $alias): ?string
    {
        if (!$this->exists($alias)) {
            return null;
        }

        $item = $this->getItem($alias);

        if (is_string($item) and $this->isColumnable($item)) {
            return $item;
        }

        return null;
    }

    public function isColumnable(string $column): bool
    {
        return preg_match('/^[a-z](([a-z0-9_]+)?\.{1})?[a-z0-9_]+$/i', $column);
    }

    public function run(Builder &$builder): void
    {
        if ($this->filterable) {
            $columns = $this->getFilteredColumns();

            if (empty($columns)) {
                $columns = $this->items;
            }
        }

        foreach ($columns as $key => $value) {
            if (is_string($value) or $value instanceof Expression) {
                if (Builder::isColumnAlias($value)) {
                    $builder->addSelect(sprintf('%s as %s', $value, $key));
                } else {
                    $builder->selectRaw(
                        sprintf('%s as "%s"', $value, str_replace('"', '', $key))
                    );
                }

                continue;
            }

            $builder->selectSub($value, DB::raw(sprintf('"%s"', $key)));
        }
    }

    protected function getFilteredColumns()
    {
        $matches = $this->generateMatches();

        return array_filter(
            $this->items,
            function ($key) use ($matches) {
                foreach ($matches as $match) {
                    if (preg_match($match, $key)) {
                        return true;
                    }
                }

                return false;
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Gerar un array de expresiones regulares para field, la expresion
     * regular permite mantener la estructura separada por comas.
     */
    protected function generateMatches(): array
    {
        return array_map(
            function ($parameter) {
                return sprintf(
                    '/^%s/i',
                    str_replace('*', '(?:[a-z._]+)', $parameter)
                );
            },
            $this->parameters
        );
    }
}

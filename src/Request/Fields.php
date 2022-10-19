<?php

namespace Joalvm\Utils\Request;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Builder;
use Symfony\Component\HttpFoundation\ParameterBag;

class Fields extends ParameterBag
{
    protected $queryColumns = [];

    protected $filterable = true;

    public function __construct()
    {
        parent::__construct($this->catchParameters());
    }

    public function catchParameters(): array
    {
        $parameters = Arr::get($_GET, 'fields', []);

        if (is_string($parameters)) {
            $parameters = to_list($parameters);
        }

        return array_map(
            function ($parameter) {
                return filter_var($parameter, FILTER_SANITIZE_STRING);
            },
            array_filter(
                array_map('strval', array_values($parameters)),
                function ($parameter) {
                    return strlen(trim($parameter)) > 0;
                }
            ),
        );
    }

    public function setFilterable(bool $filterable = true): self
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function setQueryColumns(array $queryColumns): self
    {
        $this->queryColumns = $queryColumns;

        return $this;
    }

    public function getValues()
    {
        return $this->parameters;
    }

    public function exists(string $field): bool
    {
        return array_key_exists($field, $this->queryColumns);
    }

    public function getDefaults()
    {
        return $this->queryColumns;
    }

    public function run(Builder &$builder): void
    {
        if ($this->filterable) {
            $columns = $this->getFilteredColumns();

            if (empty($columns)) {
                $columns = $this->queryColumns;
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
            $this->queryColumns,
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

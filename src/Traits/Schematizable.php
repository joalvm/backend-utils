<?php

namespace Joalvm\Utils\Traits;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use UnexpectedValueException;

trait Schematizable
{
    private $columnExp = '/^(([a-zA-Z])(\\w+)?\\.)?([a-zA-Z]\\w+|_)$/i';

    private $schema = [];
    private $matches = [];
    private $fields = [];

    private $filterColumns = true;

    /**
     * @return static
     */
    public function schema(array $schema)
    {
        if (empty($this->from)) {
            throw new UnexpectedValueException('El esquema necesita que definas primero el from');
        }

        $this->schema = $this->resolveSchema($schema);

        foreach ($this->getSchema() as $alias => $field) {
            if (is_callable($field)) {
                $this->selectSub($field, new Expression($alias));
            } elseif (!preg_match($this->columnExp, $field)) {
                $this->selectRaw(
                    sprintf('%s as %s', $field, $alias),
                );
            } else {
                $this->addSelect(sprintf('%s as %s', $field, $alias));
            }
        }

        return $this;
    }

    /**
     * Desabilita el filtrado del esquema.
     *
     * @return static
     */
    public function disableFilterColumns()
    {
        $this->filterColumns = false;

        return $this;
    }

    protected function boot(): void
    {
        $this->catchHttpGetParameterFields();
        $this->generateMatches();
    }

    private function setPreffix(?string $preffix, string $value): string
    {
        return (!empty($preffix) ? "{$preffix}." : '') . $value;
    }

    private function getSchema(): array
    {
        $items = [];

        if (!$this->filterColumns) {
            return $this->schema;
        }

        foreach ($this->schema as $alias => $field) {
            if ($this->isFiltrable($alias)) {
                $items[$alias] = $field;
            }
        }

        return empty($items) ? $this->schema : $items;
    }

    private function isFiltrable(string $alias): bool
    {
        if (0 === count($this->matches)) {
            return true;
        }

        foreach ($this->matches as $regex) {
            if (preg_match($regex, $alias)) {
                return true;
            }
        }

        return false;
    }

    private function catchHttpGetParameterFields(
        ?array $values = null,
        ?string $preffix = null
    ) {
        $fields = $values ?? to_array(Request::query('fields'));
        $preffix = $preffix ?? '';

        foreach ($fields as $httpParam) {
            if (is_string($httpParam)) {
                array_push(
                    $this->fields,
                    $this->setPreffix($preffix, $httpParam)
                );
            } elseif (is_array($httpParam)) {
                $key = key($httpParam);
                $this->fields = array_merge(
                    $this->fields,
                    $this->init(
                        $httpParam[$key],
                        $this->setPreffix($preffix, $key)
                    )
                );
            }
        }
    }

    /**
     * Gerar un array de expresiones regulares para field, la expresion
     * regular permite mantener la estructura separada por comas.
     */
    private function generateMatches(): void
    {
        $this->matches = array_map(
            function ($key) {
                return '/^' . str_replace('*', '(?:[a-z._]+)', $key) . '/i';
            },
            $this->fields
        );
    }

    private static function isColumnAlias($ColumnAlias): bool
    {
        return preg_match(
            '/^(([a-zA-Z])(\\w+)?\\.)?([a-zA-Z]\\w+|_)$/i',
            $ColumnAlias
        );
    }

    private function resolveSchema(array $fields, string $preffix = '', string $aliasTable = '')
    {
        $nfields = [];

        if (empty($aliasTable)) {
            $aliasTable = preg_split('/\\sas\\s/i', $this->from)[1];
        }

        foreach ($fields as $aliasColumn => $field) {
            $params = [
                'field' => $field,
                'preffix' => $preffix,
                'aliasTable' => $aliasTable,
                'aliasColumn' => $aliasColumn,
            ];

            if (is_string($field)) {
                $pfield = explode('.', $field);
                if (2 === count($pfield)) {
                    $params['aliasTable'] = $pfield[0];
                    $params['field'] = $pfield[1];
                }
            }

            if (is_numeric($aliasColumn)) {
                $nfields = array_merge(
                    $nfields,
                    $this->resolveField($params)
                );
            } elseif (is_string($aliasColumn)) {
                if (is_array($field)) {
                    $parts = explode(':', $aliasColumn);
                    $ast = '';

                    if (2 == count($parts)) {
                        $parts = array_map('trim', $parts);
                        if (false !== strpos($parts[1], '.*')) {
                            $parts[1] = str_replace('.*', '', $parts[0]);
                            $ast = '.*';
                        }
                    }

                    $nfields = array_merge(
                        $nfields,
                        $this->resolveSchema(
                            $field,
                            $this->setPreffix($preffix, $parts[0] . $ast),
                            2 == count($parts) ? $parts[1] : $aliasTable
                        )
                    );
                } else {
                    $nfields = array_merge(
                        $nfields,
                        $this->resolveField($params)
                    );
                }
            }
        }

        return $nfields;
    }

    private function resolveField(array $params)
    {
        $column = $params['field'];

        if (is_string($params['field'])) {
            $column = trim($params['field']);
        }

        if ($column instanceof Expression or is_callable($column)) {
            $alias = $this->setPreffix($params['preffix'], $params['aliasColumn']);

            return [$alias => $column];
        }

        if (is_string($column)) {
            if (preg_match($this->columnExp, $column)) {
                return [
                    $this->setPreffix(
                        $params['preffix'],
                        !is_numeric($params['aliasColumn']) ? $params['aliasColumn'] : $column
                    ) => $this->setPreffix($params['aliasTable'], $column),
                ];
            }

            $alias = $this->setPreffix($params['preffix'], $params['aliasColumn']);

            return [
                $alias => DB::raw("({$column})"),
            ];
        }

        return [];
    }
}

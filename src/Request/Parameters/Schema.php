<?php

namespace Joalvm\Utils\Request\Parameters;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Schema
{
    public const PARAMETER__NAME = 'schema';

    /**
     * Prefijo de la tabla principal que se encuntra definido en el from.
     */
    protected string $fromAs = '';

    /**
     * Valores del schema.
     */
    protected array $values = [];

    /**
     * Items del schema.
     *
     * @var string[]
     */
    protected array $items = [];

    /**
     * Matches del schema.
     *
     * @var string[]
     */
    protected array $matches = [];

    /**
     * Claves del schema.
     *
     * @var string[]
     */
    protected array $keys = [];

    /**
     * The database query grammar instance.
     */
    private Grammar $grammar;

    public function __construct(mixed $schema, Grammar $grammar)
    {
        $this->handleSchema(Arr::get($schema, self::PARAMETER__NAME, []));
        $this->generateMatches();

        $this->grammar = $grammar;
    }

    public function setFromAs(string $alias): self
    {
        $this->fromAs = $alias;

        $this->items = $this->generateSchema($this->items, $alias);

        return $this;
    }

    public function getFromAs(): string
    {
        return $this->fromAs;
    }

    public function setItems(array $items): self
    {
        $this->items = $this->generateSchema($items, $this->fromAs);

        return $this;
    }

    public function getValues()
    {
        $columns = [];

        foreach ($this->filterItemKeys() as $as) {
            $column = $this->items[$as];

            if ($this->isQueryable($column) or $column instanceof Expression) {
                $columns[] = [$column, $as];

                continue;
            }

            $columns[] = sprintf('%s as "%s"', $this->grammar->wrapTable($column), $as);
        }

        return $columns;
    }

    public function filterItemKeys()
    {
        $keys = [];

        // Si no se ha filtrado el esqueema, se retornan todas las claves.
        if (!$this->matches) {
            return $this->keys;
        }

        foreach ($this->matches as $match) {
            $keys = array_merge($keys, preg_grep($match, $this->keys));
        }

        return $keys;
    }

    public function getKeys(): array
    {
        return $this->keys;
    }

    public function exists(string $key): bool
    {
        return in_array($key, $this->keys);
    }

    /**
     * Obtiene un item del schema.
     *
     * @return null|mixed
     */
    public function getItem(string $item)
    {
        if ($this->exists($item)) {
            return Arr::get($this->items, $item);
        }

        return null;
    }

    public function getColumns(array $keys): array
    {
        $columns = [];

        foreach ($keys as $key) {
            if ($this->isACorrectColumnName($item = $this->getItem($key))) {
                $columns[] = $item;
            }
        }

        return $columns;
    }

    public function getColumnsOrValues(array $keys): array
    {
        $columns = [];

        foreach ($keys as $key) {
            $item = $this->getItem($key);

            if ($this->isACorrectColumnName($item)) {
                $columns[$key] = $item;

                continue;
            }

            if ($item) {
                $columns[$key] = $item;
            }
        }

        return $columns;
    }

    /**
     * Obtiene la columna o el alias de la columna en caso de que sea una expresion.
     *
     * @param array<string,Expression|string> $keys
     */
    public function getColumnsOrAlias(array $keys): array
    {
        $columns = [];
        $filtered = $this->filterItemKeys();

        foreach ($keys as $key) {
            if ($this->isACorrectColumnName($item = $this->getItem($key))) {
                $columns[$key] = $item;

                continue;
            }

            // Si no es nombre de columna correcta, debemos buscar entre los alias
            // que esten el eschema filtrado, de lo contrario dara error.
            if (in_array($key, $filtered)) {
                $columns[$key] = DB::raw(sprintf('"%s"', $key));
            }
        }

        return $columns;
    }

    private function handleSchema(mixed $schema): void
    {
        if (is_string($schema)) {
            $this->values = to_list($schema);

            return;
        }

        if (is_array($schema)) {
            $this->values = array_values($schema);
        }
    }

    private function generateSchema(array $items, string $alias)
    {
        if (!$items or !$alias) {
            return $items;
        }

        $newItems = $this->schematize($items, '', $alias);
        $this->keys = array_keys($newItems);

        return $newItems;
    }

    /**
     * Gerar un array de expresiones regulares para field, la expresion
     * regular permite mantener la estructura separada por comas.
     */
    private function generateMatches(): void
    {
        foreach ($this->values as $value) {
            $this->matches[] = sprintf(
                '/^%s(\..+)?$/i',
                // Se eliminan los puntos al final de la cadena.
                preg_replace(
                    '/\.+$/',
                    '',
                    // Se eliminan los caracteres especiales excepto el punto y el guion bajo.
                    preg_replace('/[^a-zA-Z0-9\._]/', '', $value . '.')
                )
            );
        }
    }

    private function schematize(
        ?array $fields = [],
        ?string $preffix = '',
        ?string $aliasTable = ''
    ): array {
        $nfields = [];

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
                        $this->schematize(
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
        $field = is_string($params['field'])
            ? trim($params['field'])
            : $params['field'];

        $key = '';
        $value = '';

        if ($field instanceof Expression or is_callable($field)) {
            $key = $this->setPreffix($params['preffix'], $params['aliasColumn']);
            $value = $field;
        } elseif (is_string($field)) {
            if ($this->isACorrectColumnName($field)) {
                $key = $this->setPreffix(
                    $params['preffix'],
                    !is_numeric($params['aliasColumn'])
                        ? $params['aliasColumn']
                        : $field
                );

                $value = $this->setPreffix($params['aliasTable'], $field);
            } else {
                $key = $this->setPreffix($params['preffix'], $params['aliasColumn']);
                $value = DB::raw(sprintf('(%s)', $field));
            }
        }

        return [$key => $value];
    }

    private function setPreffix(?string $preffix, string $value): string
    {
        if (!empty($preffix)) {
            return "{$preffix}.{$value}";
        }

        return $value;
    }

    /**
     * Verifica que un nombre de columna sea correcto.
     *
     * Ejemplos:
     * - column
     * - column_with_underscore
     * - table.column
     * - table.column_with_underscore
     * - alias.column
     * - alias.colum_with_underscore.
     *
     * @param string $ColumnAlias
     */
    private function isACorrectColumnName($ColumnAlias): bool
    {
        if (!is_string($ColumnAlias) or !$ColumnAlias) {
            return false;
        }

        return \preg_match(
            '/^(([a-zA-Z])(\\w+)?\\.)?([a-zA-Z]\\w+|_)$/i',
            $ColumnAlias
        );
    }

    private function isQueryable($value)
    {
        return $value instanceof \Illuminate\Database\Query\Builder
                || $value instanceof \Illuminate\Database\Eloquent\Builder
               || $value instanceof \Illuminate\Database\Eloquent\Relations\Relation
               || $value instanceof \Closure;
    }
}

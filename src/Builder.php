<?php

namespace Joalvm\Utils;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Request\Dates;
use Joalvm\Utils\Request\Paginate;
use Joalvm\Utils\Request\Schema;
use Joalvm\Utils\Request\Search;
use Joalvm\Utils\Request\Sort;

class Builder extends BaseBuilder
{
    protected $attributes = [
        'columns' => [],
        'from' => [],
    ];

    private $filterable = true;

    /**
     * @var Dates
     */
    private $datesBag;

    /**
     * @var PaginateBag
     */
    private $paginateBag;

    /**
     * @var Schema
     */
    private $schemaBag;

    /**
     * @var Search
     */
    private $searchBag;

    /**
     * @var Sort
     */
    private $sortBag;

    /**
     * @var Closure
     */
    private $castCallback;

    public function __construct(?ConnectionInterface $connection = null)
    {
        parent::__construct($connection ?? DB::connection());

        $this->schemaBag = new Schema();
        $this->paginateBag = new Paginate();
        $this->sortBag = new Sort($this->grammar);
        $this->searchBag = new Search($this->grammar);
    }

    public static function connection(string $name = null): self
    {
        return new static(DB::connection($name));
    }

    public static function table(string $table, string $as = null): self
    {
        return (new static())->from($table, $as);
    }

    public function from($tableName, $as = null)
    {
        list($table, $alias) = $this->resolveTableAlias($tableName);

        if ($as) {
            $alias = $as;
        }

        $this->attributes['from'] = [$table, $alias];

        $this->from = "{$table} as {$alias}";

        return $this;
    }

    public function whereIn($column, $values, $boolean = 'and', $not = false): self
    {
        if (is_countable($values)) {
            if (1 === count($values)) {
                $operator = $not ? '<>' : '=';
                $value = array_values($values)[0];

                return parent::where($column, $operator, $value, $boolean);
            }
        }

        return parent::whereIn($column, $values, $boolean, $not);
    }

    public function schema($columns = ['*'])
    {
        $this->attributes['columns'] = $this->resolveSchema(
            $columns,
            '',
            $this->attributes['from'][1] ?? ''
        );

        $this->schemaBag->loadItems($this->attributes['columns']);
        $this->sortBag->loadSchema($this->schemaBag);
        $this->searchBag->loadSchema($this->schemaBag);

        return $this;
    }

    /**
     * Funcion que castea cada item.
     *
     * @param callable(Item): void $callback
     */
    public function casts(callable $callback): self
    {
        $this->castCallback = $callback;

        return $this;
    }

    /**
     * Obtiene elementos en base al proceso de esquematización.
     */
    public function all(): Collection
    {
        $this->prepareQuery();

        return (new Collection(
            $this->paginateBag->paginate
                ? $this->getPagination()
                : $this->get(),
            array_keys($this->attributes['columns'])
        ))->setCasts($this->castCallback);
    }

    public function item(): Item
    {
        $this->schemaBag->setFilterable($this->filterable)->run($this);

        return (new Item((array) $this->first()))->schematize($this->castCallback);
    }

    /**
     * Fuerza el builder a devolver los datos paginados.
     */
    public function pagination(): Collection
    {
        $this->paginateBag->paginate = true;

        $this->prepareQuery();

        return new Collection(
            $this->getPagination(),
            array_keys($this->attributes['columns']),
            $this->castCallback
        );
    }

    public function handleTimestamp(string $column, ?string $columnTZ = null): self
    {
        $this->datesBag = new Dates($column, $columnTZ);

        return $this;
    }

    public function disablePaginate(): self
    {
        $this->paginateBag->disable();

        return $this;
    }

    public function enablePaginate(): self
    {
        $this->paginateBag->enable();

        return $this;
    }

    public static function setPreffix(?string $preffix, string $value): string
    {
        return (!empty($preffix) ? "{$preffix}." : '') . $value;
    }

    public static function isColumnAlias($ColumnAlias): bool
    {
        return \preg_match(
            '/^(([a-zA-Z])(\\w+)?\\.)?([a-zA-Z]\\w+|_)$/i',
            $ColumnAlias
        );
    }

    protected function prepareQuery()
    {
        $this->schemaBag->setFilterable($this->filterable)->run($this);
        $this->sortBag->run($this);
        $this->searchBag->run($this);

        if ($this->datesBag) {
            $this->datesBag->run($this);
        }
    }

    protected function getPagination(): LengthAwarePaginator
    {
        return $this->paginate(
            $this->paginateBag->perPage,
            array_keys($this->columns),
            Paginate::PARAMETER_PAGE,
            $this->paginateBag->page
        );
    }

    private function resolveTableAlias(string $tableName): array
    {
        $tableName = str_replace(' as ', ' AS ', $tableName);

        return (2 == count($parts = explode(' AS ', $tableName)))
            ? $parts
            : (
                2 == count($parts = explode(' ', $tableName))
                    ? $parts
                    : [$tableName, $tableName]
            );
    }

    private function resolveSchema(
        array $fields,
        string $preffix = '',
        string $aliasTable = ''
    ) {
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
                        $this->resolveSchema(
                            $field,
                            self::setPreffix($preffix, $parts[0] . $ast),
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
            $key = self::setPreffix($params['preffix'], $params['aliasColumn']);
            $value = $field;
        } elseif (is_string($field)) {
            if (self::isColumnAlias($field)) {
                $key = self::setPreffix(
                    $params['preffix'],
                    !is_numeric($params['aliasColumn'])
                        ? $params['aliasColumn']
                        : $field
                );

                $value = self::setPreffix($params['aliasTable'], $field);
            } else {
                $key = self::setPreffix($params['preffix'], $params['aliasColumn']);
                $value = DB::raw($field);
            }
        }

        return [$key => $value];
    }
}

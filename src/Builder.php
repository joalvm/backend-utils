<?php

namespace Joalvm\Utils;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Request\Paginate;
use Joalvm\Utils\Request\Schema;
use Joalvm\Utils\Request\Search;
use Joalvm\Utils\Request\Sort;

class Builder extends BaseBuilder
{
    protected $timestamps = [];

    protected $fromAs = '';

    /**
     * Schema de la consulta.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * Paginación de la colección.
     *
     * @var Paginate
     */
    private $paginateBag;

    /**
     * @var Search
     */
    private $searchBag;

    /**
     * @var Sort
     */
    private $sortBag;

    /**
     * @var \callable
     */
    private $castCallback;

    public function __construct(?ConnectionInterface $connection = null)
    {
        parent::__construct($connection ?? DB::connection());

        $this->schema = new Schema($this->grammar);
        $this->paginateBag = new Paginate(Request::query());
        $this->searchBag = new Search();
        $this->sortBag = new Sort();
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
        if ($this->isQueryable($tableName) or $tableName instanceof Expression) {
            if ($as) {
                $this->schema->setFromAs($as);
                $this->registerColumns();
            }

            return parent::from($tableName, $as);
        }

        list($table, $alias) = $this->resolveTableAs($tableName);

        $this->schema->setFromAs($as ?? $alias);
        $this->registerColumns();

        return parent::from($table, $as ?? $alias);
    }

    public function fromSub($query, $as)
    {
        $this->schema->setFromAs($as);
        $this->registerColumns();

        return parent::fromSub($query, $as);
    }

    public function schema($columns = ['*'])
    {
        $this->schema->setItems($columns);
        $this->registerColumns();

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

    /**
     * Obtiene elementos en base al proceso de esquematización.
     */
    public function all(): Collection
    {
        if ($this->paginateBag->paginate) {
            return $this->pagination();
        }

        $this->prepareSearch();
        $this->prepareSorter();

        return (
            new Collection($this->get(), $this->schema->keys())
        )->setCasts($this->castCallback);
    }

    public function getOne(): Item
    {
        $this->paginateBag->disable();

        $this->limit(1);

        return (new Item((array) $this->first()))->schematize($this->castCallback);
    }

    /**
     * Fuerza el builder a devolver los datos paginados.
     */
    public function pagination(): Collection
    {
        $this->paginateBag->paginate = true;

        $this->prepareSearch();
        $this->prepareSorter();

        return (
            new Collection(
                $this->paginate(
                    $this->paginateBag->perPage,
                    array_keys($this->columns),
                    Paginate::PARAMETER_PAGE,
                    $this->paginateBag->page
                ),
                $this->schema->keys()
            )
        )->setCasts($this->castCallback);
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
     * Añade a las columnas de la consulta los items del esquema.
     */
    private function registerColumns()
    {
        foreach ($this->schema->getValues() as $item) {
            if (is_string($item)) {
                $this->addSelect(DB::raw($item));

                continue;
            }

            if (is_array($item)) {
                if ($this->isQueryable($item[0])) {
                    $this->selectSub($item[0], DB::raw(sprintf('"%s"', $item[1])));

                    continue;
                }

                if ($item[0] instanceof Expression) {
                    $column = method_exists($item[0], '__toString')
                        ? (string) $item[0]
                        : $item[0]->getValue($this->grammar);

                    $this->addSelect(
                        DB::raw(sprintf('(%s) as "%s"', $column, $item[1]))
                    );

                    continue;
                }
            }
        }
    }

    private function resolveTableAs(string $tableName): array
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

    private function prepareSearch(): void
    {
        $this->where(function (self $query) {
            foreach ($this->searchBag->getValues($this->schema) as $item) {
                if ($this->isQueryable($item['column'])) {
                    continue;
                }

                $query->orWhereRaw(
                    sprintf('(%s)::text ilike (?)::text', $item['column']),
                    $item['text']
                );
            }
        });
    }

    private function prepareSorter(): void
    {
        foreach ($this->sortBag->getValues($this->schema) as $item) {
            $this->orderBy($item['column'], $item['order']);
        }
    }
}

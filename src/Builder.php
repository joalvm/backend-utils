<?php

namespace Joalvm\Utils;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Joalvm\Utils\Request\Parameters\Paginate;
use Joalvm\Utils\Request\Parameters\Schema;
use Joalvm\Utils\Request\Parameters\Search;
use Joalvm\Utils\Request\Parameters\Sort;
use Joalvm\Utils\Traits\QueryParams;

class Builder extends BaseBuilder
{
    use QueryParams;

    /**
     * Schema de la consulta.
     */
    protected ?Schema $schema;

    /**
     * Nombre de la llave primaria.
     */
    protected string $primaryKey = 'id';

    /**
     * Función de casteo de la llave primaria.
     */
    protected string $primaryKeyfnCast = 'to_int';

    /**
     * @var \Closure
     */
    private $fnCasts;

    public function __construct(?ConnectionInterface $connection = null)
    {
        parent::__construct($connection ?? DB::connection());

        $this->schema = new Schema(Request::query(), $this->grammar);
        $this->paginate = new Paginate(Request::query());
        $this->search = new Search(Request::query());
        $this->sort = new Sort(Request::query());
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
     * Funcion que castea cada item.
     *
     * @param callable(Item): void $callback
     */
    public function casts(callable $callback): self
    {
        $this->fnCasts = $callback;

        return $this;
    }

    public function all(): Collection
    {
        $this->prepare();

        return Collection::make(
            $this->handleCollection()
        )->schematize($this->fnCasts);
    }

    public function find($id, $columns = ['*']): ?Item
    {
        $primaryKeyColumn = sprintf(
            '%s.%s',
            $this->schema->getFromAs(),
            $this->primaryKey
        );

        if (is_callable($this->primaryKeyfnCast)) {
            $id = call_user_func($this->primaryKeyfnCast, $id);
        }

        $this->whereId($id, $primaryKeyColumn);

        $item = parent::first($columns);

        if (!$item) {
            return null;
        }

        return Item::make($item)->schematize($this->fnCasts);
    }

    public function first($columns = ['*']): ?Item
    {
        $this->prepareSorter();

        $item = parent::first($columns);

        if (!$item) {
            return null;
        }

        return Item::make($item)->schematize($this->fnCasts);
    }

    /**
     * Añade al inicio de la lista de where la condición de que el id sea igual al valor.
     *
     * @return self
     */
    protected function whereId(mixed $id, string $primaryKeyColumn)
    {
        $this->bindings['where'] = array_merge([$id], $this->bindings['where']);
        $this->wheres = array_merge(
            [
                [
                    'type' => 'Basic',
                    'column' => $primaryKeyColumn,
                    'operator' => '=',
                    'value' => $id,
                    'boolean' => 'and',
                ],
            ],
            $this->wheres
        );

        return $this;
    }

    private function handleCollection()
    {
        if (!$this->paginate->getPaginate()) {
            return $this->get();
        }

        return $this->paginate(
            $this->paginate->getPerPage(),
            $this->schema->getValues(),
            Paginate::PARAMETER_PAGE_NAME,
            $this->paginate->getPage()
        );
    }

    /**
     * Añade a las columnas de la consulta los items del esquema.
     */
    private function registerColumns(): void
    {
        foreach ($this->schema->getValues() as $item) {
            if (is_string($item)) {
                $this->addSelect(DB::raw($item));

                continue;
            }

            if (is_array($item)) {
                $this->registerCustomColum($item);
            }
        }
    }

    private function registerCustomColum($item)
    {
        if ($this->isQueryable($item[0])) {
            $this->selectSub(
                $item[0],
                $this->grammar->quoteString($item[1])
            );

            return;
        }

        if ($item[0] instanceof Expression) {
            /** @var Expression $column */
            $column = $item[0];
            $this->addSelect(
                DB::raw(
                    sprintf(
                        '(%s) as "%s"',
                        $column->getValue($this->getGrammar()),
                        $item[1]
                    )
                )
            );

            return;
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
}

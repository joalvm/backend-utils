<?php

namespace Joalvm\Utils;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Schema\Columns\AbstractColumn as Column;
use Joalvm\Utils\Schema\Columns\ColumnInterface;
use Joalvm\Utils\Schema\FilterBag;
use Joalvm\Utils\Schema\Schema;
use Joalvm\Utils\Schema\SortBag;

class Builder extends BaseBuilder
{
    public const OPTION_FORCE_PAGINATION = 'force_pagination';

    protected const ALLOWED_OPTIONS = [
        self::OPTION_FORCE_PAGINATION,
    ];

    /**
     * @var Connection
     */
    public $connection;

    /**
     * @var string
     */
    protected $tableAs;

    /**
     * @var SortBag
     */
    protected $sortBag;

    /**
     * @var FilterBag
     */
    protected $filterBag;

    /**
     * @var PaginateBag
     */
    protected $paginateBag;

    /**
     * @var Schema
     */
    private $schema;

    /**
     * Guarda el schema original cuando el from no está definido.
     *
     * @var Schema
     */
    private $temporalSchema;

    /**
     * Guarda las opciones del builder.
     *
     * @var array
     */
    private $options = [
        self::OPTION_FORCE_PAGINATION => false,
    ];

    /**
     * Initialize class.
     *
     * @param null|Connection|ConnectionInterface|string $connection
     */
    public function __construct($connection = null)
    {
        parent::__construct($this->normalizeConnection($connection));

        $this->sortBag = new SortBag();
        $this->filterBag = new FilterBag();
        $this->paginateBag = new PaginateBag();
    }

    public static function connection(string $connectionName): self
    {
        return new static($connectionName);
    }

    public function from($table, $as = null)
    {
        $this->tableAs = $as ?? $table;

        parent::from($table, $as);

        if ($this->schema) {
            $this->initSchema($this->temporalSchema);
            $this->temporalSchema = null;
        }

        return $this;
    }

    /**
     * Extiende el metodo whereIn, cuando el valor es de un solo elemento,
     * el metodo es reemplazado por el metodo `where`, de lo contrario usa
     * el metodo `whereIn`.
     *
     * {@inheritDoc}
     */
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

    public static function table(string $table, ?string $as = null): self
    {
        return (new static())->from($table, $as);
    }

    public function schema(Schema $schema): self
    {
        if (!$this->from) {
            $this->temporalSchema = $schema;
        }

        return $this->initSchema($schema);
    }

    /**
     * Obtiene elementos en base al proceso de esquematización.
     */
    public function all(): Collection
    {
        $this->prepareQuery();

        return new Collection(
            $this->paginateBag->paginate ? $this->getPaginate() : $this->get(),
            $this->schema
        );
    }

    public function pagination(): Collection
    {
        $this->forcePagination();
        $this->prepareQuery();

        return new Collection($this->getPaginate(), $this->schema);
    }

    public function item(): Item
    {
        $this->limit(1);

        return new Item($this->schema->schematize($this->first() ?? []));
    }

    public function setOptions(array $options): self
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }

        return $this;
    }

    public function setOption(string $option, $value): self
    {
        if (in_array($option, self::ALLOWED_OPTIONS)) {
            $this->options[$option] = $value;
        }

        return $this;
    }

    public function forcePagination(): self
    {
        return $this->setOption(self::OPTION_FORCE_PAGINATION, true);
    }

    protected function getPaginate(): LengthAwarePaginator
    {
        return $this->paginate(
            $this->paginateBag->perPage,
            array_keys($this->columns),
            PaginateBag::PARAMETER_PAGE,
            $this->paginateBag->page
        );
    }

    private function initSchema(Schema $schema): self
    {
        $this->schema = $schema;

        $this->schema->setTableAs($this->tableAs)
            ->setDriverName($this->connection->getDriverName())
            ->boot()
        ;

        foreach ($this->filterBag->getColumns($schema) as $column) {
            switch ($column->type) {
                case Column::TYPE_SUBQUERY:
                    $this->selectSub(
                        $column->getColumn(),
                        DB::raw($column->getColumnAs())
                    );

                    break;

                case Column::TYPE_EXPRESSION:
                    $this->selectRaw(
                        DB::raw($column->getColumn(true)),
                        $column->bindings
                    );

                    break;

                default:
                    $this->addSelect(DB::raw($column->getColumn(true)));
            }

            $column->setAddedToSelect(true);
        }

        return $this;
    }

    private function prepareQuery(): void
    {
        if ($this->options[self::OPTION_FORCE_PAGINATION]) {
            $this->paginateBag->paginate = true;
        }

        $this->initSort();
    }

    private function initSort(): void
    {
        foreach ($this->sortBag->getColumns($this->schema) as $order) {
            /** @var ColumnInterface $column */
            $column = $order['column'];

            if (
                Column::TYPE_COLUMN_NAME === $column->type
                or Column::TYPE_EXPRESSION === $column->type
            ) {
                $this->orderBy(
                    DB::raw(
                        $column->getAddedToSelect()
                            ? $column->getColumnAs()
                            : $column->getColumn()
                    ),
                    $order['direction']
                );

                continue;
            }

            if (Column::TYPE_SUBQUERY === $column->type) {
                $this->orderBy($column->getColumn(), $order['direction']);
            }
        }
    }

    /**
     * Normaliza la conección pasada a la clase.
     *
     * @param null|ConnectionInterface|string $connection
     */
    private function normalizeConnection($connection): ConnectionInterface
    {
        if (is_string($connection) || is_null($connection)) {
            return DB::connection($connection ?? DB::getDefaultConnection());
        }

        if ($connection instanceof ConnectionInterface) {
            return $connection;
        }
    }
}

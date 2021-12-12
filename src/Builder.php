<?php

namespace Joalvm\Utils;

use Illuminate\Database\Connection;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Facades\DB;
use Joalvm\Utils\Schema\Columns\AbstractColumn as Column;
use Joalvm\Utils\Schema\FilterBag;
use Joalvm\Utils\Schema\Schema;
use Joalvm\Utils\Schema\SortBag;
use Joalvm\Utils\Traits\Paginatable;

class Builder extends BaseBuilder
{
    use Paginatable {
        paginatable::boot as paginatableBoot;
    }

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
     * @var Schema
     */
    private $schema;

    /**
     * Initialize class.
     *
     * @param null|Connection|ConnectionInterface|string $connection
     */
    public function __construct($connection = null)
    {
        parent::__construct($this->normalizeConnection($connection));

        $this->paginatableBoot();

        $this->sortBag = new SortBag();
        $this->filterBag = new FilterBag();
    }

    public static function connection(string $connectionName): self
    {
        return new static($connectionName);
    }

    public function from($table, $as = null)
    {
        $this->tableAs = $as ?? $table;

        return parent::from($table, $as);
    }

    public static function table(string $table, ?string $as = null): self
    {
        return (new static())->from($table, $as);
    }

    public function schema(Schema $schema): self
    {
        $this->schema = $schema
            ->setTableAs($this->tableAs)
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

    /**
     * Obtiene elementos en base al proceso de esquematización.
     */
    public function all(): Collection
    {
        $this->initSort();

        return new Collection(
            $this->paginate
                ? $this->paginate($this->perPage, ['*'], 'page', $this->page)
                : $this->get(),
            $this->schema
        );
    }

    public function item(): Item
    {
        $this->limit(1);

        return new Item((new Collection($this->get(), $this->schema))->first());
    }

    private function initSort(): void
    {
        foreach ($this->sortBag->getColumns($this->schema) as $order) {
            /** @var \Joalvm\Utils\Schema\Columns\ColumnInterface $column */
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
            } elseif (Column::TYPE_SUBQUERY === $column->type) {
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

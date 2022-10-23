<?php

namespace Joalvm\Utils\Schematic;

use Joalvm\Utils\Schematic\Grammars\Grammar;
use Joalvm\Utils\Schematic\Types\Type;
use Joalvm\Utils\Schematic\Types\UnknownType;
use ReflectionClass;

class Schema
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $tableAs;

    /**
     * @var string[]
     */
    protected $parents = [];

    /**
     * @var string
     */
    protected $defaultType = UnknownType::class;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * Maneja la creación de sentencias SQL.
     *
     * @var Grammar
     */
    private $grammar;

    /**
     * Indica si el esquema ha sido preparado.
     *
     * @var bool
     */
    private $ready = false;

    /**
     * Undocumented variable.
     *
     * @var array
     */
    private $filters = [];

    /**
     * @param array|string $first  alias for the table
     * @param array        $second propiedades del esquema
     */
    public function __construct($first, $second = [])
    {
        if (is_array($first)) {
            $second = $first;
            $first = null;
        }

        $this->setTableAs($first);
        $this->setProperties(array_merge($this->properties, $second));
    }

    public function setFilters($filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    public function prepare(): void
    {
        $properties = [];

        if ($this->ready) {
            return;
        }

        foreach ($this->properties as $key => $value) {
            [$name, $as] = $this->normalizeKey($key, $value);

            if ($this->isItemSchema($value)) {
                $properties[$name] = $this->handleItemSchema($value, $name, $as);

                continue;
            }

            if ($this->isItemType($value)) {
                $properties[$name] = $this->handleItemType($value, $name, $as);

                continue;
            }
        }

        $this->properties = $properties;

        $this->ready = true;
    }

    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        $this->ready = false;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = to_str($name);

        $this->ready = false;

        return $this;
    }

    public function setTableAs(?string $tableAs): self
    {
        $this->tableAs = to_str($tableAs);

        $this->ready = false;

        return $this;
    }

    public function setGrammar(Grammar $grammar): self
    {
        $this->grammar = $grammar;

        $this->ready = false;

        return $this;
    }

    public function addParents($schemaName): self
    {
        array_push($this->parents, ...to_list($schemaName));

        $this->ready = false;

        return $this;
    }

    public function hasTableAs(): bool
    {
        return !is_null($this->tableAs);
    }

    public function hasName(): bool
    {
        return !is_null($this->name);
    }

    public function hasGrammar(): bool
    {
        return !is_null($this->grammar);
    }

    public function getColumns(Grammar $grammar): array
    {
        $columns = [];

        foreach ($this->properties as $property) {
            if ($property instanceof Schema) {
                $columns = array_merge(
                    $columns,
                    $property->getColumns($grammar)
                );

                continue;
            }

            $columns[] = $grammar->compileSelect($property);
        }

        return $columns;
    }

    public function flatten(): array
    {
        $columns = [];

        foreach ($this->properties as $property) {
            if ($property instanceof Schema) {
                $columns = array_merge($columns, $property->flatten());

                continue;
            }

            $columns[$property->as] = $property;
        }

        return $columns;
    }

    /**
     * Prepara un item del esquema.
     *
     * @param string|Type $type
     */
    private function handleItemType($type, ?string $name, ?string $alias): Type
    {
        if (!$type instanceof Type) {
            /** @var Type $type */
            $type = (new ReflectionClass($this->defaultType))->newInstanceArgs([$type]);
        }

        $type->setAs($this->makeColumnAs($name));

        if (!$type->hasColumn()) {
            $type->setColumn($name);
        }

        if (!$type->hasTableAs()) {
            $type->setTableAs($alias);
        }

        return $type;
    }

    /**
     * Prepara el atributo para ser usado en una consulta.
     *
     * @param array|static $schema
     */
    private function handleItemSchema($schema, ?string $name, ?string $tableAs): Schema
    {
        // When schema is array, we need to create a new schema
        if (is_array($schema)) {
            $schema = new static($schema);
        }

        if (!$schema->hasTableAs()) {
            $schema->setTableAs($tableAs);
        }

        $schema->setName($name);
        $schema->addParents([...$this->parents, $this->name]);

        $schema->setGrammar($this->grammar)->prepare();

        return $schema;
    }

    /**
     * Divide el nombre de la tabla en dos partes.
     *
     * @param mixed $type
     *
     * @return string[]
     */
    private function normalizeKey(string $alias, $type)
    {
        if (is_numeric($alias)) {
            return [strval($type), $this->tableAs];
        }

        $parts = to_list(strval($alias), true, ':');

        return [
            to_str($parts[0]),
            to_str($parts[1] ?? $this->tableAs),
        ];
    }

    private function makeColumnAs($alias): string
    {
        return implode('.', to_list([...$this->parents, $this->name, $alias]));
    }

    private function isItemType($item): bool
    {
        return $item instanceof Type
            or $item instanceof \Closure
            or is_string($item)
            or is_object($item);
    }

    private function isItemSchema($item): bool
    {
        return is_array($item) or $item instanceof Schema;
    }
}

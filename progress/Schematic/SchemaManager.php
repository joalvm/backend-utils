<?php

namespace Joalvm\Utils\Schematic;

use InvalidArgumentException;
use Joalvm\Utils\Schematic\Grammars\Grammar;
use Joalvm\Utils\Schematic\Grammars\PgsqlGrammar;
use Joalvm\Utils\Schematic\Types\Type;
use Joalvm\Utils\Schematic\Types\UnknownType;
use PDO;

class SchemaManager
{
    /**
     * Nombre del driver de la base de datos.
     *
     * @var string
     */
    protected $pdoDriver;

    /**
     * Clase que maneja la sintaxis de la base de datos.
     *
     * @var Grammar
     */
    protected $grammar;

    /**
     * Tipo por defecto para las propiedades que no tengan un tipo especificado.
     */
    protected $defaultType = UnknownType::class;

    /**
     * @var Schema
     */
    protected $schema = [];

    public function __construct(string $pdoDriver)
    {
        $this->setPdoDriver($pdoDriver);
    }

    public function setSchema(Schema $schema): self
    {
        $this->schema = $schema;

        if (!$schema->hasGrammar()) {
            $this->schema->setGrammar($this->grammar);
        }

        return $this;
    }

    public function prepare()
    {
        if (!$this->schema->hasGrammar()) {
            $this->schema->setGrammar($this->grammar);
        }

        $this->schema->prepare();
    }

    public function columns()
    {
        return $this->schema->getColumns($this->grammar);
    }

    public function flatten()
    {
        return $this->schema->flatten($this->grammar);
    }

    public function setDefaultType(string $type)
    {
        if (!is_a($type, Type::class, true)) {
            throw new InvalidArgumentException('The type must be an instance of Type');
        }

        $this->defaultType = $type;
    }

    public function setPdoDriver(string $pdoDriver)
    {
        if (!in_array($pdoDriver, PDO::getAvailableDrivers())) {
            throw new InvalidArgumentException('PDO driver not found');
        }

        $this->pdoDriver = $pdoDriver;

        switch ($this->pdoDriver) {
            case 'pgsql':
                $this->grammar = new PgsqlGrammar();

                break;
        }

        return $this;
    }
}

<?php

namespace Joalvm\Utils\Schema;

use Illuminate\Support\Arr;
use Joalvm\Utils\Schema\Columns\ColumnInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class FilterBag extends ParameterBag
{
    /**
     * Indica que el filtro del esquema es obligatorio.
     *
     * @var bool
     */
    protected $force = false;

    /**
     * Indica si el esquema puede ser filtrado.
     *
     * @var bool
     */
    protected $locked = false;

    public function __construct()
    {
        parent::__construct(
            $this->getParameters(Arr::get($_GET, 'schema', []))
        );
    }

    public function enableForce()
    {
        $this->force = true;
        $this->locked = false;
    }

    /**
     * Indica que el esquema no puede ser filtrado.
     */
    public function lock()
    {
        $this->locked = true;
    }

    public function getParameters($parameters): array
    {
        $values = [];

        if (is_string($parameters)) {
            $parameters = to_list_str(
                filter_var($parameters, FILTER_SANITIZE_STRING)
            );
        }

        $values = array_map(
            function ($parameter) {
                return $this->convertToRegex($parameter);
            },
            array_filter(
                $parameters,
                function ($parameter) {
                    $parameter = filter_var($parameter, FILTER_SANITIZE_STRING);

                    return is_string($parameter) and strlen($parameter) > 0;
                }
            ),
        );

        if ($this->force and 0 === count($values)) {
            throw new BadRequestHttpException('No schema filters provided');
        }

        return $values;
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(Schema $schema)
    {
        if ($this->locked) {
            return $schema->getColumns();
        }

        $filtered = array_filter(
            $schema->getColumns(),
            function (ColumnInterface $column) {
                foreach ($this->parameters as $filter) {
                    if (
                        preg_match($filter, $column->columnAs)
                        and $column->isFilterable()
                    ) {
                        return true;
                    }
                }

                return false;
            }
        );

        if (0 === count($filtered) and $this->force) {
            throw new BadRequestHttpException('No columns match the filters');
        }

        return count($filtered) > 0 ? $filtered : $schema->getColumns();
    }

    /**
     * convierte el filtro a una expresión regular.
     */
    private function convertToRegex(string $value): string
    {
        return sprintf(
            '/^%s/i',
            str_replace(
                '*',
                '(?:[a-z._]+)',
                filter_var($value, FILTER_SANITIZE_STRING)
            )
        );
    }
}

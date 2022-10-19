<?php

namespace Joalvm\Utils\Schematic\Types\Pgsql;

use Joalvm\Utils\Schematic\Types\StrType;

class Varchar extends StrType
{
    protected const CASTER = 'varchar';
}

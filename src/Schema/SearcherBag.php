<?php

namespace Joalvm\Utils\Schema;

use Symfony\Component\HttpFoundation\ParameterBag;

class SearcherBag extends ParameterBag
{
    public const CONTAINS_PARAMETER = 'contains';
    public const STARTS_WITH_PARAMETER = 'starts_with';
    public const ENDS_WITH_PARAMETER = 'ends_with';
    public const EQUALS_PARAMETER = 'equals';
    public const NOT_EQUALS_PARAMETER = 'not_equals';
    public const IN_PARAMETER = 'in';
    public const NOT_IN_PARAMETER = 'not_in';

    public const PARAMETERS = [
        self::CONTAINS_PARAMETER,
        self::STARTS_WITH_PARAMETER,
        self::ENDS_WITH_PARAMETER,
        self::EQUALS_PARAMETER,
        self::NOT_EQUALS_PARAMETER,
        self::IN_PARAMETER,
        self::NOT_IN_PARAMETER,
    ];

    public function __construct()
    {
    }
}

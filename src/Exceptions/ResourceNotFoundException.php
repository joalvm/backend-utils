<?php

namespace Joalvm\Utils\Exceptions;

use Illuminate\Support\Facades\Lang;
use Symfony\Component\HttpFoundation\Response;

class ResourceNotFoundException extends Exception
{
    public const HTTP_CODE = Response::HTTP_NOT_FOUND;

    public function __construct(string $resourceName)
    {
        parent::__construct(
            Lang::get('resource.not_found', ['name' => $resourceName])
        );
    }
}

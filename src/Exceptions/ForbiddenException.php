<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends Exception
{
    public const HTTP_CODE = Response::HTTP_FORBIDDEN;
}

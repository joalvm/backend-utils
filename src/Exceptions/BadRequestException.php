<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends Exception
{
    public const HTTP_CODE = Response::HTTP_BAD_REQUEST;
}

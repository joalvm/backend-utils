<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception
{
    public const HTTP_CODE = Response::HTTP_NOT_FOUND;
}

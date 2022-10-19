<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityException extends Exception
{
    public const HTTP_CODE = Response::HTTP_UNPROCESSABLE_ENTITY;
}

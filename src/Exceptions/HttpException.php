<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException as BaseHttpException;

class HttpException extends BaseHttpException
{
    protected $errors = [];

    public function errors(): array
    {
        return $this->errors;
    }
}

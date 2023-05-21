<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends HttpException
{
    public function __construct(
        $message = null,
        \Throwable $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            $message ?? Response::$statusTexts[Response::HTTP_BAD_REQUEST],
            $previous,
            $headers,
            $code
        );
    }
}

<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends HttpException
{
    public function __construct(
        $message = null,
        \Throwable $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(
            Response::HTTP_UNAUTHORIZED,
            $message ?? Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
            $previous,
            $headers,
            $code
        );
    }
}

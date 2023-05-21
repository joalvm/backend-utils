<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends HttpException
{
    public function __construct(
        $message = null,
        \Throwable $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(
            Response::HTTP_FORBIDDEN,
            $message ?? Response::$statusTexts[Response::HTTP_FORBIDDEN],
            $previous,
            $headers,
            $code
        );
    }
}

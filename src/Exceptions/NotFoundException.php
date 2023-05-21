<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends HttpException
{
    public function __construct(
        $message = null,
        \Throwable $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(
            Response::HTTP_NOT_FOUND,
            $message ?? Response::$statusTexts[Response::HTTP_NOT_FOUND],
            $previous,
            $headers,
            $code
        );
    }
}

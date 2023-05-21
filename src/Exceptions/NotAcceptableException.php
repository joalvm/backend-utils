<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotAcceptableException extends HttpException
{
    public function __construct(
        $message = null,
        \Throwable $previous = null,
        array $headers = [],
        $code = 0
    ) {
        parent::__construct(
            Response::HTTP_NOT_ACCEPTABLE,
            $message ?? Response::$statusTexts[Response::HTTP_NOT_ACCEPTABLE],
            $previous,
            $headers,
            $code
        );
    }
}

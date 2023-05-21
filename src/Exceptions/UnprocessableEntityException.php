<?php

namespace Joalvm\Utils\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnprocessableEntityException extends HttpException
{
    /**
     * @param array $errors Information about the error(s) that occurred
     */
    public function __construct(
        array $errors = [],
        ?string $message = null,
        \Throwable $previous = null,
        array $headers = [],
        mixed $code = 0
    ) {
        parent::__construct(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $message ?? Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            $previous,
            $headers,
            $code
        );

        $this->errors = $errors;
    }
}

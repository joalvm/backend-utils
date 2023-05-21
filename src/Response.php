<?php

namespace Joalvm\Utils;

use Illuminate\Http\Response as BaseResponse;
use Illuminate\Validation\ValidationException;
use Joalvm\Utils\Exceptions\UnprocessableEntityException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Response extends BaseResponse
{
    public function __construct($content, $httpCode, ?string $message = null)
    {
        $httpCode = $this->normalizeHttpCode($httpCode);

        parent::__construct(
            $this->normalizeContent($content, $httpCode, $message),
            $httpCode
        );
    }

    /**
     * Is response successful?
     *
     * @final
     *
     * @param mixed $statusCode
     */
    public function isSuccessfulCode($statusCode): bool
    {
        return $statusCode >= 200 && $statusCode < 300;
    }

    public static function success(): self
    {
        return new self(null, BaseResponse::HTTP_OK);
    }

    public static function collection(
        $content,
        int $statusCode = self::HTTP_OK,
        ?string $message = null
    ) {
        return new static($content, $statusCode, $message);
    }

    public static function item(
        $content,
        int $statusCode = self::HTTP_OK,
        ?string $message = null
    ) {
        if (self::isEmptyContent($content) and self::HTTP_OK === $statusCode) {
            return new static(null, self::HTTP_NOT_FOUND);
        }

        return new static($content, $statusCode, $message);
    }

    public static function stored($content, ?string $message = null)
    {
        return self::item($content, self::HTTP_CREATED, $message);
    }

    public static function updated($content, ?string $message = null)
    {
        return self::item($content, self::HTTP_ACCEPTED, $message);
    }

    public static function destroyed($content, ?string $message = null)
    {
        return self::updated($content, $message);
    }

    /**
     * Captura las excepciones.
     *
     * @param Exception|ValidationException $ex
     */
    public static function catch($ex): self
    {
        $httpCode = $ex->getCode();
        $message = $ex->getMessage();
        $content = $ex->getTrace();

        // Las excepciones de laravel guardan los codigo en
        // en el metodo getStatusCode
        if (method_exists($ex, 'getStatusCode')) {
            $httpCode = call_user_func([$ex, 'getStatusCode']);
        }

        if (self::isValidatorException($ex)) {
            $httpCode = self::HTTP_UNPROCESSABLE_ENTITY;
            $content = $ex->errors();
            $message = $ex->getMessage(); // Respuesta custom
        }

        return new static($content, $httpCode, $message);
    }

    private function normalizeContent($content, $httpCode, ?string $message = null)
    {
        $body = [
            'error' => !$this->isSuccessfulCode($httpCode),
            'message' => $message ?: self::$statusTexts[$httpCode],
            'code' => $httpCode,
        ];

        if ($content instanceof Collection) {
            $body['data'] = $content->toArray();

            if ($content->isPagination()) {
                $body['meta'] = $content->getMetadata();
            }

            return $body;
        }

        return array_merge($body, ['data' => $content]);
    }

    private static function normalizeHttpCode($statusCode)
    {
        if (is_numeric($statusCode)) {
            if (!($statusCode < 100 || $statusCode >= 600)) {
                return $statusCode;
            }
        }

        return self::HTTP_INTERNAL_SERVER_ERROR;
    }

    private static function isEmptyContent($content): bool
    {
        if ($content instanceof \Countable) {
            return 0 === count($content);
        }

        if (is_object($content) and method_exists($content, 'isEmpty')) {
            return $content->isEmpty();
        }

        return empty($content);
    }

    private static function isValidatorException(\Throwable $ex): bool
    {
        return
            $ex instanceof ValidationException
            or $ex instanceof UnprocessableEntityException
            or $ex instanceof UnprocessableEntityHttpException;
    }
}

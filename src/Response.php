<?php

namespace Joalvm\Utils;

use Illuminate\Http\Response as BaseResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Joalvm\Utils\Exceptions\UnprocessableEntityException;

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
     */
    public static function catch(\Throwable $ex): self
    {
        $httpCode = $ex->getCode();
        $message = $ex->getMessage();
        $content = null;

        // Las excepciones de laravel guardan los codigo en
        // en el metodo getStatusCode
        // Las excepciones de laravel guardan los codigo en en el metodo getStatusCode
        if ($ex instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            $httpCode = $ex->getStatusCode();
        }

        // Cuando se usa el metodos findOrFail de eloquent
        if ($ex instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $httpCode = self::HTTP_NOT_FOUND;
            $message = sprintf(
                'Resource %s not found',
                Str::snake(Str::singular(Str::afterLast($ex->getModel(), '\\')), ' ')
            );
            $content = $ex->getIds();
        }

        if ($ex instanceof \Joalvm\Utils\Exceptions\HttpException) {
            $content = $ex->errors();
        }

        if (
            $ex instanceof ValidationException
            or $ex instanceof UnprocessableEntityException
        ) {
            $httpCode = self::HTTP_UNPROCESSABLE_ENTITY;
            $message = $ex->getMessage();
            $content = $ex->errors();
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
}

<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Response as BaseResponse;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Validation\ValidationException;
use Joalvm\Utils\Exceptions\NotFoundException;
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
            return self::catch(new NotFoundException());
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
        return self::updated(['deleted' => $content], $message);
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
        $content = null;

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
        return array_merge(
            [
                'error' => !$this->isSuccessfulCode($httpCode),
                'message' => (
                    empty($message)
                    ? strtoupper(self::$statusTexts[$httpCode])
                    : $message
                ),
                'code' => $httpCode,
            ],
            (is_a($content, Collection::class) and $content->isPagination())
                ? $content->toArray()
                : ['data' => $content]
        );
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
        $empty = false;

        if (self::isPaginator($content) || self::isCollection($content)) {
            $empty = $content->isEmpty();
        } else {
            $empty = empty($content);
        }

        return $empty;
    }

    private static function isPaginator($data)
    {
        return $data instanceof Paginator || $data instanceof LengthAwarePaginator;
    }

    private static function isValidatorException($ex): bool
    {
        return $ex instanceof UnprocessableEntityException
        || $ex instanceof ValidationException;
    }

    private static function isCollection($data): bool
    {
        return
            ($data instanceof BaseCollection)
            || ($data instanceof EloquentCollection);
    }
}

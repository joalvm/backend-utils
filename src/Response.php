<?php

namespace Joalvm\Utils;

use Exception;
use Illuminate\Http\Response as BaseResponse;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use PDOException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            return self::catch(new NotFoundHttpException());
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
            $content = call_user_func([$ex, 'errors']);
            $message = $ex->getMessage(); // Respuesta custom
        }

        return new static($content, $httpCode, $message);
    }

    private function normalizeContent($content, $httpCode, ?string $message = null)
    {
        return array_merge(
            [
                'error' => !$this->isSuccessfulCode($httpCode),
                'message' => $this->normalizeMessage($message, $httpCode),
                'code' => $httpCode,
            ],
            $this->normalizeData($content),
        );
    }

    private function normalizeMessage($message, $httpCode): string
    {
        if (!Config::get('app.debug') || empty($message)) {
            return strtoupper(self::$statusTexts[$httpCode]);
        }

        return $message;
    }

    private function normalizeData($content): array
    {
        if ($content instanceof Collection) {
            return !$content->isPagination()
                ? ['data' => $content->all()]
                : $content->all();
        }

        return ['data' => $content];
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
        if ($content instanceof Enumerable) {
            return $content->isEmpty();
        }

        return empty($content);
    }

    /**
     * Determina si la excepción es un validador con codigo 422.
     *
     * @param HttpException|UnprocessableEntityHttpException|ValidationException $ex
     */
    private static function isValidatorException(\Throwable $ex): bool
    {
        if (
            $ex instanceof UnprocessableEntityHttpException
            || $ex instanceof ValidationException
        ) {
            return true;
        }

        if ($ex instanceof PDOException) {
            return false;
        }

        return self::HTTP_UNPROCESSABLE_ENTITY === (
            $ex->status ?? $ex->getStatusCode() ?? 0
        );
    }
}

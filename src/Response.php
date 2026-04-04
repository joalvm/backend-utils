<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Joalvm\Utils\Exceptions\HttpException;
use Joalvm\Utils\Exceptions\UnprocessableEntityException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Clase para gestionar respuestas HTTP JSON y manejo de excepciones en aplicaciones Laravel.
 *
 * Proporciona métodos para respuestas estándar (colección, item, creado, actualizado, eliminado, error, descarga)
 * y para capturar y transformar excepciones en respuestas JSON estructuradas.
 *
 * @phpstan-type ExceptionParams array{code: int, message: string, content: mixed}
 */
class Response
{
    /**
     * Las cabeceras de la respuesta.
     *
     * @var array<string, mixed>
     */
    public $headers = [];

    /**
     * Última excepción capturada.
     *
     * @var \Throwable|null
     */
    public $exception;

    /**
     * @param ResponseFactory $factory Fábrica de respuestas de Laravel.
     * @param bool            $debug   Si es true, incluye el trace de la excepción en la respuesta.
     */
    public function __construct(
        private ResponseFactory $factory,
        private string $basePath = '',
        private bool $debug = false,
    ) {
    }

    /**
     * Genera una respuesta JSON.
     *
     * @param mixed                $content    Contenido de la respuesta.
     * @param int                  $statusCode Código HTTP.
     * @param array<string, mixed> $headers    Cabeceras adicionales.
     *
     * @return JsonResponse
     */
    public function response(
        mixed $content,
        int $statusCode,
        array $headers = []
    ): JsonResponse {
        $data = $content;

        if ($content instanceof Collection and $content->isPaginate()) {
            $data = [
                'data' => $content->all(),
                ...$content->getMetadata(),
            ];
        }

        return $this->factory->json($data, $statusCode, $headers);
    }

    /**
     * Genera una respuesta para colecciones.
     *
     * @param mixed                $content
     * @param int                  $statusCode
     * @param array<string, mixed> $headers
     *
     * @return JsonResponse
     */
    public function collection(
        mixed $content,
        int $statusCode = BaseResponse::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        return $this->response($content, $statusCode, $headers);
    }

    /**
     * Genera una respuesta para un solo elemento.
     *
     * @param mixed                $content
     * @param int                  $statusCode
     * @param array<string, mixed> $headers
     *
     * @return JsonResponse
     */
    public function item(
        mixed $content,
        int $statusCode = BaseResponse::HTTP_OK,
        array $headers = []
    ): JsonResponse {
        if ($this->isEmptyContent($content) and BaseResponse::HTTP_OK === $statusCode) {
            $content = '';
            $statusCode = BaseResponse::HTTP_NOT_FOUND;
        }

        return $this->response($content, $statusCode, $headers);
    }

    /**
     * Respuesta para recurso creado.
     *
     * @param mixed                $content
     * @param array<string, mixed> $headers Cabeceras adicionales
     *
     * @return JsonResponse
     */
    public function stored(mixed $content, array $headers = []): JsonResponse
    {
        return $this->item($content, BaseResponse::HTTP_CREATED, $headers);
    }

    /**
     * Respuesta para recurso actualizado.
     *
     * @param mixed                $content
     * @param array<string, mixed> $headers Cabeceras adicionales
     *
     * @return JsonResponse
     */
    public function updated(mixed $content, array $headers = []): JsonResponse
    {
        return $this->item($content, BaseResponse::HTTP_ACCEPTED, $headers);
    }

    /**
     * Respuesta para recurso eliminado.
     *
     * @param mixed                $content
     * @param array<string, mixed> $headers Cabeceras adicionales
     *
     * @return JsonResponse
     */
    public function destroyed(mixed $content, array $headers = []): JsonResponse
    {
        return $this->updated($content, $headers);
    }

    /**
     * Respuesta exitosa estándar.
     *
     * @param mixed|null           $content
     * @param array<string, mixed> $headers Cabeceras adicionales
     *
     * @return JsonResponse
     */
    public function ok(mixed $content = null, array $headers = []): JsonResponse
    {
        return $this->response($content, BaseResponse::HTTP_OK, $headers);
    }

    /**
     * Respuesta para descarga de archivos.
     *
     * @param string               $path        Ruta del archivo.
     * @param string|null          $name        Nombre del archivo.
     * @param array<string, mixed> $headers     Cabeceras adicionales.
     * @param string               $disposition Disposición del archivo (attachment o inline).
     *
     * @return BinaryFileResponse
     */
    public function download(
        string $path,
        ?string $name = null,
        array $headers = [],
        string $disposition = 'attachment'
    ): BinaryFileResponse {
        return $this->factory->download($path, $name, $headers, $disposition);
    }

    /**
     * Respuesta de error personalizada.
     *
     * @param string               $message    Mensaje de error.
     * @param int                  $statusCode Código HTTP.
     * @param mixed                $content    Detalles del error.
     * @param array<string, mixed> $headers    Cabeceras adicionales.
     *
     * @return JsonResponse
     */
    public function error(
        string $message,
        int $statusCode = BaseResponse::HTTP_BAD_REQUEST,
        mixed $content = null,
        array $headers = []
    ): JsonResponse {
        $content = [
            'message' => Lang::get($message),
            'errors' => $content,
        ];

        return $this->response($content, $statusCode, $headers);
    }

    /**
     * Captura y transforma una excepción en respuesta JSON.
     *
     * @param \Throwable $ex
     *
     * @return JsonResponse
     */
    public function catch(\Throwable $ex): JsonResponse
    {
        $this->exception = $ex;

        $params = $this->handleParamsFromException();

        $content = [
            'message' => $params['message'],
            'errors' => $params['content'],
        ];

        if ($this->debug) {
            $content['trace'] = $this->getTrace($this->exception);
        }

        return $this->factory->json($content, $params['code'], $this->getHeaders());
    }

    /**
     * Procesa la excepción y retorna los parámetros para la respuesta.
     *
     * @return ExceptionParams
     */
    private function handleParamsFromException(): array
    {
        if ($this->exception instanceof \PDOException) {
            return [
                'code' => BaseResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $this->exception->getMessage(),
                'content' => null,
            ];
        }

        if ($this->isValidationException($this->exception)) {
            /** @var ValidationException $ex */
            $ex = $this->exception;

            return [
                'code' => BaseResponse::HTTP_UNPROCESSABLE_ENTITY,
                'message' => Lang::get($this->exception->getMessage()),
                'content' => $ex->errors(),
            ];
        }

        // Cuando se usa el metodos findOrFail de eloquent
        if (
            $this->exception instanceof ModelNotFoundException
            or $this->exception->getPrevious() instanceof ModelNotFoundException
        ) {
            $modelName = $this->exception instanceof ModelNotFoundException
                ? $this->exception->getModel()
                : call_user_func([$this->exception->getPrevious(), 'getModel']);

            $resourceName = implode(
                ' ',
                Str::ucsplit(Str::afterLast($modelName, '\\'))
            );

            return [
                'code' => BaseResponse::HTTP_NOT_FOUND,
                'message' => sprintf('The resource %s could not be found.', $resourceName),
                'content' => null,
            ];
        }

        if ($this->exception instanceof HttpException) {
            return [
                'code' => $this->exception->getStatusCode(),
                'message' => Lang::get($this->exception->getMessage()),
                'content' => $this->exception->errors()
            ];
        }

        // Las excepciones de laravel guardan los codigo en en el metodo getStatusCode
        if ($this->exception instanceof HttpExceptionInterface) {
            return [
                'code' => $this->exception->getStatusCode(),
                'message' => Lang::get($this->exception->getMessage()),
                'content' => null,
            ];
        }

        $httpCode = $this->exception->getCode();

        if (is_string($this->exception->getCode()) or $httpCode < 100 or $httpCode > 599) {
            $httpCode = BaseResponse::HTTP_INTERNAL_SERVER_ERROR;
        }

        return [
            'code' => $httpCode,
            'message' => Lang::get($this->exception->getMessage()),
            'content' => null,
        ];
    }

    /**
     * Determina si la excepción es de validación.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    private function isValidationException(\Throwable $exception): bool
    {
        return $exception instanceof ValidationException
            || $exception instanceof UnprocessableEntityException
            || $exception instanceof UnprocessableEntityHttpException;
    }

    /**
     * Obtiene las cabeceras asociadas a la excepción.
     *
     * @return array<string, mixed>
     */
    private function getHeaders(): array
    {
        $headers = [];

        if ($this->exception instanceof HttpException) {
            $headers = $this->exception->getHeaders();
        }

        if ($this->exception instanceof ValidationException) {
            $headers = $this->exception->response?->headers?->all() ?? [];
        }

        return $headers;
    }

    /**
     * Determina si el contenido está vacío.
     *
     * @param mixed $content
     *
     * @return bool
     */
    private function isEmptyContent(mixed $content): bool
    {
        if ($content instanceof \Countable) {
            return 0 === count($content);
        }

        if (is_object($content) and method_exists($content, 'isEmpty')) {
            return $content->isEmpty();
        }

        return empty($content);
    }

    /**
     * Obtiene el trace de la excepción como array de strings.
     *
     * @return string[]
     */
    private function getTrace(): array
    {
        return array_map(
            fn ($trace) => sprintf(
                '%s:%d %s',
                str_replace(
                    $this->basePath . DIRECTORY_SEPARATOR,
                    '...',
                    $trace['file'] ?? 'unknown'
                ),
                $trace['line'] ?? 0,
                $trace['function'] ?? 'unknown'
            ),
            $this->exception->getTrace(),
        );
    }
}

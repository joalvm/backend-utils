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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ResponseManager
{
    /**
     * Las cabeceras de la respuesta.
     *
     * @var array
     */
    public $headers = [];

    /**
     * @var \Throwable
     */
    public $exception;

    public function __construct(
        private ResponseFactory $factory,
        private bool $debug = false,
    ) {
    }

    public function collection(
        mixed $content,
        int $status = Response::HTTP_OK,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->response($content, $status, $headers, $options);
    }

    public function item(
        mixed $content,
        int $status = Response::HTTP_OK,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        if ($this->isEmptyContent($content) and Response::HTTP_OK === $status) {
            $content = null;
            $status = Response::HTTP_NOT_FOUND;
        }

        return $this->response($content, $status, $headers, $options);
    }

    public function response($content, $status, $headers, $options): JsonResponse
    {
        $data = $content;

        if ($content instanceof Collection) {
            if ($content->isPaginate()) {
                $data = [
                    'data' => $content->all(),
                    ...$content->getMetadata(),
                ];
            }
        }

        return $this->factory->json($data, $status, $headers, $options);
    }

    public function stored(mixed $content): JsonResponse
    {
        return $this->item($content, Response::HTTP_CREATED);
    }

    public function updated(mixed $content): JsonResponse
    {
        return $this->item($content, Response::HTTP_ACCEPTED);
    }

    public function destroyed(mixed $content): JsonResponse
    {
        return $this->updated($content);
    }

    public function ok(mixed $content = null): JsonResponse
    {
        return $this->collection($content);
    }

    public function download(string $path, ?string $name = null, bool $deleteFile = true): BinaryFileResponse
    {
        $response = $this->factory->download($path, $name);

        if ($deleteFile) {
            $response->deleteFileAfterSend(true);
        }

        return $response;
    }

    /**
     * Captura las excepciones.
     */
    public function catch(\Throwable $ex)
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

    private function handleParamsFromException(): array
    {
        if ($this->exception instanceof \PDOException) {
            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $this->exception->getMessage(),
                'content' => null,
            ];
        }

        if ($this->isValidationException($this->exception)) {
            /** @var ValidationException $ex */
            $ex = $this->exception;

            return [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => Lang::get($this->exception->getMessage()),
                'content' => $ex->errors(),
            ];
        }

        // Cuando se usa el metodos findOrFail de eloquent
        if ($this->exception instanceof ModelNotFoundException) {
            $resourceName = implode(
                ' ',
                Str::ucsplit(Str::afterLast($this->exception->getModel(), '\\'))
            );

            return [
                'code' => Response::HTTP_NOT_FOUND,
                'message' => sprintf('The resource %s could not be found.', $resourceName),
                'content' => null,
            ];
        }

        if ($this->exception instanceof HttpException) {
            return [
                'code' => $this->exception->getStatusCode(),
                'message' => Lang::get($this->exception->getMessage()),
                'content' => !$this->exception->errors() ? null : $this->exception->errors(),
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
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return [
            'code' => $httpCode,
            'message' => Lang::get($this->exception->getMessage()),
            'content' => null,
        ];
    }

    private function isValidationException(\Throwable $exception): bool
    {
        return $this->exception instanceof ValidationException
            || $this->exception instanceof UnprocessableEntityException
            || $this->exception instanceof UnprocessableEntityHttpException;
    }

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

    private function getTrace(): ?string
    {
        $trace = (string) mb_convert_encoding($this->exception, 'UTF-8');

        if (empty($trace)) {
            return null;
        }

        return $trace;
    }
}

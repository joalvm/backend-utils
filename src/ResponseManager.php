<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResponseManager
{
    public $isDeveloper = false;

    public $logger = true;

    public $debug = [];

    /**
     * @var \Throwable
     */
    public $exception;

    /**
     * Guarda los datos de autenticación que se obtuvieron
     * al momento de autenticar al usuario.
     *
     * @var array
     */
    public $auth = [];

    public $headers = [];

    public function __construct(
        private ResponseFactory $responseFactory,
        private Request $request,
    ) {
    }

    public function collection(
        mixed $content,
        int $status = Response::HTTP_OK,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->responseFactory->json(
            $this->normalizeContent($content),
            $status,
            $headers,
            $options
        );
    }

    public function item(
        mixed $content,
        int $status = Response::HTTP_OK,
        array $headers = [],
        int $options = 0
    ): JsonResponse {
        return $this->responseFactory->json(
            $this->normalizeContent($content),
            $status,
            $headers,
            $options
        );
    }

    // public function response($content, $status, $message): JsonResponse
    // {
    //     $content = $this->normalizeContent($content, $status, $message);

    //     // if ($this->logger and to_bool(Config::get('app.request_log'))) {
    //     //     $this->emitLoggingRequestEvent($content, $status, $message);
    //     // }

    //     return $this->responseFactory->json($content, $status, $this->headers);
    // }

    // public function registerAuth(array $auth): self
    // {
    //     $this->auth = array_merge($this->auth, $auth);

    //     return $this;
    // }

    // public function collection(
    //     mixed $content,
    //     int $status = Response::HTTP_OK,
    //     ?string $message = null
    // ): JsonResponse {
    //     return $this->response($content, $status, $message);
    // }

    // public function item(
    //     mixed $content,
    //     int $statusCode = Response::HTTP_OK,
    //     ?string $message = null
    // ): JsonResponse {
    //     if ($this->isEmptyContent($content) and Response::HTTP_OK === $statusCode) {
    //         $content = null;
    //         $statusCode = Response::HTTP_NOT_FOUND;
    //     }

    //     return $this->response($content, $statusCode, $message);
    // }

    // public function stored(mixed $content, ?string $message = null): JsonResponse
    // {
    //     return $this->item($content, Response::HTTP_CREATED, $message);
    // }

    // public function updated(mixed $content, ?string $message = null): JsonResponse
    // {
    //     return $this->item($content, Response::HTTP_ACCEPTED, $message);
    // }

    // public function destroyed(mixed $content, ?string $message = null): JsonResponse
    // {
    //     return $this->updated($content, $message);
    // }

    // public function ok(mixed $content = null): JsonResponse
    // {
    //     return $this->collection($content);
    // }

    // /**
    //  * Captura las excepciones.
    //  *
    //  * @param \Illuminate\Validation\ValidationException|\Joalvm\Utils\Exceptions\HttpException $ex
    //  */
    // public function catch(\Throwable $ex)
    // {
    //     $httpCode = $ex->getCode();
    //     $message = $ex->getMessage();
    //     $content = null;

    //     $this->headers = $this->getHeaders($ex);
    //     $this->exception = $ex;

    //     if ($this->isDeveloper or App::environment('local')) {
    //         $this->debug = [
    //             'file' => $ex->getFile() ?? '-',
    //             'line' => $ex->getLine() ?? '-',
    //             'trace' => $this->filterExceptionTrace($ex),
    //         ];
    //     }

    //     [
    //         'http_code' => $httpCode,
    //         'message' => $message,
    //         'content' => $content
    //     ] = $this->handleParamsFromException($ex);

    //     return $this->response($content, $httpCode, $message);
    // }

    // public function isDeveloper(bool $isDevelop = true): self
    // {
    //     $this->isDeveloper = $isDevelop;

    //     return $this;
    // }

    // public function setLog(bool $log = true): self
    // {
    //     $this->logger = $log;

    //     return $this;
    // }

    // /**
    //  * Is response successful?
    //  *
    //  * @final
    //  */
    // public function isSuccessfulCode(int $statusCode): bool
    // {
    //     return $statusCode >= 200 && $statusCode < 300;
    // }

    // // private function emitLoggingRequestEvent(
    // //     array $content,
    // //     int $status,
    // //     ?string $message = null
    // // ): void {
    // //     if (App::runningInConsole()) {
    // //         return;
    // //     }

    // //     $info = new RequestInfo($this->request);

    // //     $info->setContent($content)
    // //         ->setMessage($message ?? Response::$statusTexts[$status])
    // //         ->setStatus($status)
    // //         ->setException($this->exception)
    // //     ;

    // //     LoggingRequestEvent::dispatch($info->get(), $this->auth);
    // // }

    // private function handleParamsFromException(\Throwable $ex): array
    // {
    //     if ($ex instanceof \PDOException and App::isProduction()) {
    //         return [
    //             'http_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
    //             'message' => Lang::get('core::database.default'),
    //             'content' => null,
    //         ];
    //     }

    //     if (
    //         $ex instanceof \Illuminate\Validation\ValidationException
    //         || $ex instanceof \Joalvm\Utils\Exceptions\UnprocessableEntityException
    //         || $ex instanceof \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
    //     ) {
    //         return [
    //             'http_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
    //             'message' => Lang::get($ex->getMessage()),
    //             'content' => $ex->errors(),
    //         ];
    //     }

    //     // Cuando se usa el metodos findOrFail de eloquent
    //     if ($ex instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
    //         $resourceName = implode(
    //             ' ',
    //             Str::ucsplit(Str::afterLast($ex->getModel(), '\\'))
    //         );

    //         return [
    //             'http_code' => Response::HTTP_NOT_FOUND,
    //             'message' => Response::$statusTexts[Response::HTTP_NOT_FOUND],
    //             'content' => null,
    //         ];
    //     }

    //     if ($ex instanceof \Joalvm\Utils\Exceptions\HttpException) {
    //         return [
    //             'http_code' => $ex->getStatusCode(),
    //             'message' => Lang::get($ex->getMessage()),
    //             'content' => !$ex->errors() ? null : $ex->errors(),
    //         ];
    //     }

    //     // Las excepciones de laravel guardan los codigo en en el metodo getStatusCode
    //     if ($ex instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
    //         return [
    //             'http_code' => $ex->getStatusCode(),
    //             'message' => Lang::get($ex->getMessage()),
    //             'content' => null,
    //         ];
    //     }

    //     $httpCode = $ex->getCode();

    //     if (is_string($ex->getCode()) or $httpCode < 100 or $httpCode > 599) {
    //         $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
    //     }

    //     return [
    //         'http_code' => $httpCode,
    //         'message' => Lang::get($ex->getMessage()),
    //         'content' => null,
    //     ];
    // }

    private function getHeaders(\Throwable $exception): array
    {
        $headers = [];

        if ($exception instanceof \Joalvm\Utils\Exceptions\HttpException) {
            $headers = $exception->getHeaders();
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $headers = $exception->response?->headers?->all() ?? [];
        }

        return $headers;
    }

    /**
     * Maneja la estructura de la respuesta.
     */
    private function normalizeContent(mixed $content): mixed
    {
        if ($content instanceof Collection) {
            $meta = [];

            if ($content->isPaginate()) {
                return ['data' => $content->all(), ...$content->getMetadata()];
            }

            return $content->all();
        }

        return $content;
    }

    // /**
    //  * Añade los datos de debug a la respuesta.
    //  * Si la aplicación está en modo debug, se añaden los datos de debug.
    //  */
    // private function setDebugToContent(array $body): array
    // {
    //     if ($this->debug) {
    //         $body['debug'] = $this->debug;
    //     }

    //     return $body;
    // }

    // private function isEmptyContent(mixed $content): bool
    // {
    //     if ($content instanceof \Countable) {
    //         return 0 === count($content);
    //     }

    //     if (is_object($content) and method_exists($content, 'isEmpty')) {
    //         return $content->isEmpty();
    //     }

    //     return empty($content);
    // }

    // private function getUniqueRequestId(): array
    // {
    //     if ($this->request->server->has('UNIQUE_ID')) {
    //         return ['id' => $this->request->server('UNIQUE_ID')];
    //     }

    //     if ($this->request->server->has('HTTP_X_REQUEST_ID')) {
    //         return ['id' => $this->request->server('HTTP_X_REQUEST_ID')];
    //     }

    //     // AWS ELB
    //     if ($this->request->server->has('HTTP_X_AMZN_TRACE_ID')) {
    //         return ['id' => $this->request->server('HTTP_X_AMZN_TRACE_ID')];
    //     }

    //     return [];
    // }

    // private function filterExceptionTrace(\Throwable $exception): array
    // {
    //     return array_map(
    //         function ($value) {
    //             $filepath = ltrim(
    //                 str_replace(
    //                     'vendor' . DIRECTORY_SEPARATOR,
    //                     '',
    //                     Arr::get($value, 'file', '')
    //                 ),
    //                 '/'
    //             );

    //             return sprintf(
    //                 '%s(:%s) %s%s%s()',
    //                 $filepath,
    //                 Arr::get($value, 'line', ''),
    //                 Arr::get($value, 'class', ''),
    //                 Arr::get($value, 'type', ''),
    //                 Arr::get($value, 'function', ''),
    //             );
    //         },
    //         $exception->getTrace() ?? []
    //     );
    // }
}

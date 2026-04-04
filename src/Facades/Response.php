<?php

// @phpcs:disable

namespace Joalvm\Utils\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Joalvm\Utils\Response as ResponseBuilder;

/**
 * @method static JsonResponse       collection(mixed $content, int $status = BaseResponse::HTTP_OK, array $headers = [])                 Devuelve la estructura de una respuesta para una colección de datos.
 * @method static JsonResponse       item(mixed $content, int $statusCode = BaseResponse::HTTP_OK, array $headers = [])                   Devuelve la estructura de una respuesta para un elemento de datos.
 * @method static JsonResponse       stored(mixed $content, array $headers = [])                                                          Devuelve la estructura de una respuesta para un elemento de datos creado.
 * @method static JsonResponse       updated(mixed $content, array $headers = [])                                                         Devuelve la estructura de una respuesta para un elemento de datos actualizado.
 * @method static JsonResponse       destroyed(mixed $content, array $headers = [])                                                       Devuelve la estructura de una respuesta para un elemento de datos eliminado.
 * @method static JsonResponse       ok(mixed $content = null, array $headers = [])                                                       Devuelve la estructura de una respuesta para una petición exitosa.
 * @method static JsonResponse       error(string $message, int $statusCode = 400, mixed $content = null, array $headers = [])            Devuelve la estructura de una respuesta para un error.
 * @method static JsonResponse       catch(\Throwable $exception)                                                                         Devuelve la estructura de una respuesta para una excepción.
 * @method static BinaryFileResponse download(string $file, string $name = null, array $headers = [], string $disposition = 'attachment') Devuelve una respuesta para descargar un archivo.
 *
 * @see Joalvm\Utils\Response
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseBuilder::class;
    }
}

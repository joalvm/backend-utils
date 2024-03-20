<?php

namespace Joalvm\Utils\Facades;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Facade;
use Joalvm\Utils\ResponseManager;

/**
 * @method static JsonResponse collection(mixed $content, int $status = 200, array $headers = [], int $options = 0) Create a new JSON response instance from a collection.
 */
class Response extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseManager::class;
    }
}

<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Joalvm\Utils\Facades\Response;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(Response::class, function (Application $app) {
            return new ResponseManager(
                $app->get(\Illuminate\Contracts\Routing\ResponseFactory::class),
                $app->get(\Illuminate\Http\Request::class),
            );
        });
    }

    public function register()
    {
    }
}

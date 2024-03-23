<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('joalvm.response', function (Application $app) {
            return new ResponseManager(
                $app->get(\Illuminate\Contracts\Routing\ResponseFactory::class),
                $app->get('config')->get('app.debug')
            );
        });
    }

    public function register()
    {
    }
}

<?php

namespace Joalvm\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    public function register()
    {
        $this->registerFacades();
        $this->registerCommands();
    }

    private function registerCommands()
    {
        $this->commands([
            Commands\GenerateDatabaseCommand::class,
        ]);
    }

    private function registerFacades()
    {
        $this->app->singleton(Response::class, function (Application $app) {
            return new Response(
                $app->get(ResponseFactory::class),
                $app->basePath(),
                $app->get('config')->get('app.debug', false)
            );
        });
    }
}

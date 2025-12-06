<?php

namespace Craveva\Craveva;

use Craveva\Craveva\Commands\MigrateCheckCommand;
use Illuminate\Support\ServiceProvider;

class CravevaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/craveva.php','craveva'
        );
        $this->commands([
            MigrateCheckCommand::class
        ]);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->publishFiles();
    }

    public function publishFiles()
    {
        $this->publishes([
            __DIR__ . '/Config/craveva.php' => config_path('craveva.php'),
        ]);

        $this->publishes([
            __DIR__ . '/Migrations/' => database_path('migrations')
        ], 'migrations');

        $this->publishes([
            __DIR__ . '/Views' => resource_path('views/vendor/craveva'),
        ]);

    }
}

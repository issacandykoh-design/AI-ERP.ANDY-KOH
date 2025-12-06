<?php

namespace Open\RestAPI\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Open\RestAPI\ApiRouter;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the API router
        $this->app->singleton('api.router', function ($app) {
            return new ApiRouter($app['router']);
        });

        // Register the API router registrar
        $this->app->singleton('api.registrar', function ($app) {
            return new ApiRouter($app['router']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware
        $this->registerMiddleware();

        // Register exception handler
        $this->registerExceptionHandler();
    }

    /**
     * Register API middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        // Register API authentication middleware
        $router->aliasMiddleware('api.auth', \Open\RestAPI\Middleware\ApiAuthMiddleware::class);
    }

    /**
     * Register exception handler for API exceptions.
     */
    protected function registerExceptionHandler(): void
    {
        // Temporarily commented out to avoid conflicts
        // $this->app->bind(
        //     \Illuminate\Contracts\Debug\ExceptionHandler::class,
        //     function ($app) {
        //         return new \Open\RestAPI\Exceptions\ApiExceptionHandler(
        //             $app[\Illuminate\Contracts\Debug\ExceptionHandler::class]
        //         );
        //     }
        // );
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'api.router',
            'api.registrar',
        ];
    }
}

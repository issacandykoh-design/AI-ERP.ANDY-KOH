<?php

namespace Open\RestAPI\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Routing\RouteRegistrar group(array $attributes, \Closure|string $routes)
 * @method static \Illuminate\Routing\Route get(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route post(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route put(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route patch(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route delete(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route options(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route any(string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\Route match(array|string $methods, string $uri, array|string|callable|null $action = null)
 * @method static \Illuminate\Routing\PendingResourceRegistration resource(string $name, string $controller, array $options = [])
 * @method static \Illuminate\Routing\PendingResourceRegistration apiResource(string $name, string $controller, array $options = [])
 * @method static void middleware(array|string|null $middleware)
 * @method static void prefix(string $prefix)
 * @method static void name(string $name)
 * @method static void namespace(string $namespace)
 * @method static void domain(string $domain)
 * @method static void where(array|string $name, string $expression = null)
 */
class ApiRoute extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'api.router';
    }
}

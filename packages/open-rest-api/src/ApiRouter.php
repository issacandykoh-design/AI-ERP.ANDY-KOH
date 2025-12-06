<?php

namespace Open\RestAPI;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

class ApiRouter
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Create a route group with API-specific defaults.
     */
    public function group(array $attributes, $routes)
    {
        // Set default API middleware and prefix if not specified
        $attributes = array_merge([
            'prefix' => 'api',
            'middleware' => ['api'],
        ], $attributes);

        return $this->router->group($attributes, $routes);
    }

    /**
     * Register a GET route.
     */
    public function get($uri, $action = null)
    {
        return $this->router->get($uri, $action);
    }

    /**
     * Register a POST route.
     */
    public function post($uri, $action = null)
    {
        return $this->router->post($uri, $action);
    }

    /**
     * Register a PUT route.
     */
    public function put($uri, $action = null)
    {
        return $this->router->put($uri, $action);
    }

    /**
     * Register a PATCH route.
     */
    public function patch($uri, $action = null)
    {
        return $this->router->patch($uri, $action);
    }

    /**
     * Register a DELETE route.
     */
    public function delete($uri, $action = null)
    {
        return $this->router->delete($uri, $action);
    }

    /**
     * Register an OPTIONS route.
     */
    public function options($uri, $action = null)
    {
        return $this->router->options($uri, $action);
    }

    /**
     * Register a route that responds to any HTTP verb.
     */
    public function any($uri, $action = null)
    {
        return $this->router->any($uri, $action);
    }

    /**
     * Register a route that responds to multiple HTTP verbs.
     */
    public function match($methods, $uri, $action = null)
    {
        return $this->router->match($methods, $uri, $action);
    }

    /**
     * Register a resource route.
     */
    public function resource($name, $controller, array $options = [])
    {
        return $this->router->resource($name, $controller, $options);
    }

    /**
     * Register an API resource route.
     */
    public function apiResource($name, $controller, array $options = [])
    {
        return $this->router->apiResource($name, $controller, $options);
    }

    /**
     * Add middleware to subsequent routes.
     */
    public function middleware($middleware)
    {
        return $this->router->middleware($middleware);
    }

    /**
     * Add a prefix to subsequent routes.
     */
    public function prefix($prefix)
    {
        return $this->router->prefix($prefix);
    }

    /**
     * Add a name prefix to subsequent routes.
     */
    public function name($name)
    {
        return $this->router->name($name);
    }

    /**
     * Add a namespace to subsequent routes.
     */
    public function namespace($namespace)
    {
        return $this->router->namespace($namespace);
    }

    /**
     * Add a domain constraint to subsequent routes.
     */
    public function domain($domain)
    {
        return $this->router->domain($domain);
    }

    /**
     * Add a where constraint to subsequent routes.
     */
    public function where($name, $expression = null)
    {
        return $this->router->where($name, $expression);
    }

    /**
     * Dynamically handle calls to the router instance.
     */
    public function __call($method, $parameters)
    {
        return $this->router->{$method}(...$parameters);
    }
}

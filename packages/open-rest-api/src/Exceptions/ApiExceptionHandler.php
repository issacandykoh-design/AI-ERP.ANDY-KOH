<?php

namespace Open\RestAPI\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Open\RestAPI\ApiResponse;

class ApiExceptionHandler
{
    protected $handler;

    public function __construct(ExceptionHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception)
    {
        return $this->handler->report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        // Only handle API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            return $this->renderApiException($request, $exception);
        }

        return $this->handler->render($request, $exception);
    }

    /**
     * Render API exception response.
     */
    protected function renderApiException(Request $request, Throwable $exception)
    {
        // Handle API exceptions
        if ($exception instanceof ApiException) {
            return $exception->render();
        }

        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            return ApiResponse::validationError($exception->errors(), $exception->getMessage());
        }

        // Handle authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return ApiResponse::unauthorized($exception->getMessage());
        }

        // Handle model not found exceptions
        if ($exception instanceof ModelNotFoundException) {
            return ApiResponse::notFound('Resource not found');
        }

        // Handle 404 exceptions
        if ($exception instanceof NotFoundHttpException) {
            return ApiResponse::notFound('Endpoint not found');
        }

        // Handle method not allowed exceptions
        if ($exception instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error('Method not allowed', [], 405);
        }

        // Handle general exceptions
        $message = config('app.debug') ? $exception->getMessage() : 'Internal server error';
        $statusCode = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 500;

        return ApiResponse::error($message, [], $statusCode);
    }

    /**
     * Determine if the exception should be reported.
     */
    public function shouldReport(Throwable $exception)
    {
        return $this->handler->shouldReport($exception);
    }

    /**
     * Dynamically handle calls to the underlying handler.
     */
    public function __call($method, $parameters)
    {
        return $this->handler->{$method}(...$parameters);
    }
}

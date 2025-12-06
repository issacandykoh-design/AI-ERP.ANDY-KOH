<?php

namespace Open\RestAPI\Exceptions;

use Exception;
use Open\RestAPI\ApiResponse;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected $statusCode;
    protected $errors;

    public function __construct(string $message = 'API Error', int $statusCode = 400, array $errors = [], Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->errors, $this->statusCode);
    }

    /**
     * Get the status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

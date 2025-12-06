<?php

namespace Open\RestAPI\Exceptions;

class UnauthorizedException extends ApiException
{
    public function __construct(string $message = 'Unauthorized', array $errors = [])
    {
        parent::__construct($message, 401, $errors);
    }
}

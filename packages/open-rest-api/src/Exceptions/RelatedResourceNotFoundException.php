<?php

namespace Open\RestAPI\Exceptions;

class RelatedResourceNotFoundException extends ApiException
{
    public function __construct(string $message = 'Related resource not found', array $errors = [])
    {
        parent::__construct($message, 404, $errors);
    }
}

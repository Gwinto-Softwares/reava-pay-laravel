<?php

namespace ReavaPay\Exceptions;

class ValidationException extends ReavaPayException
{
    protected array $errors;

    public function __construct(string $message, int $statusCode, array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct($message, $statusCode);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

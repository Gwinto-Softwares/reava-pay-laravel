<?php

namespace ReavaPay\Exceptions;

use Exception;

class ReavaPayException extends Exception
{
    protected int $statusCode;

    public function __construct(string $message, int $statusCode = 0, ?\Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

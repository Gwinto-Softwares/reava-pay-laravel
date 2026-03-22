<?php

namespace ReavaPay\Exceptions;

class RateLimitException extends ReavaPayException
{
    protected int $retryAfter;

    public function __construct(string $message, int $statusCode, int $retryAfter = 60)
    {
        $this->retryAfter = $retryAfter;
        parent::__construct($message, $statusCode);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}

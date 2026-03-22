<?php

namespace ReavaPay\Http;

use Illuminate\Support\Facades\Http;
use ReavaPay\Exceptions\AuthenticationException;
use ReavaPay\Exceptions\RateLimitException;
use ReavaPay\Exceptions\ReavaPayException;
use ReavaPay\Exceptions\ValidationException;

class ApiClient
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;

    public function __construct(string $apiKey, string $baseUrl, int $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
    }

    public function get(string $endpoint, array $query = []): object
    {
        return $this->request('GET', $endpoint, query: $query);
    }

    public function post(string $endpoint, array $data = []): object
    {
        return $this->request('POST', $endpoint, body: $data);
    }

    public function put(string $endpoint, array $data = []): object
    {
        return $this->request('PUT', $endpoint, body: $data);
    }

    public function patch(string $endpoint, array $data = []): object
    {
        return $this->request('PATCH', $endpoint, body: $data);
    }

    public function delete(string $endpoint): object
    {
        return $this->request('DELETE', $endpoint);
    }

    private function request(
        string $method,
        string $endpoint,
        array $body = [],
        array $query = [],
    ): object {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $pending = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'User-Agent' => 'ReavaPay-Laravel/1.0.0',
        ])->timeout($this->timeout);

        $response = match (strtoupper($method)) {
            'GET' => $pending->get($url, $query),
            'POST' => $pending->post($url, $body),
            'PUT' => $pending->put($url, $body),
            'PATCH' => $pending->patch($url, $body),
            'DELETE' => $pending->delete($url),
        };

        $statusCode = $response->status();
        $decoded = $response->object();

        if ($statusCode >= 400) {
            $this->handleError($statusCode, $decoded);
        }

        return $decoded;
    }

    private function handleError(int $statusCode, ?object $response): never
    {
        $message = $response->message ?? $response->error ?? 'Unknown API error';

        match (true) {
            $statusCode === 401 => throw new AuthenticationException($message, $statusCode),
            $statusCode === 422 => throw new ValidationException(
                $message,
                $statusCode,
                (array) ($response->errors ?? []),
            ),
            $statusCode === 429 => throw new RateLimitException(
                $message,
                $statusCode,
                (int) ($response->retry_after ?? 60),
            ),
            default => throw new ReavaPayException($message, $statusCode),
        };
    }
}

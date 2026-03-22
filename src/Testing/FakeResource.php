<?php

namespace ReavaPay\Testing;

class FakeResource
{
    public function __construct(
        private string $name,
        private array $responses,
        private array &$recordedCalls,
    ) {
    }

    public function create(array $params = []): object
    {
        $this->recordedCalls[] = ['method' => "{$this->name}.create", 'params' => $params];

        return $this->findResponse("{$this->name}/create", [
            'id' => rand(1, 9999),
            'status' => 'created',
        ]);
    }

    public function get(string|int $id): object
    {
        $this->recordedCalls[] = ['method' => "{$this->name}.get", 'params' => ['id' => $id]];

        return $this->findResponse("{$this->name}/{$id}", [
            'id' => $id,
            'status' => 'active',
        ]);
    }

    public function update(string|int $id, array $params = []): object
    {
        $this->recordedCalls[] = ['method' => "{$this->name}.update", 'params' => array_merge(['id' => $id], $params)];

        return $this->findResponse("{$this->name}/{$id}", [
            'id' => $id,
            'status' => 'updated',
        ]);
    }

    public function list(array $params = []): object
    {
        $this->recordedCalls[] = ['method' => "{$this->name}.list", 'params' => $params];

        return $this->findResponse("{$this->name}", [
            'data' => [],
            'total' => 0,
        ]);
    }

    private function findResponse(string $key, array $default): object
    {
        if (isset($this->responses[$key])) {
            return $this->responses[$key];
        }

        foreach ($this->responses as $pattern => $response) {
            if (str_contains($pattern, '*') && fnmatch($pattern, $key)) {
                return $response;
            }
        }

        return (object) $default;
    }
}

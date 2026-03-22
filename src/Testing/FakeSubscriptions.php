<?php

namespace ReavaPay\Testing;

class FakeSubscriptions
{
    public function __construct(
        private array $responses,
        private array &$recordedCalls,
    ) {
    }

    public function create(int|string $planId, array $params = []): object
    {
        $this->recordedCalls[] = ['method' => 'subscriptions.create', 'params' => array_merge(['plan_id' => $planId], $params)];
        return (object) ['id' => rand(1, 9999), 'status' => 'active', 'plan_id' => $planId];
    }

    public function get(int|string $planId, int|string $subId): object
    {
        $this->recordedCalls[] = ['method' => 'subscriptions.get', 'params' => ['plan_id' => $planId, 'id' => $subId]];
        return (object) ['id' => $subId, 'plan_id' => $planId, 'status' => 'active', 'progress_percentage' => 0];
    }

    public function list(int|string $planId, array $params = []): object
    {
        $this->recordedCalls[] = ['method' => 'subscriptions.list', 'params' => array_merge(['plan_id' => $planId], $params)];
        return (object) ['data' => [], 'total' => 0];
    }

    public function cancel(int|string $planId, int|string $subId, string|array $reason = ''): object
    {
        $this->recordedCalls[] = ['method' => 'subscriptions.cancel', 'params' => ['plan_id' => $planId, 'id' => $subId, 'reason' => $reason]];
        return (object) ['id' => $subId, 'status' => 'cancelled'];
    }

    public function payments(int|string $planId, int|string $subId, array $params = []): object
    {
        $this->recordedCalls[] = ['method' => 'subscriptions.payments', 'params' => ['plan_id' => $planId, 'id' => $subId]];
        return (object) ['data' => [], 'total' => 0];
    }
}

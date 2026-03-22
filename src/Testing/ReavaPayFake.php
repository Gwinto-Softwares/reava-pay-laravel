<?php

namespace ReavaPay\Testing;

use PHPUnit\Framework\Assert;
use ReavaPay\Resources\Webhooks;

class ReavaPayFake
{
    private array $responses;
    private array $recordedCollections = [];
    private array $recordedPayouts = [];
    private array $recordedCalls = [];

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function collect(array $params): object
    {
        $this->recordedCollections[] = $params;
        $this->recordedCalls[] = ['method' => 'collect', 'params' => $params];

        return $this->findResponse('collections/create', [
            'status' => 'processing',
            'reference' => 'TXN-fake-' . uniqid(),
            'transaction_id' => 'txn_fake_' . uniqid(),
        ]);
    }

    public function payout(array $params): object
    {
        $this->recordedPayouts[] = $params;
        $this->recordedCalls[] = ['method' => 'payout', 'params' => $params];

        return $this->findResponse('payouts/create', [
            'status' => 'processing',
            'reference' => 'PAY-fake-' . uniqid(),
        ]);
    }

    public function me(): object
    {
        $this->recordedCalls[] = ['method' => 'me', 'params' => []];

        return $this->findResponse('me', [
            'business_name' => 'Test Merchant',
            'email' => 'test@example.com',
            'status' => 'active',
        ]);
    }

    public function collections(): FakeResource
    {
        return new FakeResource('collections', $this->responses, $this->recordedCalls);
    }

    public function payouts(): FakeResource
    {
        return new FakeResource('payouts', $this->responses, $this->recordedCalls);
    }

    public function transactions(): FakeResource
    {
        return new FakeResource('transactions', $this->responses, $this->recordedCalls);
    }

    public function floatAccounts(): FakeResource
    {
        return new FakeResource('float-accounts', $this->responses, $this->recordedCalls);
    }

    public function settlements(): FakeResource
    {
        return new FakeResource('settlements', $this->responses, $this->recordedCalls);
    }

    public function customers(): FakeResource
    {
        return new FakeResource('customers', $this->responses, $this->recordedCalls);
    }

    public function recurring(): FakeRecurring
    {
        return new FakeRecurring($this->responses, $this->recordedCalls);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks();
    }

    // ── Assertions ──────────────────────────────────────

    public function assertCollected(?callable $callback = null): void
    {
        Assert::assertNotEmpty($this->recordedCollections, 'No collections were recorded.');

        if ($callback) {
            $matched = array_filter($this->recordedCollections, $callback);
            Assert::assertNotEmpty($matched, 'No collections matched the given callback.');
        }
    }

    public function assertNothingCollected(): void
    {
        Assert::assertEmpty($this->recordedCollections, 'Collections were unexpectedly recorded.');
    }

    public function assertPaidOut(?callable $callback = null): void
    {
        Assert::assertNotEmpty($this->recordedPayouts, 'No payouts were recorded.');

        if ($callback) {
            $matched = array_filter($this->recordedPayouts, $callback);
            Assert::assertNotEmpty($matched, 'No payouts matched the given callback.');
        }
    }

    public function assertNothingPaidOut(): void
    {
        Assert::assertEmpty($this->recordedPayouts, 'Payouts were unexpectedly recorded.');
    }

    private function findResponse(string $key, array $default): object
    {
        // Match exact key first, then wildcard patterns
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

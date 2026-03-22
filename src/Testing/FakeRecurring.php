<?php

namespace ReavaPay\Testing;

class FakeRecurring
{
    public function __construct(
        private array $responses,
        private array &$recordedCalls,
    ) {
    }

    public function plans(): FakeResource
    {
        return new FakeResource('recurring/plans', $this->responses, $this->recordedCalls);
    }

    public function subscriptions(): FakeSubscriptions
    {
        return new FakeSubscriptions($this->responses, $this->recordedCalls);
    }
}

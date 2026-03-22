<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Recurring
{
    public readonly RecurringPlans $plans;
    public readonly RecurringSubscriptions $subscriptions;

    public function __construct(ApiClient $http)
    {
        $this->plans = new RecurringPlans($http);
        $this->subscriptions = new RecurringSubscriptions($http);
    }
}

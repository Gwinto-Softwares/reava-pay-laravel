<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class RecurringPlans
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a recurring plan.
     *
     * @param array $params {
     *     @type string  $name                    Plan name (required)
     *     @type string  $type                    'subscription', 'savings_goal', 'bill', 'auto_disbursement' (required)
     *     @type string  $currency_code           3-letter currency code (required)
     *     @type float   $amount                  Amount per cycle (required)
     *     @type string  $frequency               'daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'semi_annual', 'annual', 'custom' (required)
     *     @type float   $target_amount           Target amount (for savings_goal)
     *     @type string  $description             Plan description
     *     @type int     $frequency_interval_days Custom interval in days
     *     @type int     $max_cycles              Maximum charge cycles
     *     @type bool    $auto_charge             Whether to auto-charge subscribers
     *     @type string  $auto_charge_channel     'mpesa', 'card', 'any'
     *     @type string  $callback_url            Webhook URL for plan events
     *     @type array   $metadata                Custom metadata
     * }
     */
    public function create(array $params): object
    {
        $res = $this->http->post('recurring/plans', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a recurring plan by UUID.
     *
     * Includes subscribers_count and active_subscribers_count.
     */
    public function get(string $id): object
    {
        $res = $this->http->get("recurring/plans/{$id}");
        return $res->data ?? $res;
    }

    /**
     * Update a recurring plan by UUID.
     *
     * @param string $id     Plan UUID
     * @param array  $params {
     *     @type string $name                    Plan name
     *     @type string $description             Plan description
     *     @type float  $amount                  Amount per cycle
     *     @type float  $target_amount           Target amount (for savings_goal)
     *     @type int    $max_cycles              Maximum charge cycles
     *     @type bool   $auto_charge             Whether to auto-charge subscribers
     *     @type string $auto_charge_channel     'mpesa', 'card', 'any'
     *     @type string $callback_url            Webhook URL for plan events
     *     @type array  $metadata                Custom metadata
     * }
     * @return object Updated plan
     */
    public function update(string $id, array $params): object
    {
        $res = $this->http->put("recurring/plans/{$id}", $params);
        return $res->data ?? $res;
    }

    /**
     * List recurring plans with optional filters.
     *
     * @param array $params {
     *     @type string $type     Filter by type
     *     @type bool   $active   Filter by active status
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('recurring/plans', $params);
    }
}

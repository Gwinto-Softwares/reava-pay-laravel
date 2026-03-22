<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class RecurringSubscriptions
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a subscription for a plan.
     *
     * @param string $planId Plan UUID
     * @param array $params {
     *     @type string $customer_name       Subscriber name (required)
     *     @type string $customer_identifier Subscriber phone/email for charging (required)
     *     @type string $payment_method      'mpesa' or 'card' (required)
     *     @type string $customer_email      Subscriber email
     *     @type string $customer_phone      Subscriber phone
     *     @type float  $amount_override     Override the plan amount
     *     @type array  $metadata            Custom metadata
     * }
     */
    public function create(string $planId, array $params): object
    {
        $res = $this->http->post("recurring/plans/{$planId}/subscriptions", $params);
        return $res->data ?? $res;
    }

    /**
     * Get a subscription by UUID.
     *
     * Returns progress_percentage, total_paid, total_due, current_cycle,
     * failed_charge_count, next_charge_at, last_payment_at.
     */
    public function get(string $planId, string $subscriptionId): object
    {
        $res = $this->http->get("recurring/plans/{$planId}/subscriptions/{$subscriptionId}");
        return $res->data ?? $res;
    }

    /**
     * List subscriptions for a plan.
     *
     * @param array $params {
     *     @type string $status   Filter: 'active', 'paused', 'cancelled', 'completed', 'trial'
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(string $planId, array $params = []): object
    {
        return $this->http->get("recurring/plans/{$planId}/subscriptions", $params);
    }

    /**
     * Cancel a subscription.
     *
     * Sets status to 'cancelled' and clears next_charge_at.
     *
     * @param string       $planId         Plan UUID
     * @param string       $subscriptionId Subscription UUID
     * @param string|array $reason         Cancellation reason
     */
    public function cancel(string $planId, string $subscriptionId, string|array $reason = ''): object
    {
        $data = is_array($reason) ? $reason : ['reason' => $reason];
        $res = $this->http->post("recurring/plans/{$planId}/subscriptions/{$subscriptionId}/cancel", $data);
        return $res->data ?? $res;
    }

    /**
     * Pause a subscription.
     *
     * Sets status to 'paused' and clears next_charge_at.
     *
     * @param string $planId         Plan UUID
     * @param string $subscriptionId Subscription UUID
     * @return object Updated subscription
     */
    public function pause(string $planId, string $subscriptionId): object
    {
        $res = $this->http->post("recurring/plans/{$planId}/subscriptions/{$subscriptionId}/pause");
        return $res->data ?? $res;
    }

    /**
     * Resume a paused subscription.
     *
     * Sets status back to 'active' and schedules next_charge_at.
     *
     * @param string $planId         Plan UUID
     * @param string $subscriptionId Subscription UUID
     * @return object Updated subscription
     */
    public function resume(string $planId, string $subscriptionId): object
    {
        $res = $this->http->post("recurring/plans/{$planId}/subscriptions/{$subscriptionId}/resume");
        return $res->data ?? $res;
    }

    /**
     * List payments for a subscription.
     *
     * Returns cycle_number, amount_expected, amount_paid, status,
     * payment_method, provider_reference, failure_reason, due_at, paid_at.
     */
    public function payments(string $planId, string $subscriptionId, array $params = []): object
    {
        return $this->http->get("recurring/plans/{$planId}/subscriptions/{$subscriptionId}/payments", $params);
    }
}

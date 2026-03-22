<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Payouts
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Initiate a payout (B2C disbursement).
     *
     * @param array $params {
     *     @type float  $amount           Amount to send (min 10, max 500000)
     *     @type string $currency         3-letter currency code (default: KES)
     *     @type string $recipient_phone  Recipient M-Pesa phone number
     *     @type string $recipient_name   Recipient display name
     *     @type int    $float_account_id Float account to debit
     *     @type string $description      Payout reason/description
     *     @type string $callback_url     Webhook URL for payout status
     *     @type array  $metadata         Custom key-value metadata
     * }
     * @return object Response with payout_id, reference, status, amount, charge, net_debit, requires_approval
     */
    public function create(array $params): object
    {
        $res = $this->http->post('payouts', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a payout by UUID.
     *
     * @param string $id Payout UUID
     * @return object Payout details
     */
    public function get(string $id): object
    {
        $res = $this->http->get("payouts/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List payouts with optional filters.
     *
     * @param array $params {
     *     @type string $status   Filter: 'pending', 'processing', 'completed', 'failed'
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('payouts', $params);
    }
}

<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Collections
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Initiate a collection (M-Pesa STK Push, Card, or Wallet).
     *
     * @param array $params {
     *     @type float  $amount          Amount to collect (min 10, max 500000)
     *     @type string $currency        3-letter currency code (default: KES)
     *     @type string $channel         Payment channel: 'mpesa' or 'card'
     *     @type string $phone           Customer phone (required for M-Pesa)
     *     @type string $email           Customer email (required for Card)
     *     @type string $account_reference Your internal reference
     *     @type string $description     Payment description
     *     @type string $callback_url    Webhook URL for payment status
     *     @type array  $metadata        Custom key-value metadata
     * }
     * @return object Response with status, reference, transaction_id, authorization_url (for card)
     */
    public function create(array $params): object
    {
        $res = $this->http->post('collections', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a collection by UUID.
     *
     * @param string $id Collection UUID
     * @return object Collection details
     */
    public function get(string $id): object
    {
        $res = $this->http->get("collections/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List collections with optional filters.
     *
     * @param array $params {
     *     @type string $status   Filter: 'pending', 'completed', 'failed'
     *     @type string $from     Start date (YYYY-MM-DD)
     *     @type string $to       End date (YYYY-MM-DD)
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('collections', $params);
    }
}

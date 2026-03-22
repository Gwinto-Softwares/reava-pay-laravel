<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Transactions
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Get a transaction by UUID or external reference.
     *
     * Returns full details including processor info when available.
     *
     * @param string $id Transaction UUID or external reference
     */
    public function get(string $id): object
    {
        $res = $this->http->get("transactions/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List all transactions (collections + payouts) with optional filters.
     *
     * @param array $params {
     *     @type string $type     Filter: 'collection' or 'payout'
     *     @type string $status   Filter: 'pending', 'processing', 'completed', 'failed'
     *     @type string $from     Start date (YYYY-MM-DD)
     *     @type string $to       End date (YYYY-MM-DD)
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('transactions', $params);
    }
}

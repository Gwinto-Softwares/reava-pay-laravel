<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Settlements
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Get a settlement by UUID.
     *
     * @param string $id Settlement UUID
     * @return object Settlement details
     */
    public function get(string $id): object
    {
        $res = $this->http->get("settlements/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List settlements with optional filters.
     *
     * @param array $params {
     *     @type string $status   Filter: 'pending', 'processing', 'completed'
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('settlements', $params);
    }
}

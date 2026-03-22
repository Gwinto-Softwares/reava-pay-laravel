<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class WebhookEndpoints
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a new webhook endpoint.
     *
     * @param array $params {
     *     @type string $url    Endpoint URL (required)
     *     @type array  $events List of event types to subscribe to (required)
     *     @type string $secret Signing secret (auto-generated if omitted)
     *     @type array  $metadata Custom key-value metadata
     * }
     * @return object Created webhook endpoint with secret
     */
    public function create(array $params): object
    {
        $res = $this->http->post('webhook-endpoints', $params);
        return $res->data ?? $res;
    }

    /**
     * List webhook endpoints.
     *
     * @param array $params {
     *     @type bool   $active   Filter by active status
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     * @return object Paginated list of webhook endpoints
     */
    public function list(array $params = []): object
    {
        return $this->http->get('webhook-endpoints', $params);
    }

    /**
     * Update a webhook endpoint by UUID.
     *
     * @param string $id     Webhook endpoint UUID
     * @param array  $params {
     *     @type string $url    Endpoint URL
     *     @type array  $events List of event types to subscribe to
     *     @type bool   $active Whether the endpoint is active
     *     @type array  $metadata Custom key-value metadata
     * }
     * @return object Updated webhook endpoint
     */
    public function update(string $id, array $params): object
    {
        $res = $this->http->put("webhook-endpoints/{$id}", $params);
        return $res->data ?? $res;
    }

    /**
     * Delete a webhook endpoint by UUID.
     *
     * @param string $id Webhook endpoint UUID
     * @return object Deletion confirmation
     */
    public function delete(string $id): object
    {
        $res = $this->http->delete("webhook-endpoints/{$id}");
        return $res->data ?? $res;
    }
}

<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class PaymentLinks
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a new payment link.
     *
     * @param array $params {
     *     @type string $name          Link name (required)
     *     @type float  $amount        Fixed amount (optional for flexible links)
     *     @type string $currency_code 3-letter currency code (required)
     *     @type string $description   Link description
     *     @type string $redirect_url  URL to redirect after payment
     *     @type string $callback_url  Webhook URL for payment events
     *     @type array  $metadata      Custom key-value metadata
     * }
     * @return object Created payment link with URL
     */
    public function create(array $params): object
    {
        $res = $this->http->post('payment-links', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a payment link by UUID.
     *
     * @param string $id Payment link UUID
     * @return object Payment link details
     */
    public function get(string $id): object
    {
        $res = $this->http->get("payment-links/{$id}");
        return $res->data ?? $res;
    }

    /**
     * Update a payment link by UUID.
     *
     * @param string $id     Payment link UUID
     * @param array  $params {
     *     @type string $name          Link name
     *     @type float  $amount        Fixed amount
     *     @type string $description   Link description
     *     @type string $redirect_url  URL to redirect after payment
     *     @type string $callback_url  Webhook URL for payment events
     *     @type bool   $active        Whether the link is active
     *     @type array  $metadata      Custom key-value metadata
     * }
     * @return object Updated payment link
     */
    public function update(string $id, array $params): object
    {
        $res = $this->http->put("payment-links/{$id}", $params);
        return $res->data ?? $res;
    }

    /**
     * List payment links with optional filters.
     *
     * @param array $params {
     *     @type bool   $active   Filter by active status
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     * @return object Paginated list of payment links
     */
    public function list(array $params = []): object
    {
        return $this->http->get('payment-links', $params);
    }
}

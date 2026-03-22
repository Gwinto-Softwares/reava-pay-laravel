<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Invoices
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a new invoice.
     *
     * @param array $params {
     *     @type string $customer_id   Customer UUID (required)
     *     @type string $currency_code 3-letter currency code (required)
     *     @type array  $items         Line items (required)
     *     @type string $due_date      Due date (YYYY-MM-DD)
     *     @type string $notes         Invoice notes
     *     @type array  $metadata      Custom key-value metadata
     * }
     * @return object Created invoice
     */
    public function create(array $params): object
    {
        $res = $this->http->post('invoices', $params);
        return $res->data ?? $res;
    }

    /**
     * Get an invoice by UUID.
     *
     * @param string $id Invoice UUID
     * @return object Invoice details
     */
    public function get(string $id): object
    {
        $res = $this->http->get("invoices/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List invoices with optional filters.
     *
     * @param array $params {
     *     @type string $status   Filter: 'draft', 'sent', 'paid', 'overdue', 'cancelled'
     *     @type string $from     Start date (YYYY-MM-DD)
     *     @type string $to       End date (YYYY-MM-DD)
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     * @return object Paginated list of invoices
     */
    public function list(array $params = []): object
    {
        return $this->http->get('invoices', $params);
    }

    /**
     * Send an invoice to the customer.
     *
     * @param string $id Invoice UUID
     * @return object Send confirmation
     */
    public function send(string $id): object
    {
        $res = $this->http->post("invoices/{$id}/send");
        return $res->data ?? $res;
    }
}

<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class Customers
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a new customer.
     *
     * @param array $params {
     *     @type string $name        Customer full name (required)
     *     @type string $email       Customer email
     *     @type string $phone       Customer phone
     *     @type string $company     Company name
     *     @type string $external_id Your internal customer ID
     *     @type array  $metadata    Custom key-value metadata
     * }
     * @return object Created customer
     */
    public function create(array $params): object
    {
        $res = $this->http->post('customers', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a customer by UUID.
     *
     * Returns extended details including address, city, country_code, tax_id,
     * external_id, total_paid, total_transactions, metadata.
     *
     * @param string $id Customer UUID
     */
    public function get(string $id): object
    {
        $res = $this->http->get("customers/{$id}");
        return $res->data ?? $res;
    }

    /**
     * Update a customer by UUID.
     *
     * @param string $id     Customer UUID
     * @param array  $params {
     *     @type string $name        Customer full name
     *     @type string $email       Customer email
     *     @type string $phone       Customer phone
     *     @type string $company     Company name
     *     @type string $external_id Your internal customer ID
     *     @type array  $metadata    Custom key-value metadata
     * }
     * @return object Updated customer
     */
    public function update(string $id, array $params): object
    {
        $res = $this->http->put("customers/{$id}", $params);
        return $res->data ?? $res;
    }

    /**
     * List customers with optional filters.
     *
     * @param array $params {
     *     @type string $status   Filter: 'active', 'inactive'
     *     @type string $search   Search by name, email, or phone
     *     @type int    $per_page Results per page (max 100, default 20)
     * }
     */
    public function list(array $params = []): object
    {
        return $this->http->get('customers', $params);
    }
}

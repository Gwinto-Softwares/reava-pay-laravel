<?php

namespace ReavaPay\Resources;

use ReavaPay\Http\ApiClient;

class FloatAccounts
{
    public function __construct(private ApiClient $http)
    {
    }

    /**
     * Create a new float account.
     *
     * @param array $params {
     *     @type string $name          Account name (required)
     *     @type string $currency_code 3-letter currency code (required)
     *     @type string $description   Account description
     *     @type array  $metadata      Custom key-value metadata
     * }
     * @return object Created float account
     */
    public function create(array $params): object
    {
        $res = $this->http->post('float-accounts', $params);
        return $res->data ?? $res;
    }

    /**
     * Get a float account by ID.
     *
     * @param int $id Float account ID
     * @return object Float account details with balances
     */
    public function get(int $id): object
    {
        $res = $this->http->get("float-accounts/{$id}");
        return $res->data ?? $res;
    }

    /**
     * List all active float accounts with balances.
     *
     * @return object List with id, name, account_number, currency, available_balance, reserved_balance, actual_balance
     */
    public function list(array $params = []): object
    {
        return $this->http->get('float-accounts', $params);
    }
}

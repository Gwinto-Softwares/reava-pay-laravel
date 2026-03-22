<?php

namespace ReavaPay;

use ReavaPay\Http\ApiClient;
use ReavaPay\Resources\Collections;
use ReavaPay\Resources\Customers;
use ReavaPay\Resources\FloatAccounts;
use ReavaPay\Resources\Payouts;
use ReavaPay\Resources\Recurring;
use ReavaPay\Resources\Settlements;
use ReavaPay\Resources\Transactions;
use ReavaPay\Resources\Invoices;
use ReavaPay\Resources\PaymentLinks;
use ReavaPay\Resources\WebhookEndpoints;
use ReavaPay\Resources\Webhooks;

class ReavaPayManager
{
    private ApiClient $http;
    private ?Collections $collectionsResource = null;
    private ?Payouts $payoutsResource = null;
    private ?Transactions $transactionsResource = null;
    private ?FloatAccounts $floatAccountsResource = null;
    private ?Settlements $settlementsResource = null;
    private ?Customers $customersResource = null;
    private ?Recurring $recurringResource = null;
    private ?Invoices $invoicesResource = null;
    private ?PaymentLinks $paymentLinksResource = null;
    private ?WebhookEndpoints $webhookEndpointsResource = null;
    private ?Webhooks $webhooksResource = null;

    public function __construct(string $apiKey, string $baseUrl, int $timeout = 30)
    {
        $this->http = new ApiClient($apiKey, $baseUrl, $timeout);
    }

    /**
     * Initiate a collection (shorthand).
     */
    public function collect(array $params): object
    {
        return $this->collections()->create($params);
    }

    /**
     * Initiate a payout (shorthand).
     */
    public function payout(array $params): object
    {
        return $this->payouts()->create($params);
    }

    /**
     * Get merchant profile info.
     */
    public function me(): object
    {
        return $this->http->get('me');
    }

    public function collections(): Collections
    {
        return $this->collectionsResource ??= new Collections($this->http);
    }

    public function payouts(): Payouts
    {
        return $this->payoutsResource ??= new Payouts($this->http);
    }

    public function transactions(): Transactions
    {
        return $this->transactionsResource ??= new Transactions($this->http);
    }

    public function floatAccounts(): FloatAccounts
    {
        return $this->floatAccountsResource ??= new FloatAccounts($this->http);
    }

    public function settlements(): Settlements
    {
        return $this->settlementsResource ??= new Settlements($this->http);
    }

    public function customers(): Customers
    {
        return $this->customersResource ??= new Customers($this->http);
    }

    public function recurring(): Recurring
    {
        return $this->recurringResource ??= new Recurring($this->http);
    }

    public function invoices(): Invoices
    {
        return $this->invoicesResource ??= new Invoices($this->http);
    }

    public function paymentLinks(): PaymentLinks
    {
        return $this->paymentLinksResource ??= new PaymentLinks($this->http);
    }

    public function webhookEndpoints(): WebhookEndpoints
    {
        return $this->webhookEndpointsResource ??= new WebhookEndpoints($this->http);
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooksResource ??= new Webhooks();
    }
}

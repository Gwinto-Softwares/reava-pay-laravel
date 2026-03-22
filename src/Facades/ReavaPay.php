<?php

namespace ReavaPay\Facades;

use Illuminate\Support\Facades\Facade;
use ReavaPay\ReavaPayManager;
use ReavaPay\Testing\ReavaPayFake;

/**
 * @method static object collect(array $params)
 * @method static object payout(array $params)
 * @method static object me()
 * @method static \ReavaPay\Resources\Collections collections()
 * @method static \ReavaPay\Resources\Payouts payouts()
 * @method static \ReavaPay\Resources\Transactions transactions()
 * @method static \ReavaPay\Resources\FloatAccounts floatAccounts()
 * @method static \ReavaPay\Resources\Settlements settlements()
 * @method static \ReavaPay\Resources\Customers customers()
 * @method static \ReavaPay\Resources\Recurring recurring()
 * @method static \ReavaPay\Resources\Invoices invoices()
 * @method static \ReavaPay\Resources\PaymentLinks paymentLinks()
 * @method static \ReavaPay\Resources\WebhookEndpoints webhookEndpoints()
 * @method static \ReavaPay\Resources\Webhooks webhooks()
 *
 * @see \ReavaPay\ReavaPayManager
 */
class ReavaPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ReavaPayManager::class;
    }

    /**
     * Replace the bound instance with a fake for testing.
     */
    public static function fake(array $responses = []): ReavaPayFake
    {
        $fake = new ReavaPayFake($responses);

        static::swap($fake);

        return $fake;
    }

    /**
     * Build a fake response object for use with fake().
     */
    public static function response(array $data, int $status = 200): object
    {
        return (object) array_merge($data, ['_status' => $status]);
    }
}

<?php

namespace ReavaPay\Console;

use Illuminate\Console\Command;
use ReavaPay\Exceptions\ReavaPayException;
use ReavaPay\Http\ApiClient;

class WebhooksRetryCommand extends Command
{
    protected $signature = 'reava-pay:webhooks:retry';
    protected $description = 'Retry failed Reava Pay webhook deliveries';

    public function handle(): int
    {
        try {
            $http = new ApiClient(
                config('reava-pay.key'),
                config('reava-pay.base_url'),
                config('reava-pay.timeout', 30),
            );

            $response = $http->post('webhooks/retry');

            $retried = $response->retried ?? 0;
            $this->info("Retried {$retried} failed webhook(s).");

            return self::SUCCESS;
        } catch (ReavaPayException $e) {
            $this->error("Failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

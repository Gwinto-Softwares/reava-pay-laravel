<?php

namespace ReavaPay\Console;

use Illuminate\Console\Command;
use ReavaPay\Exceptions\ReavaPayException;
use ReavaPay\Facades\ReavaPay;

class StatusCommand extends Command
{
    protected $signature = 'reava-pay:status';
    protected $description = 'Check Reava Pay API connection and key validity';

    public function handle(): int
    {
        $this->info('Checking Reava Pay API connection...');

        try {
            $merchant = ReavaPay::me();

            $this->newLine();
            $this->info('Connected successfully!');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Business Name', $merchant->business_name ?? 'N/A'],
                    ['Email', $merchant->email ?? 'N/A'],
                    ['Status', $merchant->status ?? 'N/A'],
                    ['Base URL', config('reava-pay.base_url')],
                ],
            );

            return self::SUCCESS;
        } catch (ReavaPayException $e) {
            $this->error("Connection failed: {$e->getMessage()} (HTTP {$e->getStatusCode()})");
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Connection failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

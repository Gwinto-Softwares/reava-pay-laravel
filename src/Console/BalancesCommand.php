<?php

namespace ReavaPay\Console;

use Illuminate\Console\Command;
use ReavaPay\Exceptions\ReavaPayException;
use ReavaPay\Facades\ReavaPay;

class BalancesCommand extends Command
{
    protected $signature = 'reava-pay:balances';
    protected $description = 'Show Reava Pay float account balances';

    public function handle(): int
    {
        try {
            $response = ReavaPay::floatAccounts()->list();
            $accounts = $response->data ?? [];

            if (empty($accounts)) {
                $this->info('No float accounts found.');
                return self::SUCCESS;
            }

            $rows = array_map(fn($fa) => [
                $fa->name ?? '',
                $fa->currency ?? 'KES',
                number_format($fa->available_balance ?? 0, 2),
                number_format($fa->pending_balance ?? 0, 2),
                number_format($fa->total_balance ?? 0, 2),
            ], $accounts);

            $this->table(
                ['Account', 'Currency', 'Available', 'Pending', 'Total'],
                $rows,
            );

            return self::SUCCESS;
        } catch (ReavaPayException $e) {
            $this->error("Failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

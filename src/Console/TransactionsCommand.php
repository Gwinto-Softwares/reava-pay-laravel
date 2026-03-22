<?php

namespace ReavaPay\Console;

use Illuminate\Console\Command;
use ReavaPay\Exceptions\ReavaPayException;
use ReavaPay\Facades\ReavaPay;

class TransactionsCommand extends Command
{
    protected $signature = 'reava-pay:transactions
                            {--limit=10 : Number of transactions to show}
                            {--status= : Filter by status (completed, pending, failed)}
                            {--type= : Filter by type (collection, payout)}';

    protected $description = 'List recent Reava Pay transactions';

    public function handle(): int
    {
        $params = array_filter([
            'per_page' => (int) $this->option('limit'),
            'status' => $this->option('status'),
            'type' => $this->option('type'),
        ]);

        try {
            $response = ReavaPay::transactions()->list($params);
            $transactions = $response->data ?? [];

            if (empty($transactions)) {
                $this->info('No transactions found.');
                return self::SUCCESS;
            }

            $rows = array_map(fn($txn) => [
                $txn->id ?? '',
                $txn->type ?? '',
                ($txn->currency ?? 'KES') . ' ' . number_format($txn->amount ?? 0, 2),
                $txn->status ?? '',
                $txn->reference ?? '',
                $txn->created_at ?? '',
            ], $transactions);

            $this->table(
                ['ID', 'Type', 'Amount', 'Status', 'Reference', 'Date'],
                $rows,
            );

            return self::SUCCESS;
        } catch (ReavaPayException $e) {
            $this->error("Failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

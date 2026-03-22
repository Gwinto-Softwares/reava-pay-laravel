<?php

namespace ReavaPay\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReavaPay\Facades\ReavaPay;

class ProcessCollection implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private array $params,
    ) {
    }

    public function handle(): void
    {
        ReavaPay::collect($this->params);
    }
}

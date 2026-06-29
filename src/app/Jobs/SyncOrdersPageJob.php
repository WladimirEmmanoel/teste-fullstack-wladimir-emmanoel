<?php

namespace App\Jobs;

use App\Services\Synchronization\OrderSynchronizationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncOrdersPageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }
    
    /**
     * Create a new job instance.
     */
    public function __construct(private array $orders){}

    /**
     * Execute the job.
     */
    public function handle(OrderSynchronizationService $synchronizer): void
    {
        $synchronizer->sync($this->orders);
    }
}
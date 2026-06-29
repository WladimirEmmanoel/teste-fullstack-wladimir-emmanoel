<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class WorkerHeartbeatCommand extends Command
{
    protected $signature = 'worker:heartbeat';
    protected $description = 'Send worker heartbeat to Redis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        while (true) {
            Redis::set('worker:heartbeat', time());
            sleep(5);
        }
    }
}

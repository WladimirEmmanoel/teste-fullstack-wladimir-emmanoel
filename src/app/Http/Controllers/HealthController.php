<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;


class HealthController extends Controller
{
    public function index()
    {
        $mysql = $this->checkMysql();
        $redis = $this->checkRedis();
        $worker = $this->checkWorker();

        return response()->json([
            'status' => ($mysql && $redis && $worker) ? 'ok' : 'error',
            'services' => [
                'mysql' => $mysql,
                'redis' => $redis,
                'worker' => $worker,
            ]
        ]);
    }

    private function checkMysql(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkRedis(): bool
    {
        try {
            return Redis::ping() === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkWorker(): bool
    {
        try {
            $lastHeartbeat = Redis::get('worker:heartbeat');

            if (!$lastHeartbeat) {
                return false;
            }

            return (time() - (int) $lastHeartbeat) <= 10;
        } catch (\Exception $e) {
            return false;
        }
    }
}

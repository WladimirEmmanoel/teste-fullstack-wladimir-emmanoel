<?php

namespace App\Services\FakeStore;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use App\Services\RateLimiterService;


abstract class BaseService
{
    
    protected const RATE_LIMIT_KEY = 'fakestore-api';

    public function __construct(protected RateLimiterService $rateLimiter)
    {}

    final protected function client(): PendingRequest
    {
        $this->rateLimiter->throttle(self::RATE_LIMIT_KEY);

        return Http::baseUrl(config('services.fakestore.url'))
            ->acceptJson()
            ->timeout(15)
            ->retry(3, 100);
    }
}
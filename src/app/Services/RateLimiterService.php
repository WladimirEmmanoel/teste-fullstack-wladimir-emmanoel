<?php

namespace App\Services;

use Illuminate\Support\Facades\RateLimiter;

class RateLimiterService
{
    /*
    */
    public function throttle(string $key): void
    {
        $maxAttempts = (int) config('services.fakestore.rate_limit');
        $decaySeconds = (int) config('services.fakestore.rate_window');

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            sleep(RateLimiter::availableIn($key));
        }

        RateLimiter::hit($key, $decaySeconds);
    }
}
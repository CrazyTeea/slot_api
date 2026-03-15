<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheStampedeProtection
{
    private string $cacheKeyPrefix;
    public function __construct()
    {
        $this->cacheKeyPrefix = 'lock:'.config('cache.cache_key_prefix');
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Cache::get($this->cacheKeyPrefix)) {
            usleep(10000);
        }
        else {
            Cache::put($this->cacheKeyPrefix, true, 2);
            $response = $next($request);
            Cache::forget($this->cacheKeyPrefix);
            return $response;
        }
        return $next($request);
    }
}

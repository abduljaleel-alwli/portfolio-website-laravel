<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Analytics\AnalyticsService;

class TrackPageView
{
    public function handle($request, Closure $next)
    {
        if ($request->method() === 'GET' && ! $request->ajax()) {
            app(AnalyticsService::class)->track('page_view');
        }

        return $next($request);
    }
}

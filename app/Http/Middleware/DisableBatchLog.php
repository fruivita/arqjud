<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\LogBatch;

/**
 * @see https://laravel.com/docs/9.x/middleware
 * @see https://spatie.be/docs/laravel-activitylog/v4/advanced-usage/batch-logs
 * @see https://stackoverflow.com/a/74397045
 */
class DisableBatchLog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        LogBatch::endBatch();

        return $response;
    }
}

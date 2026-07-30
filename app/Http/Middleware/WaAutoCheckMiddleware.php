<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\WaRoutineService;
use Symfony\Component\HttpFoundation\Response;

class WaAutoCheckMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     * Runs asynchronously via fastcgi_finish_request() with 0ms delay to user.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only run for GET requests on admin panel routes
        if ($request->isMethod('GET') && $request->is('admin*')) {
            WaRoutineService::runRoutineCheck();
        }
    }
}

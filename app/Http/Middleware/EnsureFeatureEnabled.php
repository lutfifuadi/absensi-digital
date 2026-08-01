<?php

namespace App\Http\Middleware;

use App\Facades\Feature;
use Closure;
use Illuminate\Http\Request;

class EnsureFeatureEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $featureKey
     */
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        if (Feature::isOff($featureKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fitur ini sedang tidak aktif.',
                ], 403);
            }

            abort(403, 'Fitur ini sedang tidak aktif.');
        }

        return $next($request);
    }
}

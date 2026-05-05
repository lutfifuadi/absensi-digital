<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass if not installed or on install routes
        if (!file_exists(storage_path('installed')) || $request->is('install*')) {
            return $next($request);
        }

        // Bypass for super_admin if authenticated
        // This will be handled by Tio in route configuration, but we can do a basic check here.
        if (auth()->check() && auth()->user()->isRole('super_admin')) {
            return $next($request);
        }

        // Check if Standalone mode
        if (env('APP_MULTIPURPOSE_MODE') === 'standalone') {
            $school = School::where('status', 'active')->first();
        } else {
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];

            // Let's assume localhost fallback or just simple subdomain check
            // If testing on localhost, we might want a fallback
            if ($subdomain === 'localhost' || $subdomain === '127') {
                $school = School::where('status', 'active')->first();
            } else {
                $school = School::where('subdomain', $subdomain)
                                ->where('status', 'active')
                                ->first();
            }
        }

        if (!$school) {
            // For now, abort or redirect. Let's abort 404 if tenant not found.
            abort(404, 'Tenant not found');
        }

        // Store tenant info in container and session
        app()->instance('current_school', $school);
        session(['school_id' => $school->id]);

        return $next($request);
    }
}

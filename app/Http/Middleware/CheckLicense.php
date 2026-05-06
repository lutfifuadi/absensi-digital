<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLicense
{
    /**
     * Route segments that are exempt from the license check.
     */
    private const EXEMPT_PREFIXES = [
        'install',
        'license-warning',
        'lang',
        'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce after the application has been installed
        if (!file_exists(storage_path('installed'))) {
            return $next($request);
        }

        // Skip exempt routes
        foreach (self::EXEMPT_PREFIXES as $prefix) {
            if ($request->is($prefix) || $request->is($prefix . '/*')) {
                return $next($request);
            }
        }

        $licenseKey = env('LICENSE_KEY');

        if (empty($licenseKey)) {
            // AJAX / JSON requests get a 403 JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Lisensi belum diaktifkan. Silakan aktifkan lisensi terlebih dahulu.',
                    'redirect' => route('license.warning'),
                ], 403);
            }

            return redirect()->route('license.warning');
        }

        return $next($request);
    }
}

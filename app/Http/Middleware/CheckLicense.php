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

        $licenseKey = config('license.key');
        
        // Double-check with database to bypass config caching issues
        $dbStatus = \App\Models\Pengaturan::where('key', 'license_status')->value('value');

        if (empty($licenseKey) || $dbStatus === 'inactive') {
            \Illuminate\Support\Facades\Log::info('License check: UNAUTHORIZED detected for ' . $request->fullUrl() . ' (Key: ' . ($licenseKey ? 'Exists' : 'Empty') . ', DB Status: ' . ($dbStatus ?: 'Not Set') . ')');
            
            // AJAX / JSON requests get a 403 JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Lisensi belum diaktifkan atau telah dicabut. Silakan aktifkan lisensi kembali.',
                    'redirect' => route('license.warning'),
                ], 403);
            }

            return redirect()->route('license.warning');
        }

        // FAIL-SAFE: Trigger scheduler silently if it hasn't run in the last 10 minutes
        // This ensures license verification happens even if the system task scheduler is not set up.
        $this->triggerLazyCron();

        return $next($request);
    }

    private function triggerLazyCron(): void
    {
        // Skip for localhost/127.0.0.1
        $host = request()->getHost();
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return;
        }

        try {
            $lastRun = \Illuminate\Support\Facades\Cache::get('lazy_cron_last_run');
            
            // Trigger every 10 minutes if there is traffic
            if (!$lastRun || now()->diffInMinutes($lastRun) >= 10) {
                \Illuminate\Support\Facades\Cache::put('lazy_cron_last_run', now(), now()->addHours(1));
                
                // Run schedule silently in the background
                // On Windows, we use 'start /B' to run without blocking the request
                if (PHP_OS_FAMILY === 'Windows') {
                    pclose(popen("start /B php " . base_path('artisan') . " schedule:run > NUL 2>&1", "r"));
                } else {
                    exec("php " . base_path('artisan') . " schedule:run > /dev/null 2>&1 &");
                }
            }
        } catch (\Throwable $e) {
            // Silently fail to not disrupt the user experience
        }
    }
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web([
            \App\Http\Middleware\CheckInstallation::class,
            \App\Http\Middleware\CheckLicense::class,
            LocaleMiddleware::class,
            \App\Http\Middleware\TahunAkademikSession::class,
            \App\Http\Middleware\CheckImpersonation::class,
            \App\Http\Middleware\CheckAutoAlphaMiddleware::class,
            \App\Http\Middleware\WaAutoCheckMiddleware::class,
        ]);
        $middleware->alias([
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'ortu'           => \App\Http\Middleware\OrangTuaMiddleware::class,
            'qr.scan.auth'   => \App\Http\Middleware\QrScanAuth::class,
            'absensi.cepat.auth' => \App\Http\Middleware\AbsensiCepatAuth::class,
            'device.trusted' => \App\Http\Middleware\CheckAuthorizedDevice::class,
            'pmbm.api.key'   => \App\Http\Middleware\ValidatePmbmApiKey::class,
            'tenant'         => \App\Http\Middleware\LocaleMiddleware::class, // Fallback alias
        ]);
        $middleware->encryptCookies(except: [
            'device_uuid',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah kedaluwarsa. Halaman akan diperbarui secara otomatis.',
                    'reload'  => true,
                    'csrf_token' => csrf_token(),
                ], 419);
            }

            return redirect()->back()
                ->withInput($request->except('_token', 'password', 'password_confirmation'))
                ->with('error', 'Sesi atau halaman Anda telah kedaluwarsa. Silakan kirim ulang formulir.');
        });
    })->create();

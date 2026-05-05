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
            LocaleMiddleware::class,
            \App\Http\Middleware\TahunAkademikSession::class,
        ]);
        $middleware->alias([
            'role'           => \App\Http\Middleware\RoleMiddleware::class,
            'qr.scan.auth'   => \App\Http\Middleware\QrScanAuth::class,
            'device.trusted' => \App\Http\Middleware\CheckAuthorizedDevice::class,
            'pmbm.api.key'   => \App\Http\Middleware\ValidatePmbmApiKey::class,
            'tenant'         => \App\Http\Middleware\TenantMiddleware::class,
        ]);
        $middleware->encryptCookies(except: [
            'device_uuid',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

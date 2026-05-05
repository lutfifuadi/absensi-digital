<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QrScanAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('qr_scan_authenticated')) {
            return redirect()->route('public.scan-qr.index')
                ->with('error', 'Silakan masukkan password terlebih dahulu.');
        }

        return $next($request);
    }
}

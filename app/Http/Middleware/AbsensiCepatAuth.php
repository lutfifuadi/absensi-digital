<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AbsensiCepatAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $settingsManager = app(\App\Services\SettingsManager::class);
        $isEnabled = $settingsManager->getBool('fitur_absensi_cepat_publik');

        if (!$isEnabled) {
            abort(404, 'Fitur Absensi Cepat Publik dinonaktifkan.');
        }

        if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin_sekolah'])) {
            return $next($request);
        }

        if (!$request->session()->get('absensi_cepat_authenticated')) {
            return redirect()->route('public.absensi-cepat.index')
                ->with('error', 'Silakan masukkan password terlebih dahulu.');
        }

        return $next($request);
    }
}

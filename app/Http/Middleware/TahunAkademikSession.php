<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TahunAkademik;

class TahunAkademikSession
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

        if (!session()->has('tahun_akademik_id')) {
            $aktif = TahunAkademik::where('is_aktif', true)->first();
            if ($aktif) {
                // Set default session to the active academic year
                session(['tahun_akademik_id' => $aktif->id]);
            }
        }

        // Sharing it with all views if a session exists
        if (session()->has('tahun_akademik_id')) {
            $currentTahun = TahunAkademik::find(session('tahun_akademik_id'));
            view()->share('currentTahunAkademik', $currentTahun);
        }

        return $next($request);
    }
}

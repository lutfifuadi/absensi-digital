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
            try {
                $aktif = TahunAkademik::where('is_aktif', true)->first();
                if ($aktif) {
                    session(['tahun_akademik_id' => $aktif->id, 'tahun_ajaran_id' => $aktif->id]);
                }
            } catch (\Exception $e) {
                // Database not ready or connection failed, skip session setting
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

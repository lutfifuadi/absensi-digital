<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Mencegah akses ke halaman admin /admin/* ketika impersonasi aktif
        if ($request->session()->has('impersonated_by') && $request->is('admin*')) {
            // Kecuali route untuk mengakhiri impersonasi
            if ($request->routeIs('impersonate.leave')) {
                return $next($request);
            }
            
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Akses ditolak. Anda harus keluar dari mode impersonasi terlebih dahulu untuk mengakses halaman admin.');
        }

        // 2. Mencegah tindakan sensitif seperti perubahan password (karena user sedang di-impersonate)
        if ($request->session()->has('impersonated_by')) {
            // Cek rute sensitif. Fortify/Jetstream biasanya menggunakan routes 'user-password.update'
            // Kita juga bisa memblokir POST request ke route/URL profile password/update.
            $sensitivePatterns = [
                'user-password.update',
                'user-profile-information.update',
                'two-factor.*',
                'profile.show', // prevent delete account too, but usually it POSTs to some route
            ];
            
            foreach ($sensitivePatterns as $pattern) {
                if ($request->routeIs($pattern)) {
                    return back()->with('error', 'Tindakan sensitif (mengubah password/profil) dilarang selama mode impersonasi.');
                }
            }

            // Juga block request POST/PUT/DELETE yang menuju endpoint update password atau profile secara URL
            if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
                if ($request->is('*password*') || $request->is('*profile*') || $request->is('*two-factor*')) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tindakan sensitif dilarang selama mode impersonasi.'
                        ], 403);
                    }
                    
                    return back()->with('error', 'Tindakan sensitif dilarang selama mode impersonasi.');
                }
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Console\Commands\AutoMarkAlphaCommand;
use App\Models\Pengaturan;
use App\Services\PengaturanService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckAutoAlphaMiddleware
{
    public function __construct(
        private PengaturanService $pengaturanService
    ) {}

    /**
     * Handle an incoming request.
     *
     * Middleware ini me-pemicu perintah auto-alpha secara otomatis melalui web request
     * tanpa perlu menjalankan Cron Job atau terminal 'schedule:run' / 'schedule:work'.
     */
    public function handle(Request $request, Closure $next)
    {
        // Jalankan pengecekan secara pasif tanpa mengganggu respon request utama
        $this->triggerAutoAlphaIfTime();

        return $next($request);
    }

    private function triggerAutoAlphaIfTime(): void
    {
        try {
            $today = now()->toDateString();
            $cacheKey = 'auto_alpha_web_check_' . $today . '_' . now()->format('H_i');

            // Cek cache throttle per 15 menit agar efisien dan tidak memberatkan server
            if (Cache::has($cacheKey)) {
                return;
            }

            // Tandai sudah di-check untuk 15 menit ke depan
            Cache::put($cacheKey, true, now()->addMinutes(15));

            // Cek toggle auto_alpha_siswa_enabled
            if (!$this->pengaturanService->isAutoAlphaEnabled()) {
                return;
            }

            // Ambil batas jam masuk global
            $batasJamMasuk = setting('jam_batas_masuk', '08:00');
            $currentTimeStr = now()->format('H:i');

            // Jika jam sekarang sudah mencapai atau melewati batas jam masuk (misal 08:00 WIB)
            if ($currentTimeStr >= $batasJamMasuk) {
                // Eksekusi auto-alpha command di background/in-request secara aman
                Artisan::call(AutoMarkAlphaCommand::class);
                Log::info("CheckAutoAlphaMiddleware: Auto-alpha berhasil dipicu otomatis via web request pada {$currentTimeStr} WIB.");
            }
        } catch (\Throwable $e) {
            // Tangkap exception agar tidak pernah mengganggu request pengguna
            Log::warning("CheckAutoAlphaMiddleware Error: " . $e->getMessage());
        }
    }
}

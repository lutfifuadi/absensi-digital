<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!file_exists(storage_path('installed'))) {
            return;
        }
        try {
            // Deteksi Zona Waktu dari pengaturan database jika tabel sudah ada (mencegah error migrate)
            if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan')) {
                $zonaWaktu = \App\Models\Pengaturan::where('key', 'zona_waktu')->value('value');
                if ($zonaWaktu) {
                    // Extract timezone format (e.g. "Asia/Jakarta (WIB)" -> "Asia/Jakarta")
                    $validTz = explode(' ', trim($zonaWaktu))[0];
                    date_default_timezone_set($validTz);
                    config(['app.timezone' => $validTz]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if DB connection is not yet established
        }

        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src !== null) {
                return [
                    'class' => preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i", $src) ? 'template-customizer-core-css' : (preg_match("/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i", $src) ? 'template-customizer-theme-css' : '')
                ];
            }
            return [];
        });
    }
}

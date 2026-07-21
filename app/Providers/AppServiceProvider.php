<?php

namespace App\Providers;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Policies\GuidePolicy;
use Illuminate\Support\Facades\Gate;
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
        // Daftarkan policy untuk Guide dan GuideCategory
        Gate::policy(Guide::class, GuidePolicy::class);
        Gate::policy(GuideCategory::class, GuidePolicy::class);
        Gate::policy(\App\Models\PelanggaranSiswa::class, \App\Policies\PelanggaranSiswaPolicy::class);

        if (!file_exists(storage_path('installed'))) {
            return;
        }
        try {
            // Deteksi Zona Waktu & Nama Sekolah dari pengaturan database jika tabel sudah ada
            if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan')) {
                $pengaturan = \App\Models\Pengaturan::whereIn('key', ['zona_waktu', 'nama_sekolah'])->get()->pluck('value', 'key');
                
                $zonaWaktu = $pengaturan->get('zona_waktu');
                if ($zonaWaktu) {
                    $validTz = explode(' ', trim($zonaWaktu))[0];
                    date_default_timezone_set($validTz);
                    config(['app.timezone' => $validTz]);
                }

                $namaSekolah = $pengaturan->get('nama_sekolah');
                if ($namaSekolah) {
                    config(['variables.templateName' => $namaSekolah]);
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

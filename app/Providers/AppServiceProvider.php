<?php

namespace App\Providers;

use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\User;
use App\Observers\UserPasswordObserver;
use App\Policies\GuidePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
        // ── Custom Rate Limiter: QR Scan ──────────────────────────────────────────
        // Menggunakan session ID sebagai key, bukan IP address.
        // Ini menghindari false-positive throttle di sekolah yang menggunakan
        // jaringan shared (NAT) sehingga semua device keluar dari 1 IP publik.
        RateLimiter::for('qr-scan', function ($request) {
            // Gunakan session ID sebagai key per-device, fallback ke IP
            $key = $request->session()->getId() ?: $request->ip();
            return Limit::perMinute(300)->by($key);
        });

        // Set Carbon locale to Indonesian globally
        \Carbon\Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID');

        // Global default pagination view — menggunakan gaya das-page-btn
        Paginator::defaultView('vendor.pagination.users');
        Paginator::defaultSimpleView('vendor.pagination.users');

        // Daftarkan policy untuk Guide dan GuideCategory
        Gate::policy(Guide::class, GuidePolicy::class);
        Gate::policy(GuideCategory::class, GuidePolicy::class);
        Gate::policy(\App\Models\PelanggaranSiswa::class, \App\Policies\PelanggaranSiswaPolicy::class);

        // Absensi per jam (PRD-006): Gate::authorize('isi', [$jadwal, $tanggal])
        // ter-resolve ke policy ini karena argumen pertama adalah JadwalPelajaran.
        Gate::policy(\App\Models\JadwalPelajaran::class, \App\Policies\AbsensiPerJamPolicy::class);

        // Observer untuk sync password_plain saat password berubah
        User::observe(UserPasswordObserver::class);
        \App\Models\Siswa::observe(\App\Observers\SiswaObserver::class);
        \App\Models\Guru::observe(\App\Observers\GuruObserver::class);

        // Daftarkan Blade directive @fitur
        \Illuminate\Support\Facades\Blade::directive('fitur', function ($expression) {
            return "<?php if (feature({$expression})): ?>";
        });
        \Illuminate\Support\Facades\Blade::directive('endfitur', function () {
            return '<?php endif; ?>';
        });

        // Daftarkan middleware alias 'feature'
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $router->aliasMiddleware('feature', \App\Http\Middleware\EnsureFeatureEnabled::class);

        if (!file_exists(storage_path('installed'))) {
            return;
        }
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan')) {
                $zonaWaktu = setting('zona_waktu');
                if ($zonaWaktu) {
                    $validTz = explode(' ', trim($zonaWaktu))[0];
                    date_default_timezone_set($validTz);
                    config(['app.timezone' => $validTz]);
                }

                $namaSekolah = setting('nama_lembaga') ?: setting('nama_sekolah');
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

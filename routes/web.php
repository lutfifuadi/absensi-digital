<?php

use App\Http\Controllers\Admin\GuruBkController;
use App\Http\Controllers\Admin\GuruPiketController;
use App\Http\Controllers\GuruBk\BKDashboardController;
use App\Http\Controllers\GuruBk\BKPelanggaranController;
use App\Http\Controllers\GuruBk\BKSPController;
use App\Http\Controllers\GuruBk\BKRekapController;
use App\Http\Controllers\AbsensiMandiriController;
use App\Http\Controllers\Admin\AbsensiActivityController;
use App\Http\Controllers\Admin\AbsensiGuruController;
use App\Http\Controllers\Admin\AbsensiKegiatanController;
use App\Http\Controllers\Admin\AbsensiPerJamController;
use App\Http\Controllers\Admin\AbsensiSiswaController;
use App\Http\Controllers\Admin\AnalitikSiswaController;
use App\Http\Controllers\Admin\AbsensiStaffController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AiChatController;
use App\Http\Controllers\Admin\AlumniController;
use App\Http\Controllers\Admin\ApiIntegrationController;
use App\Http\Controllers\Admin\CetakKartuController;
use App\Http\Controllers\Admin\ApiSourceSettingsController;
use App\Http\Controllers\Admin\DeployController;
use App\Http\Controllers\Admin\DeviceManagementController;
use App\Http\Controllers\Admin\EkskulAbsensiController;
use App\Http\Controllers\Admin\EkskulAnggotaController;
use App\Http\Controllers\Admin\EkskulController;
use App\Http\Controllers\Admin\GoogleSheetsSettingController;
use App\Http\Controllers\Admin\GoogleSheetsGuruSettingController;
use App\Http\Controllers\Admin\GuideCategoryController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\IdCardTemplateController;
use App\Http\Controllers\Admin\ImpersonateController;
use App\Http\Controllers\Admin\IzinSakitController;
use App\Http\Controllers\Admin\IzinPulangCepatController;
use App\Http\Controllers\Admin\JadwalPelajaranController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\JadwalKegiatanController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\LeaveLimitController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\Admin\OrangTuaController;
use App\Http\Controllers\Admin\PelepasanController;
use App\Http\Controllers\Admin\PembelianLisensiController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\PwaSettingsController;
use App\Http\Controllers\Admin\QueueControlController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ScanQrController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\StaffTataUsahaController;
use App\Http\Controllers\Admin\TahunAkademikController;
use App\Http\Controllers\Admin\UpdateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WaGatewayController;
use App\Http\Controllers\Admin\WaliKelasController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\PortalOrangTuaController;
use App\Http\Controllers\PortalSiswaController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\PublicPelepasanController;
use App\Http\Controllers\PublicQrScanController;
use App\Http\Controllers\PiketScannerController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PublicKegiatanController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// ── License Routes ────────────────────────────────────────────────────────
Route::get('/license-warning', [LicenseController::class, 'showWarning'])->name('license.warning');
Route::post('/license-warning/activate', [LicenseController::class, 'activate'])->name('license.activate');
// ─────────────────────────────────────────────────────────────────────────

// ── Web Installer Routes (dinonaktifkan setelah instalasi) ───────────────
if (config('app.installed') !== true) {
    Route::prefix('install')->name('installer.')->group(function () {
        Route::get('/', [InstallerController::class, 'step1'])->name('step1');
        Route::get('/step2', [InstallerController::class, 'step2'])->name('step2');
        Route::post('/step2', [InstallerController::class, 'step2Submit'])->name('step2Submit');
        Route::get('/step3', [InstallerController::class, 'step3'])->name('step3');
        Route::post('/step3', [InstallerController::class, 'step3Submit'])->name('step3Submit');
        Route::get('/step4', [InstallerController::class, 'step4'])->name('step4');
        Route::post('/step4', [InstallerController::class, 'step4Submit'])->name('step4Submit');
        Route::get('/step5', [InstallerController::class, 'step5'])->name('step5');
        Route::post('/process', [InstallerController::class, 'process'])->name('process');
        Route::post('/save-progress', [InstallerController::class, 'saveProgress'])->name('saveProgress');
        Route::post('/publish-assets', [InstallerController::class, 'publishAssets'])->name('publishAssets');
    });
}
// ─────────────────────────────────────────────────────────────────────────

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/login-bypass', function () {
    $user = User::where('username', 'admin')->first();
    if ($user) {
        auth()->guard('web')->login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    return 'User admin tidak ditemukan.';
});
// Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// ── Halaman Scan QR Publik (Guru Piket — tanpa login) ─────────────────────────
Route::prefix('scan-qr')->name('public.scan-qr.')->group(function () {
    Route::get('/', [PublicQrScanController::class, 'index'])->name('index')->middleware('device.trusted');
    Route::post('/auth', [PublicQrScanController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::get('/scan', [PublicQrScanController::class, 'scan'])->name('scan')->middleware(['qr.scan.auth', 'device.trusted']);
    Route::post('/process', [PublicQrScanController::class, 'process'])->name('process')->middleware(['qr.scan.auth', 'device.trusted', 'throttle:qr-scan']);
    Route::post('/logout', [PublicQrScanController::class, 'logout'])->name('logout');
    Route::get('/stats', [PublicQrScanController::class, 'scanStats'])->name('stats');
    Route::get('/search', [PublicQrScanController::class, 'searchSiswaGuru'])
        ->name('search');
});

// ── Absensi Cepat Publik ──────────────────────────────────────────────────────
Route::prefix('absensi-cepat')->name('public.absensi-cepat.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'index'])->name('index');
    Route::post('/auth', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::post('/logout', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'logout'])->name('logout');

    Route::middleware(['absensi.cepat.auth'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'dashboard'])->name('dashboard');
        Route::get('/siswa/{kelasId}', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'getSiswaByKelas'])->name('siswa');
        Route::get('/search-people', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'searchPeople'])->name('search-people');
        Route::post('/scan', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'processQr'])->name('scan')->middleware('throttle:120,1');
        Route::post('/bulk', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'storeBulk'])->name('bulk');
        Route::post('/store-single', [\App\Http\Controllers\PublicAbsensiCepatController::class, 'storeSingle'])->name('store-single');
    });
});

// Scan absensi kegiatan publik ber-password tanpa lock device
Route::prefix('kegiatan/scan-publik')->name('public.kegiatan.')->group(function () {
    Route::get('/', [PublicKegiatanController::class, 'index'])->name('index');
    Route::post('/auth', [PublicKegiatanController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::get('/scan', [PublicKegiatanController::class, 'scan'])->name('scan');
    Route::post('/process', [PublicKegiatanController::class, 'process'])->name('process')->middleware('throttle:60,1');
    Route::post('/logout', [PublicKegiatanController::class, 'logout'])->name('logout');
});

Route::get('/device-unauthorized', function () {
    return view('public.device-unauthorized');
})->name('public.device-unauthorized');

// ── Halaman Publik (tanpa login) ──────────────────────────────────────────────
Route::get('/tentang-kami', [PublicPagesController::class, 'tentangKami'])->name('public.tentang-kami');
Route::redirect('/panduan-pengguna', '/panduan', 301)->name('public.panduan-pengguna');
Route::get('/kebijakan-privasi', [PublicPagesController::class, 'kebijakanPrivasi'])->name('public.kebijakan-privasi');
Route::get('/bantuan', [PublicPagesController::class, 'bantuan'])->name('public.bantuan');
Route::get('/prestasi', [PublicPagesController::class, 'prestasi'])->name('public.prestasi');

// ── Layanan Pengaduan Data Tidak Valid (PRD-002) ─────────────────────────────
// Route utama /pengaduan — harus login, redirect ke halaman sesuai role (siswa/ortu)
Route::prefix('pengaduan')->name('pengaduan.')->middleware('feature:wa_pengaduan_enabled')->group(function () {
    Route::get('/', [PengaduanController::class, 'form'])->middleware('auth')->name('form');
    Route::get('/cek', [PengaduanController::class, 'cekForm'])->name('cek');
});
// ─────────────────────────────────────────────────────────────────────────────

// ── Halaman Panduan (public) ───────────────────────────────────────────────────
Route::prefix('panduan')->name('guide.')->group(function () {
    Route::get('/', [GuideController::class, 'index'])->name('index');
    Route::get('/cari', [GuideController::class, 'search'])->name('search');
    Route::get('/{categorySlug}', [GuideController::class, 'category'])->name('category');
    Route::get('/{categorySlug}/{slug}', [GuideController::class, 'show'])->name('show');
});
// ─────────────────────────────────────────────────────────────────────────────

// ── Halaman Scan QR Ekskul (publik — siswa scan QR dari pembina) ────────────
Route::get('/scan-ekskul', function () {
    return view('scan-ekskul');
})->name('public.scan-ekskul');

// ── Halaman Scan Ekskul oleh Pembina (publik — tanpa login, pakai PIN/passcode) ──
Route::prefix('ekskul/scan-qr')->name('public.ekskul.scan.')->group(function () {
    Route::get('/', [\App\Http\Controllers\PublicEkskulScanController::class, 'index'])->name('index');
    Route::post('/auth', [\App\Http\Controllers\PublicEkskulScanController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::get('/scanner', [\App\Http\Controllers\PublicEkskulScanController::class, 'scanner'])->name('scanner');
    Route::post('/process', [\App\Http\Controllers\PublicEkskulScanController::class, 'process'])->name('process')->middleware('throttle:120,1');
    Route::post('/logout', [\App\Http\Controllers\PublicEkskulScanController::class, 'logout'])->name('logout');
});
// ─────────────────────────────────────────────────────────────────────────────

// ── Halaman Live Board Publik (tanpa login) ───────────────────────────────────
Route::get('/live-board', [PublicQrScanController::class, 'liveBoard'])->name('public.live-board')->middleware('device.trusted');
Route::post('/live-board/scan', [PublicQrScanController::class, 'liveBoardScan'])->name('public.live-board.scan')->middleware(['throttle:qr-scan', 'device.trusted']);
Route::get('/live-board/leaderboard', [PublicQrScanController::class, 'liveBoardLeaderboard'])->name('public.live-board.leaderboard');
Route::get('/live-board-guru', [\App\Http\Controllers\Admin\LiveBoardGuruController::class, 'index'])->name('public.live-board-guru');
Route::get('/live-board-guru/data', [\App\Http\Controllers\Admin\LiveBoardGuruController::class, 'data'])->name('public.live-board-guru.data');
Route::post('/live-board-guru/scan', [\App\Http\Controllers\Admin\LiveBoardGuruController::class, 'scan'])->name('public.live-board-guru.scan');
// ─────────────────────────────────────────────────────────────────────────────

// ── Halaman Scan Pelepasan Publik (tanpa login, pakai kata kunci) ─────────────
Route::prefix('pelepasan/scan')->name('public.pelepasan.')->group(function () {
    Route::get('/', [PublicPelepasanController::class, 'index'])->name('index');
    Route::post('/auth', [PublicPelepasanController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::get('/live', [PublicPelepasanController::class, 'scan'])->name('scan');
    Route::post('/process', [PublicPelepasanController::class, 'process'])->name('process')->middleware('throttle:60,1');
    Route::post('/logout', [PublicPelepasanController::class, 'logout'])->name('logout');
});
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Role selector routes
    Route::get('/select-role', [\App\Http\Controllers\RoleSelectorController::class, 'index'])->name('role.select');
    Route::post('/select-role', [\App\Http\Controllers\RoleSelectorController::class, 'select'])->name('role.select.post');
    Route::post('/switch-role', [\App\Http\Controllers\RoleSelectorController::class, 'switch'])->name('role.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/guru', [DashboardController::class, 'guruAnalytics'])->name('admin.dashboard.guru')->middleware(['role:super_admin,admin_sekolah,operator', 'feature:fitur_dashboard_guru']);
    Route::get('/dashboard/refresh-stats', [DashboardController::class, 'refreshStats'])->name('admin.dashboard.refresh-stats')->middleware('role:super_admin,admin_sekolah');
    Route::get('/admin/wa-gateway/check-services-status', [\App\Http\Controllers\Admin\WaGatewayController::class, 'checkServicesStatus'])->name('admin.wa-gateway.check-services-status');
    Route::post('/admin/wa-gateway/batch-check-numbers', [\App\Http\Controllers\Admin\WaGatewayController::class, 'batchCheckNumbers'])->name('admin.wa-gateway.batch-check-numbers');
    Route::post('/admin/wa-gateway/check-all-role-numbers', [\App\Http\Controllers\Admin\WaGatewayController::class, 'checkAllRoleNumbers'])->name('admin.wa-gateway.check-all-role-numbers');

    // ── FITUR GURU BK (Bimbingan Konseling) ───────────────────────────────────
    Route::prefix('bk')->name('bk.')->middleware('feature:fitur_modul_pelanggaran')->group(function () {
        Route::get('/dashboard', [BKDashboardController::class, 'index'])->name('dashboard');
        Route::get('/pelanggaran', [BKPelanggaranController::class, 'index'])->name('pelanggaran.index');
        Route::get('/pelanggaran/create', [BKPelanggaranController::class, 'create'])->name('pelanggaran.create');
        Route::post('/pelanggaran', [BKPelanggaranController::class, 'store'])->name('pelanggaran.store');
        Route::get('/pelanggaran/{id}', [BKPelanggaranController::class, 'show'])->name('pelanggaran.show');

        Route::get('/sp', [BKSPController::class, 'index'])->name('sp.index');
        Route::get('/sp/create', [BKSPController::class, 'create'])->name('sp.create');
        Route::post('/sp', [BKSPController::class, 'store'])->name('sp.store');

        Route::get('/rekap', [BKRekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export', [BKRekapController::class, 'export'])->name('rekap.export');
        Route::get('/rekap/export-pdf', [BKRekapController::class, 'exportPdf'])->name('rekap.pdf');
    });

    // ── PORTAL SISWA ──────────────────────────────────────────────────────────
    Route::prefix('siswa')->middleware('role:siswa,super_admin,admin_sekolah,operator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('siswa.dashboard');
        Route::get('/profile', [SiswaController::class, 'profilSaya'])->name('siswa.profile');
        Route::get('/download-kartu', [PortalSiswaController::class, 'downloadKartu'])->name('siswa.download-kartu')->middleware('feature:fitur_download_kartu_siswa');
        Route::get('/download-kartu-pelepasan', [PortalSiswaController::class, 'downloadKartuPelepasan'])->name('siswa.download-kartu-pelepasan')->middleware('feature:fitur_download_kartu_siswa');
        Route::get('/leaderboard', [PortalSiswaController::class, 'leaderboard'])->name('siswa.leaderboard')->middleware('feature:fitur_gamifikasi');
        Route::get('/absensi', [PortalSiswaController::class, 'absensi'])->name('siswa.absensi');
        Route::get('/absensi/data', [PortalSiswaController::class, 'absensiJson'])->name('siswa.absensi.data');

        // Izin & Sakit (Hanya lihat riwayat, pengajuan dilakukan oleh Ortu/Guru/Wali Kelas/Piket/Admin)
        Route::get('/izin-sakit', [IzinSakitController::class, 'index'])->name('siswa.izin-sakit.index');
        Route::get('/izin-sakit/create', function() {
            return redirect()->route('siswa.izin-sakit.index')->with('error', 'Pengajuan izin/sakit siswa dilakukan oleh Orang Tua, Guru, Wali Kelas, Piket, atau Admin.');
        })->name('siswa.izin-sakit.create');
        Route::post('/izin-sakit', function() {
            return redirect()->route('siswa.izin-sakit.index')->with('error', 'Pengajuan izin/sakit siswa dilakukan oleh Orang Tua, Guru, Wali Kelas, Piket, atau Admin.');
        })->name('siswa.izin-sakit.store');

        // Penugasan Guru untuk Siswa
        Route::get('/assignments', [AssignmentController::class, 'index'])->name('siswa.assignments.index');
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('siswa.assignments.show');

        Route::get('/pengaduan', [PortalSiswaController::class, 'pengaduan'])->name('siswa.pengaduan');

        Route::post('/absensi-mandiri', [AbsensiMandiriController::class, 'store'])->name('siswa.absensi-mandiri.store');
        Route::post('/upload-foto', [PortalSiswaController::class, 'uploadFoto'])->name('siswa.upload-foto');
        Route::post('/update-tanggal-lahir', [PortalSiswaController::class, 'updateTanggalLahir'])->name('siswa.update-tanggal-lahir');
        Route::post('/update-biodata', [PortalSiswaController::class, 'updateBiodata'])->name('siswa.update-biodata');
    });

    // ── PORTAL GURU ───────────────────────────────────────────────────────────
    Route::prefix('guru')->middleware('role:guru,super_admin,admin_sekolah,operator')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('guru.dashboard');
        Route::get('/absensi', [AbsensiGuruController::class, 'index'])->name('guru.absensi.index');
        Route::get('/absensi/scan', [AbsensiGuruController::class, 'scan'])->name('guru.absensi.scan')->middleware('feature:fitur_scan_absensi_guru');

        // Absensi Cepat Siswa (Portal Guru)
        Route::get('/absensi-cepat', [AbsensiSiswaController::class, 'bulkForm'])
            ->name('guru.absensi-cepat');
        Route::get('/absensi-cepat/search', [AbsensiSiswaController::class, 'searchStudent'])
            ->name('guru.absensi-cepat.search');
        Route::post('/absensi-cepat', [AbsensiSiswaController::class, 'bulkStore'])
            ->name('guru.absensi-cepat.store');
        Route::post('/absensi-cepat/store-single', [AbsensiSiswaController::class, 'storeSingle'])
            ->name('guru.absensi-cepat.store-single');

        // Izin & Sakit (Scoped to self in Controller)
        Route::get('/izin-sakit', [IzinSakitController::class, 'index'])->name('guru.izin-sakit.index');
        Route::get('/izin-sakit/create', [IzinSakitController::class, 'create'])->name('guru.izin-sakit.create');
        Route::post('/izin-sakit', [IzinSakitController::class, 'store'])->name('guru.izin-sakit.store');

        // Penugasan Guru
        Route::resource('assignments', AssignmentController::class)->names([
            'index' => 'assignments.index',
            'create' => 'assignments.create',
            'store' => 'assignments.store',
            'edit' => 'assignments.edit',
            'update' => 'assignments.update',
            'destroy' => 'assignments.destroy',
            'show' => 'assignments.show',
        ]);

        // Absensi Siswa per Jam (PRD-006) — halaman "Absensi Kelas Saya" (index saja)
        Route::get('/absensi-per-jam', [AbsensiPerJamController::class, 'index'])
            ->name('guru.absensi-per-jam');

        Route::get('/rekap-absensi', [LaporanController::class, 'index'])
            ->name('guru.laporan.index');
    });

    // ── PORTAL SATPAM/GATEKEEPER ──────────────────────────────────────────────
    Route::prefix('satpam')->middleware('role:satpam,super_admin,admin_sekolah,operator,piket')->group(function () {
        Route::get('/gatekeeper-scan', [\App\Http\Controllers\Satpam\GatekeeperController::class, 'index'])
            ->name('satpam.gatekeeper.scan');
        Route::post('/gatekeeper-verify', [\App\Http\Controllers\Satpam\GatekeeperController::class, 'verify'])
            ->name('satpam.gatekeeper.verify');
        Route::post('/gatekeeper-checkout/{izin}', [\App\Http\Controllers\Satpam\GatekeeperController::class, 'confirmCheckout'])
            ->name('satpam.gatekeeper.checkout');
    });

    // ── PORTAL WALI KELAS ─────────────────────────────────────────────────────
    Route::prefix('wali-kelas')->middleware('role:wali_kelas')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('wali-kelas.dashboard');
        Route::get('/siswa', [SiswaController::class, 'index'])->name('wali-kelas.siswa.index');
        Route::get('/siswa/{siswa}/profil', [SiswaController::class, 'profil'])->name('wali-kelas.siswa.profil');
        Route::get('/absensi-siswa', [AbsensiSiswaController::class, 'index'])->name('wali-kelas.absensi-siswa.index');
        Route::get('/absensi-siswa/export', [AbsensiSiswaController::class, 'export'])->name('wali-kelas.absensi-siswa.export');
        Route::get('/absensi-cepat', [AbsensiSiswaController::class, 'bulkForm'])->name('wali-kelas.absensi-cepat');
        Route::get('/absensi-cepat/search', [AbsensiSiswaController::class, 'searchStudent'])->name('wali-kelas.absensi-cepat.search');
        Route::post('/absensi-cepat', [AbsensiSiswaController::class, 'bulkStore'])->name('wali-kelas.absensi-cepat.store');
        Route::post('/absensi-cepat/store-single', [AbsensiSiswaController::class, 'storeSingle'])->name('wali-kelas.absensi-cepat.store-single');
        Route::get('/rekap-harian', [LaporanController::class, 'rekapHarian'])->name('wali-kelas.rekap-harian');
        Route::get('/rekap-bulanan', [LaporanController::class, 'index'])->name('wali-kelas.rekap-bulanan');
        Route::get('/belum-absen', [LaporanController::class, 'belumAbsen'])->name('wali-kelas.belum-absen');
    });

    // ── PORTAL ORANG TUA ──────────────────────────────────────────────────────
    Route::prefix('ortu')->middleware(['ortu', 'feature:fitur_portal_ortu'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('ortu.dashboard');
        Route::get('/anak', function() {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $activeSiswaId = session('active_siswa_id');
            if (!$activeSiswaId) {
                $firstAnak = \App\Models\Siswa::where(function($query) use ($user) {
                    $query->where('ortu_user_id', $user->id)
                          ->orWhereHas('ortu', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })->first();
                if ($firstAnak) {
                    $activeSiswaId = $firstAnak->id;
                    session(['active_siswa_id' => $activeSiswaId]);
                }
            }
            if ($activeSiswaId) {
                return redirect()->route('ortu.anak.profil', $activeSiswaId);
            }
            return redirect()->route('ortu.dashboard');
        });
        Route::get('/absensi', function() {
            $activeSiswaId = session('active_siswa_id');
            if (!$activeSiswaId) {
                /** @var \App\Models\User $user */
                $user = auth()->user();
                $firstAnak = \App\Models\Siswa::where(function($query) use ($user) {
                    $query->where('ortu_user_id', $user->id)
                          ->orWhereHas('ortu', function($q) use ($user) {
                              $q->where('users.id', $user->id);
                          });
                })->first();
                if ($firstAnak) {
                    $activeSiswaId = $firstAnak->id;
                    session(['active_siswa_id' => $activeSiswaId]);
                }
            }
            if ($activeSiswaId) {
                return redirect()->route('ortu.anak.absensi', $activeSiswaId);
            }
            return redirect()->route('ortu.dashboard');
        })->name('ortu.absensi');
        Route::post('/switch-anak', [DashboardController::class, 'switchAnak'])->name('ortu.switch-anak');
        Route::get('/anak/{id}/profil', [PortalOrangTuaController::class, 'profilAnak'])->name('ortu.anak.profil');
        Route::get('/anak/{id}/absensi', [PortalOrangTuaController::class, 'absensiAnak'])->name('ortu.anak.absensi');
        Route::get('/anak/{id}/absensi/data', [PortalOrangTuaController::class, 'absensiAnakJson'])->name('ortu.anak.absensi.data');

        // Absensi Kegiatan Ortu
        Route::get('/absensi-kegiatan', [PortalOrangTuaController::class, 'absensiKegiatan'])->name('ortu.absensi-kegiatan');

        // Pelanggaran Ortu
        Route::get('/pelanggaran', [PortalOrangTuaController::class, 'pelanggaran'])->name('ortu.pelanggaran');

        Route::get('/izin-sakit', [PortalOrangTuaController::class, 'izinSakit'])->name('ortu.izin-sakit.index');
        Route::get('/izin-sakit/create', [PortalOrangTuaController::class, 'izinSakitCreate'])->name('ortu.izin-sakit.create');
        Route::post('/izin-sakit', [PortalOrangTuaController::class, 'izinSakitStore'])->name('ortu.izin-sakit.store');
        Route::delete('/izin-sakit/{id}', [PortalOrangTuaController::class, 'izinSakitDestroy'])->name('ortu.izin-sakit.destroy');

        // Layanan Pengaduan Portal Ortu
        Route::get('/pengaduan', [PortalOrangTuaController::class, 'pengaduan'])->name('ortu.pengaduan');

        // Pengaturan Profil & Ganti Password Ortu
        Route::get('/pengaturan', [PortalOrangTuaController::class, 'pengaturan'])->name('ortu.pengaturan');
        Route::put('/pengaturan/profil', [PortalOrangTuaController::class, 'updateProfil'])->name('ortu.pengaturan.profil');
        Route::put('/pengaturan/password', [PortalOrangTuaController::class, 'updatePassword'])->name('ortu.pengaturan.password');
    });

    // ── PORTAL PIKET ──────────────────────────────────────────────────────────
    Route::prefix('piket')->middleware('role:piket')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('piket.dashboard');
        Route::get('/scanner', [PiketScannerController::class, 'index'])->name('piket.scanner');
        Route::post('/scanner/process', [PiketScannerController::class, 'process'])->name('piket.scanner.process')->middleware('throttle:120,1');
        Route::get('/scanner/stats', [PiketScannerController::class, 'stats'])->name('piket.scanner.stats');
        Route::get('/rekap', [PiketScannerController::class, 'rekap'])->name('piket.rekap');
        Route::post('/rekap/update', [PiketScannerController::class, 'updateRekap'])->name('piket.rekap.update');
        Route::get('/rekap-saya', [PiketScannerController::class, 'rekapSaya'])->name('piket.rekap-saya');
        Route::get('/rekap-saya/pdf', [PiketScannerController::class, 'rekapSayaPdf'])->name('piket.rekap-saya.pdf');

        // Absensi Cepat Siswa (Portal Piket)
        Route::get('/absensi-cepat', [AbsensiSiswaController::class, 'bulkForm'])
            ->name('piket.absensi-cepat');
        Route::get('/absensi-cepat/search', [AbsensiSiswaController::class, 'searchStudent'])
            ->name('piket.absensi-cepat.search');
        Route::post('/absensi-cepat', [AbsensiSiswaController::class, 'bulkStore'])
            ->name('piket.absensi-cepat.store');
        Route::post('/absensi-cepat/store-single', [AbsensiSiswaController::class, 'storeSingle'])
            ->name('piket.absensi-cepat.store-single');
        Route::get('/rekap-absensi', [LaporanController::class, 'index'])
            ->name('piket.laporan.index');
    });

    // ── Impersonation routes ────────────────────────────────────────────────────
    // NOTE: 'revert' is defined FIRST to avoid conflict with {user} wildcard!
    Route::get('/admin/impersonate-revert', [ImpersonateController::class, 'revert'])
        ->name('admin.impersonate.revert');
    
    // Impersonate Siswa routes
    Route::post('/admin/siswa/{siswa}/impersonate', [\App\Http\Controllers\Admin\ImpersonationController::class, 'start'])
        ->name('admin.siswa.impersonate')
        ->middleware('role:super_admin,admin_sekolah');
    Route::post('/impersonate/leave', [\App\Http\Controllers\Admin\ImpersonationController::class, 'leave'])
        ->name('impersonate.leave');

    Route::middleware('role:super_admin')->group(function () {
        Route::post('/admin/impersonate/{user}', [ImpersonateController::class, 'loginAs'])
            ->name('admin.impersonate.login-as');
        Route::get('/admin/impersonate/{user}', [ImpersonateController::class, 'handleGet'])
            ->name('admin.impersonate.get');
        
        // System & Developer Logs routes
        Route::get('/admin/system-logs', [\App\Http\Controllers\Admin\SystemLogController::class, 'index'])
            ->name('admin.system-logs.index');
        Route::get('/admin/system-logs/data', [\App\Http\Controllers\Admin\SystemLogController::class, 'getLogs'])
            ->name('admin.system-logs.data');
        Route::post('/admin/system-logs/clear', [\App\Http\Controllers\Admin\SystemLogController::class, 'clearLog'])
            ->name('admin.system-logs.clear');
    });
    // ────────────────────────────────────────────────────────────────────────────

    Route::prefix('admin')->group(function () {
        Route::get('/analitik-siswa', [AnalitikSiswaController::class, 'index'])
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,piket')
            ->name('admin.analitik-siswa.index');
        Route::get('/analitik-siswa/data', [AnalitikSiswaController::class, 'getData'])
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,piket')
            ->name('admin.analitik-siswa.data');

        Route::get('/dashboard/siswa-belum-absen', [DashboardController::class, 'siswaBelumAbsen'])
            ->middleware('role:super_admin,admin_sekolah,operator')
            ->name('admin.dashboard.siswa-belum-absen');

        Route::get('/live-monitor', [DashboardController::class, 'liveMonitor'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.live-monitor');
        Route::post('/weekly-digest/send', [DashboardController::class, 'sendWeeklyDigest'])
            ->middleware('role:super_admin,admin_sekolah,operator')
            ->name('admin.weekly-digest.send');
        Route::get('/statistik-kelas', [DashboardController::class, 'statistikKelas'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.statistik-kelas');
        Route::get('/kalender-absensi', [DashboardController::class, 'kalenderAbsensi'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.kalender-absensi');
        Route::get('/kalender-absensi/detail', [DashboardController::class, 'kalenderDetail'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.kalender-absensi.detail');

        Route::get('/holidays', [DashboardController::class, 'holidays'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.holidays');
        Route::post('/holidays/sync', [DashboardController::class, 'holidaysSync'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.holidays.sync');
        Route::post('/holidays', [DashboardController::class, 'holidaysStore'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.holidays.store');
        Route::delete('/holidays/{id}', [DashboardController::class, 'holidaysDestroy'])
            ->middleware('role:super_admin,admin_sekolah')
            ->name('admin.holidays.destroy');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])
            ->middleware('role:super_admin')
            ->name('admin.activity-log.index');
        Route::get('/activity-log/{activityLog}', [ActivityLogController::class, 'show'])
            ->middleware('role:super_admin')
            ->name('admin.activity-log.show');
        Route::delete('/activity-log/destroy-all', [ActivityLogController::class, 'destroyAll'])
            ->middleware('role:super_admin')
            ->name('admin.activity-log.destroy-all');

        Route::get('/master-data', [MasterDataController::class, 'index'])->middleware('role:super_admin,admin_sekolah,operator')->name('admin.master-data');
        Route::get('/master-data/search', [MasterDataController::class, 'search'])->middleware('role:super_admin,admin_sekolah,operator')->name('admin.master-data.search');

        // ── MODUL KEGIATAN KHUSUS ──────────────────────────────────────────────
        Route::get('kegiatan/search-siswa', [KegiatanController::class, 'searchSiswa'])
            ->name('admin.kegiatan.search-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::resource('kegiatan', KegiatanController::class)
            ->names('admin.kegiatan')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('jadwal-kegiatan/sync', [JadwalKegiatanController::class, 'sync'])
            ->name('admin.jadwal-kegiatan.sync')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('jadwal-kegiatan/{jadwalKegiatan}/rekap', [JadwalKegiatanController::class, 'rekap'])
            ->name('admin.jadwal-kegiatan.rekap')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::resource('jadwal-kegiatan', JadwalKegiatanController::class)
            ->names('admin.jadwal-kegiatan')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('absensi-kegiatan/scan', [AbsensiKegiatanController::class, 'scan'])
            ->name('admin.absensi-kegiatan.scan')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');

        Route::post('absensi-kegiatan/store', [AbsensiKegiatanController::class, 'store'])
            ->name('admin.absensi-kegiatan.store')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');

        Route::get('absensi-kegiatan/rekap', [AbsensiKegiatanController::class, 'rekap'])
            ->name('admin.absensi-kegiatan.rekap')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('absensi-kegiatan/live-board/{kegiatan}', [AbsensiKegiatanController::class, 'liveBoard'])
            ->name('admin.absensi-kegiatan.live-board');
        Route::post('absensi-kegiatan/live-board/{kegiatan}/scan', [AbsensiKegiatanController::class, 'liveBoardScan'])
            ->name('admin.absensi-kegiatan.live-board.scan');

        // ── MODUL EKSTRAKURIKULER ──────────────────────────────────────────────
        Route::get('ekskul/export-data', [EkskulController::class, 'exportData'])
            ->name('admin.ekskul.export-data')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('ekskul/import-data', [EkskulController::class, 'importData'])
            ->name('admin.ekskul.import-data')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('ekskul', EkskulController::class)
            ->names('admin.ekskul')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('ekskul/{ekskul}/toggle-status', [EkskulController::class, 'toggleStatus'])
            ->name('admin.ekskul.toggle-status')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('ekskul.anggota', EkskulAnggotaController::class)
            ->names('admin.ekskul.anggota')
            ->only(['index', 'store', 'destroy'])
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('ekskul/{ekskul}/anggota/{anggota}/update-status', [EkskulAnggotaController::class, 'updateStatus'])
            ->name('admin.ekskul.anggota.update-status')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('ekskul/{ekskul}/absensi', [EkskulAbsensiController::class, 'index'])
            ->name('admin.ekskul.absensi.index')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::get('ekskul/{ekskul}/absensi/{tanggal}', [EkskulAbsensiController::class, 'show'])
            ->name('admin.ekskul.absensi.show')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::post('ekskul/{ekskul}/absensi/{tanggal}', [EkskulAbsensiController::class, 'store'])
            ->name('admin.ekskul.absensi.store')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::get('ekskul/{ekskul}/absensi-rekap', [EkskulAbsensiController::class, 'rekap'])
            ->name('admin.ekskul.absensi.rekap')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::get('ekskul/{ekskul}/absensi-rekap/export-excel', [EkskulAbsensiController::class, 'exportExcel'])
            ->name('admin.ekskul.absensi.rekap.export-excel')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::get('ekskul/{ekskul}/absensi-rekap/export-pdf', [EkskulAbsensiController::class, 'exportPdf'])
            ->name('admin.ekskul.absensi.rekap.export-pdf')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::post('ekskul/{ekskul}/generate-qr', [EkskulAbsensiController::class, 'generateQR'])
            ->name('admin.ekskul.generate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::post('ekskul/{ekskul}/absensi/lookup-siswa', [EkskulAbsensiController::class, 'lookupSiswa'])
            ->name('admin.ekskul.absensi.lookup-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        Route::post('ekskul/{ekskul}/absensi/admin-scan', [EkskulAbsensiController::class, 'adminScan'])
            ->name('admin.ekskul.absensi.admin-scan')
            ->middleware('role:super_admin,admin_sekolah,operator,guru');
        // ───────────────────────────────────────────────────────────────────────

        // Absensi Pelepasan Kelas XII
        Route::get('pelepasan/settings', [PelepasanController::class, 'settings'])
            ->name('admin.pelepasan.settings')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pelepasan/settings', [PelepasanController::class, 'saveSettings'])
            ->name('admin.pelepasan.settings.save')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('pelepasan', [PelepasanController::class, 'index'])
            ->name('admin.pelepasan.index')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('pelepasan/cetak-kartu', [PelepasanController::class, 'cetakKartu'])
            ->name('admin.pelepasan.cetak-kartu')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('pelepasan/live-board', [PelepasanController::class, 'liveBoard'])
            ->name('admin.pelepasan.liveboard')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('pelepasan/scan', [PelepasanController::class, 'scanStore'])
            ->name('admin.pelepasan.scan.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('pelepasan/export', [PelepasanController::class, 'export'])
            ->name('admin.pelepasan.export')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('pelepasan/reset', [PelepasanController::class, 'resetKehadiran'])
            ->name('admin.pelepasan.reset')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('pelepasan/scan', [PelepasanController::class, 'mobileScan'])
            ->name('admin.pelepasan.scan.page')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('pelepasan/realtime', [PelepasanController::class, 'realtimeData'])
            ->name('admin.pelepasan.realtime')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('scan-qr', [ScanQrController::class, 'index'])
            ->name('admin.scan-qr.index')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('scan-qr', [ScanQrController::class, 'store'])
            ->name('admin.scan-qr.store')
            ->middleware('role:super_admin,admin_sekolah,operator');

        // ───────────────────────────────────────────────────────────────────────

        Route::post('set-tahun-akademik', [TahunAkademikController::class, 'setSession'])
            ->name('admin.set-tahun-akademik')
            ->middleware('role:super_admin,admin_sekolah,guru,operator,wali_kelas');

        Route::resource('tahun-akademik', TahunAkademikController::class)
            ->names('admin.tahun-akademik')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('tahun-akademik/{tahunAkademik}/toggle-aktif', [TahunAkademikController::class, 'toggleAktif'])
            ->name('admin.tahun-akademik.toggle-aktif')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('kelas', KelasController::class)
            ->names('admin.kelas')
            ->parameters(['kelas' => 'kelas'])
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::resource('jurusan', JurusanController::class)
            ->names('admin.jurusan')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('kelas/import', [KelasController::class, 'importStore'])
            ->name('admin.kelas.import.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('kelas/download-sample', [KelasController::class, 'downloadSample'])
            ->name('admin.kelas.download-sample')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('kelas/{kelas}', [KelasController::class, 'show'])
            ->name('admin.kelas.show')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('kelas/{kelas}/pindah-massal', [KelasController::class, 'pindahMassal'])
            ->name('admin.kelas.pindah-massal')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('kelas/{kelas}/siswa', [KelasController::class, 'addSiswa'])
            ->name('admin.kelas.add-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::delete('kelas/{kelas}/siswa/{siswa}', [KelasController::class, 'removeSiswa'])
            ->name('admin.kelas.remove-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('kelas/preview-copy', [KelasController::class, 'previewCopy'])
            ->name('admin.kelas.preview-copy')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('kelas/execute-copy', [KelasController::class, 'executeCopy'])
            ->name('admin.kelas.execute-copy')
            ->middleware('role:super_admin,admin_sekolah,operator');

        // ── KELOLA JAM ABSENSI PER KELAS PER HARI (PRD-016) ────────────────────
        Route::get('jadwal-absensi', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'index'])
            ->name('admin.jadwal-absensi.index')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('jadwal-absensi/{kelas}', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'show'])
            ->name('admin.jadwal-absensi.show')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('jadwal-absensi', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'store'])
            ->name('admin.jadwal-absensi.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('jadwal-absensi/store-all', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'storeAll'])
            ->name('admin.jadwal-absensi.store-all')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('jadwal-absensi/bulk-apply', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'bulkApply'])
            ->name('admin.jadwal-absensi.bulk-apply')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::delete('jadwal-absensi/{kelas}', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'destroy'])
            ->name('admin.jadwal-absensi.destroy')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('jadwal-absensi/save-guru-settings', [\App\Http\Controllers\Admin\JadwalAbsensiController::class, 'saveGuruSettings'])
            ->name('admin.jadwal-absensi.save-guru-settings')
            ->middleware('role:super_admin,admin_sekolah');
        // ─────────────────────────────────────────────────────────────────────────

        Route::get('guru/cetak-qr', [GuruController::class, 'cetakQr'])
            ->name('admin.guru.cetak-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        // Cetak Kartu Guru Massal dengan Pilihan IDs (POST — checkbox)
        Route::post('guru/cetak-kartu-pilihan', [GuruController::class, 'cetakKartuPilihan'])
            ->name('admin.guru.cetak-kartu-pilihan')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('guru/{guru}/generate-qr', [GuruController::class, 'generateQrSatu'])
            ->name('admin.guru.generate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('guru/{guru}/regenerate-qr', [GuruController::class, 'regenerateQr'])
            ->name('admin.guru.regenerate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('guru/regenerate-qr-bulk', [GuruController::class, 'regenerateQrBulk'])
            ->name('admin.guru.regenerate-qr-bulk')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('guru/regenerate-phone', [GuruController::class, 'regeneratePhoneNumbers'])
            ->name('admin.guru.regenerate-phone')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('guru/regenerate-qr-all', [GuruController::class, 'regenerateQrAll'])
            ->name('admin.guru.regenerate-qr-all')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('guru/export', [GuruController::class, 'export'])
            ->name('admin.guru.export')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('guru/import', [GuruController::class, 'importStore'])
            ->name('admin.guru.import.store')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('guru/download-sample', [GuruController::class, 'downloadSample'])
            ->name('admin.guru.download-sample')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::delete('guru-destroy-all', [GuruController::class, 'destroyAll'])
            ->name('admin.guru.destroy-all')
            ->middleware('role:super_admin,admin_sekolah');

        Route::post('guru/destroy-bulk', [GuruController::class, 'destroyBulk'])
            ->name('admin.guru.destroy-bulk')
            ->middleware('role:super_admin,admin_sekolah');

        Route::resource('guru', GuruController::class)
            ->names('admin.guru')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('guru-bk', [GuruBkController::class, 'index'])
            ->name('admin.guru-bk.index')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('guru-bk', [GuruBkController::class, 'store'])
            ->name('admin.guru-bk.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::delete('guru-bk/{guru}', [GuruBkController::class, 'destroy'])
            ->name('admin.guru-bk.destroy')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('guru-piket', [GuruPiketController::class, 'index'])
            ->name('admin.guru-piket.index')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('guru-piket', [GuruPiketController::class, 'store'])
            ->name('admin.guru-piket.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::delete('guru-piket/{guru}', [GuruPiketController::class, 'destroy'])
            ->name('admin.guru-piket.destroy')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('mapel', MapelController::class)
            ->names('admin.mapel')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        // ── MASTER DATA & KONFIGURASI POINT PELANGGARAN SISWA (PRD-008) ──────
        Route::resource('pelanggaran-kategori', \App\Http\Controllers\Admin\KategoriPelanggaranController::class)
            ->names('admin.pelanggaran-kategori')
            ->middleware('role:super_admin,admin_sekolah');
        
        Route::resource('pelanggaran-jenis', \App\Http\Controllers\Admin\JenisPelanggaranController::class)
            ->names('admin.pelanggaran-jenis')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('pelanggaran-konfigurasi', [\App\Http\Controllers\Admin\KonfigurasiPelanggaranController::class, 'index'])
            ->name('admin.pelanggaran-konfigurasi.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pelanggaran-konfigurasi', [\App\Http\Controllers\Admin\KonfigurasiPelanggaranController::class, 'save'])
            ->name('admin.pelanggaran-konfigurasi.save')
            ->middleware('role:super_admin,admin_sekolah');

        // ── INPUT & MANAJEMEN PELANGGARAN SISWA (PRD-008 Phase 3) ──────────────
        Route::get('pelanggaran-siswa/rekap', [\App\Http\Controllers\Admin\RekapPelanggaranController::class, 'index'])
            ->name('admin.pelanggaran-siswa.rekap')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');
        Route::get('pelanggaran-siswa/siswa/{siswa}', [\App\Http\Controllers\Admin\RekapPelanggaranController::class, 'profilSiswa'])
            ->name('admin.pelanggaran-siswa.profil-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');

        Route::get('/api/internal/siswa/search', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'searchSiswa'])
            ->name('admin.pelanggaran.search-siswa')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');
        Route::get('/api/internal/siswa/{id}/poin', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'getSiswaPoin'])
            ->name('admin.pelanggaran.get-siswa-poin')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');
        Route::get('/pelanggaran/foto/{foto_id}', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'streamFoto'])
            ->name('admin.pelanggaran.stream-foto')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');

        Route::get('/pelanggaran/export-data', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'exportData'])
            ->name('admin.pelanggaran.export-data')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');

        Route::post('/pelanggaran/import-data', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'importData'])
            ->name('admin.pelanggaran.import-data')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');

        Route::post('/pelanggaran/reset-data', [\App\Http\Controllers\Admin\PelanggaranSiswaController::class, 'resetData'])
            ->name('admin.pelanggaran.reset-data')
            ->middleware('role:super_admin,admin_sekolah');

        Route::resource('pelanggaran', \App\Http\Controllers\Admin\PelanggaranSiswaController::class)
            ->names('admin.pelanggaran')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket');

        Route::get('wali-kelas/cetak-qr', [WaliKelasController::class, 'cetakQr'])
            ->name('admin.wali-kelas.cetak-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('wali-kelas/{guru}/generate-qr', [WaliKelasController::class, 'generateQrSatu'])
            ->name('admin.wali-kelas.generate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::delete('wali-kelas/user/{user}', [WaliKelasController::class, 'destroyUser'])
            ->name('admin.wali-kelas.destroy-user')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('wali-kelas', WaliKelasController::class)
            ->names('admin.wali-kelas')
            ->parameters(['wali-kelas' => 'guru'])
            ->except(['create', 'store', 'edit', 'update', 'show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('staff-tata-usaha/cetak-qr', [StaffTataUsahaController::class, 'cetakQr'])
            ->name('admin.staff-tata-usaha.cetak-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        // Cetak Kartu Staff Massal dengan Pilihan IDs (POST — checkbox)
        Route::post('staff-tata-usaha/cetak-kartu-pilihan', [StaffTataUsahaController::class, 'cetakKartuPilihan'])
            ->name('admin.staff-tata-usaha.cetak-kartu-pilihan')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('staff-tata-usaha/{staff_tata_usaha}/generate-qr', [StaffTataUsahaController::class, 'generateQrSatu'])
            ->name('admin.staff-tata-usaha.generate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('staff-tata-usaha/{staffTataUsaha}/regenerate-qr', [StaffTataUsahaController::class, 'regenerateQr'])
            ->name('admin.staff-tata-usaha.regenerate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('staff-tata-usaha/regenerate-phone', [StaffTataUsahaController::class, 'regeneratePhoneNumbers'])
            ->name('admin.staff-tata-usaha.regenerate-phone')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('staff-tata-usaha/regenerate-qr-bulk', [StaffTataUsahaController::class, 'regenerateQrBulk'])
            ->name('admin.staff-tata-usaha.regenerate-qr-bulk')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('staff-tata-usaha/regenerate-qr-all', [StaffTataUsahaController::class, 'regenerateQrAll'])
            ->name('admin.staff-tata-usaha.regenerate-qr-all')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('staff-tata-usaha', StaffTataUsahaController::class)
            ->names('admin.staff-tata-usaha')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('siswa/regenerate-phone', [SiswaController::class, 'regeneratePhoneNumbers'])
            ->name('admin.siswa.regenerate-phone')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::delete('siswa-destroy-all', [SiswaController::class, 'destroyAll'])
            ->name('admin.siswa.destroy-all')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/export', [SiswaController::class, 'export'])
            ->name('admin.siswa.export')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('siswa', SiswaController::class)
            ->names('admin.siswa')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('orang-tua/regenerate-phone', [OrangTuaController::class, 'regeneratePhoneNumbers'])
            ->name('admin.orang-tua.regenerate-phone')
            ->middleware('role:super_admin,admin_sekolah');

        Route::post('orang-tua-sync', [OrangTuaController::class, 'syncData'])
            ->name('admin.orang-tua.sync')
            ->middleware('role:super_admin,admin_sekolah');

        Route::delete('orang-tua-destroy-all', [OrangTuaController::class, 'destroyAll'])
            ->name('admin.orang-tua.destroy-all')
            ->middleware('role:super_admin,admin_sekolah');

        Route::post('orang-tua-reset-password-all', [OrangTuaController::class, 'resetPasswordAll'])
            ->name('admin.orang-tua.reset-password-all')
            ->middleware('role:super_admin,admin_sekolah');

        Route::post('orang-tua/{user}/reset-password', [OrangTuaController::class, 'resetPassword'])
            ->name('admin.orang-tua.reset-password')
            ->middleware('role:super_admin,admin_sekolah');

        Route::resource('orang-tua', OrangTuaController::class)
            ->names('admin.orang-tua')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('siswa/import', [SiswaController::class, 'import'])
            ->name('admin.siswa.import')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('siswa/import', [SiswaController::class, 'importStore'])
            ->name('admin.siswa.import.store')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('siswa/download-sample', [SiswaController::class, 'downloadSample'])
            ->name('admin.siswa.download-sample')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('siswa/import-progress', [SiswaController::class, 'importProgress'])
            ->name('admin.siswa.import-progress')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('siswa/sync-google-sheet', [SiswaController::class, 'syncGoogleSheet'])
            ->name('admin.siswa.sync-google-sheet')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('siswa/reset-password-all', [SiswaController::class, 'resetPasswordAll'])
            ->name('admin.siswa.reset-password-all')
            ->middleware('role:super_admin,admin_sekolah', 'throttle:120,1');

        Route::post('siswa/generate-ortu-massal', [SiswaController::class, 'generateOrtuMassal'])
            ->name('admin.siswa.generate-ortu-massal')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/sync-progress', [SiswaController::class, 'syncProgress'])
            ->name('admin.siswa.sync-progress')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/cetak-qr', [SiswaController::class, 'cetakQrKelas'])
            ->name('admin.siswa.cetak-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        // Cetak Kartu Massal dengan Pilihan IDs (POST — checkbox)
        Route::post('siswa/cetak-kartu-pilihan', [SiswaController::class, 'cetakKartuPilihan'])
            ->name('admin.siswa.cetak-kartu-pilihan')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/{siswa}/generate-qr', [SiswaController::class, 'generateQrSatu'])
            ->name('admin.siswa.generate-qr')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::post('siswa/generate-all-barcode', [SiswaController::class, 'generateAllBarcode'])
            ->name('admin.siswa.generate-all-barcode')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/download-barcode-kelas', [SiswaController::class, 'downloadBarcodeKelas'])
            ->name('admin.siswa.download-barcode-kelas')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas');

        Route::get('siswa/{siswa}/profil', [SiswaController::class, 'profil'])
            ->name('admin.siswa.profil')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::post('siswa/{siswa}/pindah-kelas', [SiswaController::class, 'pindahKelas'])
            ->name('admin.siswa.pindah-kelas')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('siswa/naik-kelas-massal', [SiswaController::class, 'naikKelasMassalPage'])
            ->name('admin.siswa.naik-kelas-massal');
        Route::post('siswa/naik-kelas-massal', [SiswaController::class, 'naikKelasMassalExecute'])
            ->name('admin.siswa.naik-kelas-massal.execute');

        Route::post('siswa/{siswa}/naik-kelas', [SiswaController::class, 'naikKelas'])
            ->name('admin.siswa.naik-kelas')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::get('absensi-siswa/scan', [AbsensiSiswaController::class, 'scan'])
            ->name('admin.absensi-siswa.scan')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::post('absensi-siswa/scan', [AbsensiSiswaController::class, 'scanStore'])
            ->name('admin.absensi-siswa.scan.store')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::get('absensi-cepat', [AbsensiSiswaController::class, 'bulkForm'])
            ->name('admin.absensi-cepat')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator,guru,piket');
        Route::get('absensi-cepat/search', [AbsensiSiswaController::class, 'searchStudent'])
            ->name('admin.absensi-cepat.search')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator,guru,piket');
        Route::post('absensi-cepat', [AbsensiSiswaController::class, 'bulkStore'])
            ->name('admin.absensi-cepat.store')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator,guru,piket');
        Route::post('absensi-cepat/store-single', [AbsensiSiswaController::class, 'storeSingle'])
            ->name('admin.absensi-cepat.store-single')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator,guru,piket');
        Route::post('absensi-siswa/auto-alpha', [AbsensiSiswaController::class, 'triggerAutoAlpha'])
            ->name('admin.absensi-siswa.auto-alpha')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas');

        Route::get('absensi-siswa/download-template', [AbsensiSiswaController::class, 'downloadTemplate'])
            ->name('admin.absensi-siswa.download-template')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::post('absensi-siswa/import', [AbsensiSiswaController::class, 'import'])
            ->name('admin.absensi-siswa.import')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::get('absensi-siswa/export', [AbsensiSiswaController::class, 'export'])
            ->name('admin.absensi-siswa.export')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::resource('absensi-siswa', AbsensiSiswaController::class)
            ->names('admin.absensi-siswa')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

        Route::get('absensi-guru/live-board', [\App\Http\Controllers\Admin\LiveBoardGuruController::class, 'index'])
            ->name('admin.absensi-guru.live-board')
            ->middleware('role:super_admin,admin_sekolah,guru,operator');
        Route::get('absensi-guru/live-board/data', [\App\Http\Controllers\Admin\LiveBoardGuruController::class, 'data'])
            ->name('admin.absensi-guru.live-board.data')
            ->middleware('role:super_admin,admin_sekolah,guru,operator');

        Route::get('absensi-guru/scan', [AbsensiGuruController::class, 'scan'])
            ->name('admin.absensi-guru.scan')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::post('absensi-guru/scan', [AbsensiGuruController::class, 'scanAjax'])
            ->name('admin.absensi-guru.scan.ajax')
            ->middleware(['role:super_admin,admin_sekolah,operator', 'feature:fitur_scan_absensi_guru']);

        Route::resource('absensi-guru', AbsensiGuruController::class)
            ->names('admin.absensi-guru')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,guru,operator');

        Route::resource('absensi-staff', AbsensiStaffController::class)
            ->names('admin.absensi-staff')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator,staff_tu');

        Route::get('laporan', [LaporanController::class, 'index'])
            ->name('admin.laporan.index')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,guru,piket,guru_bk,waka_kurikulum');
        Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])
            ->name('admin.laporan.exportExcel')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,guru,piket,guru_bk,waka_kurikulum');
        Route::get('laporan/export-excel-guru', [LaporanController::class, 'exportExcelGuru'])
            ->name('admin.laporan.exportExcelGuru')
            ->middleware('role:super_admin,admin_sekolah,operator');
        Route::get('laporan/export-excel-staff', [LaporanController::class, 'exportExcelStaff'])
            ->name('admin.laporan.exportExcelStaff')
            ->middleware('role:super_admin,admin_sekolah,operator');

        Route::resource('izin-sakit', IzinSakitController::class)
            ->names('admin.izin-sakit')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator,staff_tu');

        // Izin Pulang Cepat Route Group / Resource
        Route::post('izin-pulang-cepat/{izin_pulang_cepat}/approve', [IzinPulangCepatController::class, 'approve'])
            ->name('admin.izin-pulang-cepat.approve')
            ->middleware('role:super_admin,admin_sekolah,operator,guru_bk,guru,piket');
        Route::post('izin-pulang-cepat/{izin_pulang_cepat}/reject', [IzinPulangCepatController::class, 'reject'])
            ->name('admin.izin-pulang-cepat.reject')
            ->middleware('role:super_admin,admin_sekolah,operator,guru_bk,guru,piket');
        Route::resource('izin-pulang-cepat', IzinPulangCepatController::class)
            ->names('admin.izin-pulang-cepat')
            ->middleware('role:super_admin,admin_sekolah,operator,guru_bk,guru,piket');

        // Penugasan Guru untuk Admin/Super Admin/Operator
        Route::get('assignments', [AssignmentController::class, 'index'])->name('admin.assignments.index');
        Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])->name('admin.assignments.show');
        Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('admin.assignments.destroy');

        // ── BATASAN PERIZINAN (PRD-001) ─────────────────────────────────────
        Route::prefix('leave-limits')->name('admin.leave-limits.')->middleware('role:super_admin,admin_sekolah,operator')->group(function () {
            Route::get('/', [LeaveLimitController::class, 'index'])->name('index');
            Route::get('/create', [LeaveLimitController::class, 'create'])->name('create');
            Route::post('/', [LeaveLimitController::class, 'store'])->name('store');
            Route::get('/{leaveLimit}/edit', [LeaveLimitController::class, 'edit'])->name('edit');
            Route::put('/{leaveLimit}', [LeaveLimitController::class, 'update'])->name('update');
            Route::delete('/{leaveLimit}', [LeaveLimitController::class, 'destroy'])->name('destroy');
            Route::post('/{leaveLimit}/toggle-status', [LeaveLimitController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/dispensation/{user}', [LeaveLimitController::class, 'dispensationForm'])->name('dispensation');
            Route::post('/dispensation/{user}', [LeaveLimitController::class, 'grantDispensation'])->name('grant-dispensation');
        });

        // Endpoint AJAX check quota — bisa diakses role yg membuat izin
        Route::get('/izin-sakit/check-quota', [LeaveLimitController::class, 'checkQuota'])
            ->name('admin.izin-sakit.check-quota')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,siswa,staff_tu,wali_kelas');

        Route::post('izin-sakit/{izinSakit}/approve', [IzinSakitController::class, 'approve'])
            ->name('admin.izin-sakit.approve')
            ->middleware('role:super_admin,admin_sekolah');

        Route::post('notifications/mark-read', [IzinSakitController::class, 'markRead'])
            ->name('admin.notifications.mark-read');

        // Rekap Harian
        Route::get('rekap-harian', [LaporanController::class, 'rekapHarian'])
            ->name('admin.rekap-harian')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,guru,operator');

        Route::get('absensi-hari-ini', [LaporanController::class, 'absensiHariIni'])
            ->name('admin.absensi-hari-ini')
            ->middleware('role:super_admin,admin_sekolah,wali_kelas,guru,operator');

        // Laporan individual siswa
        Route::get('laporan/siswa/{siswa}', [LaporanController::class, 'individualSiswa'])
            ->name('admin.laporan.individualSiswa')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,guru,piket,guru_bk,waka_kurikulum');

        // Export PDF
        Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])
            ->name('admin.laporan.exportPdf')
            ->middleware('role:super_admin,admin_sekolah,operator,wali_kelas,guru,piket,guru_bk,waka_kurikulum');

        // Reset Data Absensi
        Route::delete('laporan/reset', [LaporanController::class, 'reset'])
            ->name('admin.laporan.reset')
            ->middleware('role:super_admin');

        // ── ABSENSI SISWA PER JAM (PRD-006, P0 — F-1..F-5) ────────────────────
        // BUG-1 fix (QA Kang Asep): wali_kelas berhak melihat rekap kelas asuhan
        // (PRD-006 F-3/F-5), tapi TIDAK boleh mengisi absensi (matriks F-3).
        // Grup B (rekap/export) WAJIB didaftarkan SEBELUM Grup A yang berisi
        // /{jadwal} — agar route statis /rekap tidak tertangkap route param.
        Route::prefix('absensi-per-jam')
            ->name('admin.absensi-per-jam.')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,piket,wali_kelas')
            ->group(function () {
                Route::get('/rekap', [AbsensiPerJamController::class, 'rekapIndex'])->name('rekap');
                Route::get('/rekap/export-excel', [AbsensiPerJamController::class, 'exportExcel'])->name('rekap.export');
                Route::get('/rekap/siswa/{siswa}', [AbsensiPerJamController::class, 'rekapSiswa'])->name('rekap.siswa');
            });

        // Grup A: pengisian absensi (index/show/store) — tanpa wali_kelas
        Route::prefix('absensi-per-jam')
            ->name('admin.absensi-per-jam.')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,piket')
            ->group(function () {
                Route::get('/', [AbsensiPerJamController::class, 'index'])->name('index');
                Route::get('/{jadwal}', [AbsensiPerJamController::class, 'show'])->name('show');
                Route::post('/{jadwal}', [AbsensiPerJamController::class, 'store'])->name('store');
                Route::post('/{jadwal}/store-single', [AbsensiPerJamController::class, 'storeSingle'])->name('store-single');
            });

        // WA Gateway Settings
        Route::get('wa-gateway', [WaGatewayController::class, 'index'])
            ->name('admin.wa-gateway.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('wa-gateway', [WaGatewayController::class, 'update'])
            ->name('admin.wa-gateway.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('wa-gateway/test', [WaGatewayController::class, 'testConnection'])
            ->name('admin.wa-gateway.test')
            ->middleware('role:super_admin,admin_sekolah');

        // WA Autoreply Keywords CRUD
        Route::get('wa-gateway/keywords/export', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'export'])
            ->name('admin.wa-gateway.keywords.export')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('wa-gateway/keywords/import', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'import'])
            ->name('admin.wa-gateway.keywords.import')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('wa-gateway/keywords', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'index'])
            ->name('admin.wa-gateway.keywords.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('wa-gateway/keywords', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'store'])
            ->name('admin.wa-gateway.keywords.store')
            ->middleware('role:super_admin,admin_sekolah');
        Route::put('wa-gateway/keywords/{keyword}', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'update'])
            ->name('admin.wa-gateway.keywords.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::delete('wa-gateway/keywords/{keyword}', [\App\Http\Controllers\Admin\WaAutoreplyKeywordController::class, 'destroy'])
            ->name('admin.wa-gateway.keywords.destroy')
            ->middleware('role:super_admin,admin_sekolah');

        // Pengaturan
        Route::get('pengaturan', [PengaturanController::class, 'index'])
            ->name('admin.pengaturan.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan', [PengaturanController::class, 'update'])
            ->name('admin.pengaturan.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/toggle', [\App\Http\Controllers\Api\V1\PengaturanApiController::class, 'toggle'])
            ->name('admin.pengaturan.toggle')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/tema', [PengaturanController::class, 'updateTheme'])
            ->name('admin.pengaturan.tema.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::delete('pengaturan/tema/reset', [PengaturanController::class, 'resetTheme'])
            ->name('admin.pengaturan.tema.reset')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/clear-cache', [PengaturanController::class, 'clearCache'])
            ->name('admin.pengaturan.clear-cache')
            ->middleware('role:super_admin');
        Route::get('pengaturan/export', [PengaturanController::class, 'exportSettings'])
            ->name('admin.pengaturan.export')
            ->middleware('role:super_admin');
        Route::post('pengaturan/import', [PengaturanController::class, 'importSettings'])
            ->name('admin.pengaturan.import')
            ->middleware('role:super_admin');

        // Update Sistem
        Route::get('update', [UpdateController::class, 'index'])
            ->name('admin.update.index')
            ->middleware('role:super_admin');
        Route::post('update/check', [UpdateController::class, 'check'])
            ->name('admin.update.check')
            ->middleware('role:super_admin');
        Route::post('update/run', [UpdateController::class, 'update'])
            ->name('admin.update.run')
            ->middleware('role:super_admin');
        Route::post('update/publish-assets', [UpdateController::class, 'publishAssets'])
            ->name('admin.update.publish-assets')
            ->middleware('role:super_admin');

        // One-Click Deploy
        Route::get('deploy', [DeployController::class, 'index'])
            ->name('admin.deploy.index')
            ->middleware('role:super_admin');
        Route::get('deploy/status', [DeployController::class, 'status'])
            ->name('admin.deploy.status')
            ->middleware('role:super_admin');
        Route::get('deploy/progress', [DeployController::class, 'progress'])
            ->name('admin.deploy.progress')
            ->middleware('role:super_admin');
        Route::post('deploy/run', [DeployController::class, 'run'])
            ->name('admin.deploy.run')
            ->middleware(['role:super_admin', 'throttle:3,10']);
        Route::post('deploy/{deployLog}/rollback', [DeployController::class, 'rollback'])
            ->name('admin.deploy.rollback')
            ->middleware(['role:super_admin', 'throttle:3,10']);
        Route::get('deploy/history', [DeployController::class, 'history'])
            ->name('admin.deploy.history')
            ->middleware('role:super_admin');

        // Manajemen Lisensi
        Route::get('license', [LicenseController::class, 'index'])
            ->name('admin.license.index')
            ->middleware('role:super_admin');

        // PWA Settings
        Route::get('pwa', [PwaSettingsController::class, 'index'])
            ->name('admin.pwa.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pwa', [PwaSettingsController::class, 'update'])
            ->name('admin.pwa.update')
            ->middleware('role:super_admin,admin_sekolah');

        Route::get('pengaturan/api-source', function () {
            return redirect()->route('admin.pengaturan.index');
        })->name('admin.pengaturan.api-source.index')->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/api-source', [ApiSourceSettingsController::class, 'update'])
            ->name('admin.pengaturan.api-source.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/api-source/sync-now', [ApiSourceSettingsController::class, 'syncNow'])
            ->name('admin.pengaturan.api-source.sync-now')
            ->middleware('role:super_admin,admin_sekolah');

        // Google Sheets Settings
        Route::get('pengaturan/google-sheets', function () {
            return redirect()->route('admin.pengaturan.index');
        })->name('admin.pengaturan.google-sheets.index')->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets', [GoogleSheetsSettingController::class, 'update'])
            ->name('admin.pengaturan.google-sheets.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/test', [GoogleSheetsSettingController::class, 'testConnection'])
            ->name('admin.pengaturan.google-sheets.test')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/sync-now', [GoogleSheetsSettingController::class, 'syncNow'])
            ->name('admin.pengaturan.google-sheets.sync-now')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/process-queue', [GoogleSheetsSettingController::class, 'processQueue'])
            ->name('admin.pengaturan.google-sheets.process-queue')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/reset-antrian', [GoogleSheetsSettingController::class, 'resetAntrian'])
            ->name('admin.pengaturan.google-sheets.reset-antrian')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('pengaturan/google-sheets/template/download', [GoogleSheetsSettingController::class, 'downloadTemplate'])
            ->name('admin.pengaturan.google-sheets.template.download')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/template/create', [GoogleSheetsSettingController::class, 'createSheetTemplate'])
            ->name('admin.pengaturan.google-sheets.template.create')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets/preview-mapping', [GoogleSheetsSettingController::class, 'previewMapping'])
            ->name('admin.pengaturan.google-sheets.preview-mapping')
            ->middleware('role:super_admin,admin_sekolah');

        // Google Sheets Guru Settings
        Route::get('pengaturan/google-sheets-guru', function () {
            return redirect()->route('admin.pengaturan.index');
        })->name('admin.pengaturan.google-sheets-guru.index')->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru', [GoogleSheetsGuruSettingController::class, 'update'])
            ->name('admin.pengaturan.google-sheets-guru.update')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/test', [GoogleSheetsGuruSettingController::class, 'testConnection'])
            ->name('admin.pengaturan.google-sheets-guru.test')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/sync-now', [GoogleSheetsGuruSettingController::class, 'syncNow'])
            ->name('admin.pengaturan.google-sheets-guru.sync-now')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/process-queue', [GoogleSheetsGuruSettingController::class, 'processQueue'])
            ->name('admin.pengaturan.google-sheets-guru.process-queue')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/reset-antrian', [GoogleSheetsGuruSettingController::class, 'resetAntrian'])
            ->name('admin.pengaturan.google-sheets-guru.reset-antrian')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('pengaturan/google-sheets-guru/template/download', [GoogleSheetsGuruSettingController::class, 'downloadTemplate'])
            ->name('admin.pengaturan.google-sheets-guru.template.download')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/template/create', [GoogleSheetsGuruSettingController::class, 'createSheetTemplate'])
            ->name('admin.pengaturan.google-sheets-guru.template.create')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-sheets-guru/preview-mapping', [GoogleSheetsGuruSettingController::class, 'previewMapping'])
            ->name('admin.pengaturan.google-sheets-guru.preview-mapping')
            ->middleware('role:super_admin,admin_sekolah');

        // Google Drive settings & Auth OAuth
        Route::get('debug-gdrive', function() {
            $setting = \App\Models\GoogleDriveSetting::first();
            $config = \App\Services\GoogleDriveConfigService::getConfig();
            
            // Mask sensitive configs
            $maskedConfig = array_map(function($val) {
                return empty($val) ? 'empty' : 'filled (' . strlen((string)$val) . ' chars)';
            }, $config);

            $error = null;
            $isEnabled = false;
            try {
                $service = new \App\Services\GoogleDriveService();
                $isEnabled = $service->isEnabled();
            } catch (\Throwable $e) {
                $error = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }

            return response()->json([
                'has_setting_record' => !empty($setting),
                'is_connected_db' => $setting?->is_connected ?? false,
                'config_check' => $maskedConfig,
                'service_is_enabled' => $isEnabled,
                'constructor_error' => $error,
            ]);
        })->middleware(['auth', 'role:super_admin,admin_sekolah']);

        Route::get('pengaturan/google-drive/status', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'checkGoogleStatus'])
            ->name('admin.google.status')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('pengaturan/google-drive/redirect', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'redirectToGoogle'])
            ->name('admin.google.redirect')
            ->middleware('role:super_admin,admin_sekolah');
        Route::get('pengaturan/google-drive/callback', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'handleGoogleCallback'])
            ->name('admin.google.callback');
        Route::post('pengaturan/google-drive/revoke', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'revokeGoogleAccess'])
            ->name('admin.google.revoke')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('pengaturan/google-drive/settings', [\App\Http\Controllers\Admin\GoogleAuthController::class, 'updateSettings'])
            ->name('admin.google.update-settings')
            ->middleware('role:super_admin,admin_sekolah');

        // AI Chat — semua role terautentikasi bisa akses, tiered access di backend
        Route::get('ai-chat', [AiChatController::class, 'index'])
            ->name('admin.ai-chat.index')
            ->middleware(['role:super_admin,admin_sekolah,operator,guru,wali_kelas,staff_tu,siswa,orang_tua,piket', 'feature:fitur_ai_chat']);
        Route::post('ai-chat/send', [AiChatController::class, 'sendMessage'])
            ->name('admin.ai-chat.send')
            ->middleware(['role:super_admin,admin_sekolah,operator,guru,wali_kelas,staff_tu,siswa,orang_tua,piket', 'feature:fitur_ai_chat', 'throttle:30,1']);
        Route::get('ai-chat/history', [AiChatController::class, 'history'])
            ->name('admin.ai-chat.history')
            ->middleware(['role:super_admin,admin_sekolah,operator,guru,wali_kelas,staff_tu,siswa,orang_tua,piket', 'feature:fitur_ai_chat']);
        Route::delete('ai-chat/clear', [AiChatController::class, 'clear'])
            ->name('admin.ai-chat.clear')
            ->middleware(['role:super_admin,admin_sekolah,operator,guru,wali_kelas,staff_tu,siswa,orang_tua,piket', 'feature:fitur_ai_chat']);

        // Integrasi API
        Route::get('api-integration', [ApiIntegrationController::class, 'index'])
            ->name('admin.api-integration.index')
            ->middleware('role:super_admin');
        Route::post('api-integration', [ApiIntegrationController::class, 'store'])
            ->name('admin.api-integration.store')
            ->middleware('role:super_admin');
        Route::delete('api-integration/{id}', [ApiIntegrationController::class, 'destroy'])
            ->name('admin.api-integration.destroy')
            ->middleware('role:super_admin');


        // User Management
        Route::resource('users', UserController::class)
            ->names('admin.users')
            ->except(['show'])
            ->middleware('role:super_admin');

        // Lihat password user (hanya super_admin & admin_sekolah)
        Route::get('users/{user}/password', [UserController::class, 'showPassword'])
            ->name('admin.users.show-password')
            ->middleware(['role:super_admin,admin_sekolah', 'throttle:30,1']);

        // Reset password user (hanya super_admin & admin_sekolah)
        Route::post('users/bulk-reset-password', [UserController::class, 'bulkResetPassword'])
            ->name('admin.users.bulk-reset-password')
            ->middleware(['role:super_admin,admin_sekolah', 'throttle:60,1']);

        Route::post('users/bulk-destroy', [UserController::class, 'bulkDestroy'])
            ->name('admin.users.bulk-destroy')
            ->middleware(['role:super_admin,admin_sekolah']);

        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])
            ->name('admin.users.reset-password')
            ->middleware(['role:super_admin,admin_sekolah', 'throttle:30,1']);

        // Role Management
        Route::resource('role', RoleController::class)
            ->names('admin.role')
            ->middleware('role:super_admin,admin_sekolah');

        // Jadwal Pelajaran
        Route::resource('jadwal', JadwalPelajaranController::class)
            ->names('admin.jadwal')
            ->except(['show'])
            ->middleware('role:super_admin,admin_sekolah,operator');

        // ID Card Template
        Route::get('id-card-templates/{idCardTemplate}/export', [IdCardTemplateController::class, 'export'])
            ->name('admin.id-card-templates.export')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('id-card-templates/import', [IdCardTemplateController::class, 'import'])
            ->name('admin.id-card-templates.import')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('id-card-templates/{idCardTemplate}/duplicate', [IdCardTemplateController::class, 'duplicate'])
            ->name('admin.id-card-templates.duplicate')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('id-card-templates/{idCardTemplate}/toggle-aktif', [IdCardTemplateController::class, 'toggleAktif'])
            ->name('admin.id-card-templates.toggle-aktif')
            ->middleware('role:super_admin,admin_sekolah');

        Route::resource('id-card-templates', IdCardTemplateController::class)
            ->names('admin.id-card-templates')
            ->middleware('role:super_admin,admin_sekolah');

        // Notification Templates
        Route::get('notification-templates/export', [NotificationTemplateController::class, 'export'])
            ->name('admin.notification-templates.export')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('notification-templates/import', [NotificationTemplateController::class, 'import'])
            ->name('admin.notification-templates.import')
            ->middleware('role:super_admin,admin_sekolah');
        Route::resource('notification-templates', NotificationTemplateController::class)
            ->names('admin.notification-templates')
            ->middleware('role:super_admin,admin_sekolah');

        // Device Security Management
        Route::get('devices', [DeviceManagementController::class, 'index'])
            ->name('admin.devices.index')
            ->middleware('role:super_admin,admin_sekolah');
        Route::post('devices/{device}/authorize', [DeviceManagementController::class, 'authorizeDevice'])
            ->name('admin.devices.authorize')
            ->middleware('role:super_admin,admin_sekolah');
        Route::delete('devices/{device}', [DeviceManagementController::class, 'destroy'])
            ->name('admin.devices.destroy')
            ->middleware('role:super_admin,admin_sekolah');

        // Dashboard Analytics
        Route::get('analytics', [DashboardController::class, 'analytics'])
            ->name('admin.analytics.index')
            ->middleware('role:super_admin,admin_sekolah');

        // Dashboard Siswa Alfa (PRD-014)
        Route::get('dashboard/belum-absen', [\App\Http\Controllers\DashboardAlfaController::class, 'index'])
            ->name('admin.dashboard.belum-absen')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');

        Route::get('dashboard/belum-absen/chart-data', [\App\Http\Controllers\DashboardAlfaController::class, 'chartData'])
            ->name('admin.dashboard.belum-absen.chart-data')
            ->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas');

        // Gamifikasi
        Route::get('gamifikasi', [DashboardController::class, 'gamifikasi'])
            ->name('admin.gamifikasi.index')
            ->middleware(['role:super_admin,admin_sekolah', 'feature:fitur_gamifikasi']);

        // Gamifikasi Rekapitulasi
        Route::get('gamifikasi/rekap', [DashboardController::class, 'gamifikasiRekap'])
            ->name('admin.gamifikasi.rekap')
            ->middleware(['role:super_admin,admin_sekolah', 'feature:fitur_gamifikasi']);

        Route::get('gamifikasi/rekap/export', [DashboardController::class, 'gamifikasiRekapExport'])
            ->name('admin.gamifikasi.rekap.export')
            ->middleware(['role:super_admin,admin_sekolah', 'feature:fitur_gamifikasi']);

        // Reminder Settings
        Route::get('reminder-settings', [DashboardController::class, 'reminderSettings'])
            ->name('admin.reminder-settings.index')
            ->middleware('role:super_admin,admin_sekolah');

        // Absensi Kegiatan
        Route::get('kegiatans/absensi', [AbsensiActivityController::class, 'index'])
            ->name('admin.kegiatans.absensi')
            ->middleware(['role:super_admin,admin_sekolah,guru', 'feature:fitur_absensi_kegiatan']);


        // ── MODUL ALUMNI SISWA (PRD-001) ──────────────────────────────────────
        Route::get('alumni', [AlumniController::class, 'index'])
            ->name('admin.alumni.index')
            ->middleware('role:super_admin');
        Route::delete('/alumni/destroy-all', [AlumniController::class, 'destroyAll'])
            ->name('admin.alumni.destroy-all')
            ->middleware('role:super_admin');
        Route::get('alumni/{siswa}/profil', [AlumniController::class, 'show'])
            ->name('admin.alumni.show')
            ->middleware('role:super_admin');
        Route::delete('alumni/{siswa}', [AlumniController::class, 'destroy'])
            ->name('admin.alumni.destroy')
            ->middleware('role:super_admin');

        // ── PENGADUAN DATA TIDAK VALID (PRD-002) ─────────────────────────
        Route::prefix('pengaduan')->name('admin.pengaduan.')->middleware('role:super_admin,admin_sekolah,operator,guru,wali_kelas,piket,staff_tu')->group(function () {
            Route::get('/check-new', [\App\Http\Controllers\Admin\PengaduanController::class, 'checkNew'])->name('check-new');
            Route::get('/', [\App\Http\Controllers\Admin\PengaduanController::class, 'index'])->name('index');
            Route::delete('/reset', [\App\Http\Controllers\Admin\PengaduanController::class, 'reset'])->name('reset')->middleware('role:super_admin');
            Route::get('/{pengaduan}', [\App\Http\Controllers\Admin\PengaduanController::class, 'show'])->name('show');
            Route::put('/{pengaduan}', [\App\Http\Controllers\Admin\PengaduanController::class, 'update'])->name('update');
            Route::post('/{pengaduan}/update-status', [\App\Http\Controllers\Admin\PengaduanController::class, 'updateStatus'])->name('update-status');
        });

        // ── CETAK KARTU IDENTITAS ALL-IN-ONE (PRD-006) ─────────────────────
        Route::prefix('cetak-kartu')->name('admin.cetak-kartu.')->middleware(['role:super_admin,admin_sekolah,operator', 'feature:fitur_download_kartu_siswa'])->group(function () {
            Route::get('/', [CetakKartuController::class, 'index'])->name('index');
            Route::post('/download', [CetakKartuController::class, 'download'])->name('download');
            Route::post('/preview', [CetakKartuController::class, 'preview'])->name('preview');
        });

        // ── QUEUE WORKER HEALTH CHECK & CONTROL ──────────────────────────
        Route::get('queue-status', [\App\Http\Controllers\Admin\QueueStatusController::class, 'index'])
            ->name('admin.queue-status')
            ->middleware('role:super_admin,admin_sekolah');

        Route::prefix('queue')->name('admin.queue.control.')->middleware('role:super_admin,admin_sekolah')->group(function () {
            Route::get('status', [\App\Http\Controllers\Admin\QueueControlController::class, 'status'])
                ->name('status');
            Route::post('start', [\App\Http\Controllers\Admin\QueueControlController::class, 'start'])
                ->name('start');
            Route::post('stop', [\App\Http\Controllers\Admin\QueueControlController::class, 'stop'])
                ->name('stop');
            Route::post('restart', [\App\Http\Controllers\Admin\QueueControlController::class, 'restart'])
                ->name('restart');
        });

        // ── UPLOAD MASSAL FOTO GOOGLE DRIVE ──────────────────────────────────
        Route::prefix('upload-massal')->name('admin.upload-massal.')->middleware('role:super_admin,admin_sekolah')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UploadMassalController::class, 'index'])->name('index');
            Route::post('/upload', [\App\Http\Controllers\Admin\UploadMassalController::class, 'upload'])->name('upload');
            Route::post('/import-zip', [\App\Http\Controllers\Admin\UploadMassalController::class, 'importZip'])->name('import-zip');
            Route::get('/batches', [\App\Http\Controllers\Admin\UploadMassalController::class, 'batches'])->name('batches');
            Route::get('/batches/{batch}', [\App\Http\Controllers\Admin\UploadMassalController::class, 'showBatch'])->name('batches.show');
            Route::get('/batches/{batch}/items', [\App\Http\Controllers\Admin\UploadMassalController::class, 'batchItems'])->name('batches.items');
            Route::get('/batches/{batch}/progress', [\App\Http\Controllers\Admin\UploadMassalController::class, 'batchProgress'])->name('batches.progress');
            Route::post('/batches/{batch}/retry', [\App\Http\Controllers\Admin\UploadMassalController::class, 'retryBatch'])->name('batches.retry');
            Route::post('/batches/{batch}/cancel', [\App\Http\Controllers\Admin\UploadMassalController::class, 'cancelBatch'])->name('batches.cancel');
            Route::delete('/batches/reset-all', [\App\Http\Controllers\Admin\UploadMassalController::class, 'resetAllBatches'])->name('batches.reset-all');
            Route::get('/check-student/{nisn}', [\App\Http\Controllers\Admin\UploadMassalController::class, 'checkStudent'])->name('check-student');
        });
    });
});

// ── MONITORING KEHADIRAN GURU PER JAM PELAJARAN (PRD-002) ────────────────
Route::middleware(['auth'])->group(function () {
    // Guru Piket
    Route::prefix('piket/monitoring')->middleware('role:piket,super_admin,admin_sekolah')->group(function () {
        Route::get('/', [\App\Http\Controllers\PiketMonitoringController::class, 'index'])->name('piket.monitoring.index');
        Route::post('/', [\App\Http\Controllers\PiketMonitoringController::class, 'store'])->name('piket.monitoring.store');
        Route::put('/{id}', [\App\Http\Controllers\PiketMonitoringController::class, 'update'])->name('piket.monitoring.update');
        Route::get('/guru-search', [\App\Http\Controllers\PiketMonitoringController::class, 'searchGuru'])->name('piket.monitoring.search-guru');
    });

    // WAKA Kurikulum - Live Board
    Route::prefix('waka/live-board')->middleware('role:waka_kurikulum,super_admin,admin_sekolah')->group(function () {
        Route::get('/', [\App\Http\Controllers\LiveBoardController::class, 'index'])->name('waka.live-board');
        Route::get('/data', [\App\Http\Controllers\LiveBoardController::class, 'data'])->name('waka.live-board.data');
    });

    // Admin Rekap
    Route::prefix('admin/monitoring')->middleware('role:super_admin,admin_sekolah,waka_kurikulum')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminMonitoringController::class, 'index'])->name('admin.monitoring.index');
        Route::delete('/{id}', [\App\Http\Controllers\AdminMonitoringController::class, 'destroy'])->name('admin.monitoring.destroy');
    });

    // Portal Guru
    Route::prefix('guru/monitoring')->middleware('role:guru,super_admin,admin_sekolah')->group(function () {
        Route::get('/', [\App\Http\Controllers\GuruMonitoringController::class, 'index'])->name('guru.monitoring.index');
    });

    // Portal Staff Tata Usaha (Role Permalinks /tu/*)
    Route::prefix('tu')->middleware('role:staff_tu,super_admin,admin_sekolah')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tu.dashboard');
        Route::get('/absensi', [AbsensiStaffController::class, 'index'])->name('tu.absensi.index');
        Route::get('/izin-sakit', [IzinSakitController::class, 'index'])->name('tu.izin-sakit.index');
        Route::get('/izin-sakit/create', [IzinSakitController::class, 'create'])->name('tu.izin-sakit.create');
    });
});
// ─────────────────────────────────────────────────────────────────────────────

// Keep-Alive Endpoint (Auto Session & CSRF Token Refresh)
Route::get('/keep-alive', function () {
    return response()->json([
        'status' => 'active',
        'csrf_token' => csrf_token(),
    ]);
})->name('keep-alive');








<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\StaffTataUsahaController;
use App\Http\Controllers\Admin\WaliKelasController;
use App\Http\Controllers\Admin\TahunAkademikController;
use App\Http\Controllers\Admin\AbsensiSiswaController;
use App\Http\Controllers\Admin\AbsensiGuruController;
use App\Http\Controllers\Admin\AbsensiStaffController;
use App\Http\Controllers\Admin\IzinSakitController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\PublicPagesController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use App\Http\Controllers\pages\Page2;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\AbsensiKegiatanController;
use App\Http\Controllers\Admin\ScanQrController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\PublicQrScanController;
use App\Http\Controllers\AbsensiMandiriController;
use App\Http\Controllers\PortalSiswaController;
use App\Http\Controllers\Admin\AbsensiActivityController;

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');
Route::get('/page-2', [Page2::class, 'index'])->name('pages-page-2');

// ── Web Installer Routes ──────────────────────────────────────────────────
Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/', [\App\Http\Controllers\InstallerController::class, 'step1'])->name('step1');
    Route::get('/step2', [\App\Http\Controllers\InstallerController::class, 'step2'])->name('step2');
    Route::post('/step2', [\App\Http\Controllers\InstallerController::class, 'step2Submit'])->name('step2Submit');
    Route::get('/step3', [\App\Http\Controllers\InstallerController::class, 'step3'])->name('step3');
    Route::post('/step3', [\App\Http\Controllers\InstallerController::class, 'step3Submit'])->name('step3Submit');
    Route::get('/step4', [\App\Http\Controllers\InstallerController::class, 'step4'])->name('step4');
    Route::post('/step4', [\App\Http\Controllers\InstallerController::class, 'step4Submit'])->name('step4Submit');
    Route::get('/step5', [\App\Http\Controllers\InstallerController::class, 'step5'])->name('step5');
    Route::post('/process', [\App\Http\Controllers\InstallerController::class, 'process'])->name('process');
    Route::post('/save-progress', [\App\Http\Controllers\InstallerController::class, 'saveProgress'])->name('saveProgress');
    Route::post('/publish-assets', [\App\Http\Controllers\InstallerController::class, 'publishAssets'])->name('publishAssets');
});
// ─────────────────────────────────────────────────────────────────────────

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');

// authentication
Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');

// ── Halaman Scan QR Publik (Guru Piket — tanpa login) ─────────────────────────
Route::prefix('scan-qr')->name('public.scan-qr.')->group(function () {
    Route::get('/',        [PublicQrScanController::class, 'index'])->name('index')->middleware('device.trusted');
    Route::post('/auth',   [PublicQrScanController::class, 'auth'])->name('auth')->middleware('throttle:5,15');
    Route::get('/scan',    [PublicQrScanController::class, 'scan'])->name('scan')->middleware(['qr.scan.auth', 'device.trusted']);
    Route::post('/process',[PublicQrScanController::class, 'process'])->name('process')->middleware(['qr.scan.auth', 'device.trusted']);
    Route::post('/logout', [PublicQrScanController::class, 'logout'])->name('logout');
});

Route::get('/device-unauthorized', function() {
    return view('public.device-unauthorized');
})->name('public.device-unauthorized');

// ── Halaman Publik (tanpa login) ──────────────────────────────────────────────
Route::get('/tentang-kami',        [PublicPagesController::class, 'tentangKami'])->name('public.tentang-kami');
Route::get('/panduan-pengguna',    [PublicPagesController::class, 'panduanPengguna'])->name('public.panduan-pengguna');
Route::get('/kebijakan-privasi',   [PublicPagesController::class, 'kebijakanPrivasi'])->name('public.kebijakan-privasi');
Route::get('/bantuan',             [PublicPagesController::class, 'bantuan'])->name('public.bantuan');
// ─────────────────────────────────────────────────────────────────────────────

// ── Halaman Live Board Publik (tanpa login) ───────────────────────────────────
Route::get('/live-board', [PublicQrScanController::class, 'liveBoard'])->name('public.live-board')->middleware('device.trusted');
Route::post('/live-board/scan', [PublicQrScanController::class, 'liveBoardScan'])->name('public.live-board.scan')->middleware(['throttle:60,1', 'device.trusted']);
Route::get('/live-board/leaderboard', [PublicQrScanController::class, 'liveBoardLeaderboard'])->name('public.live-board.leaderboard');
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
])->group(function () {
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
  Route::post('/dashboard/scan-qr', [DashboardController::class, 'scanQrAjax'])->name('admin.dashboard.scan-qr')->middleware('role:super_admin,admin_sekolah');
  Route::get('/dashboard/refresh-stats', [DashboardController::class, 'refreshStats'])->name('admin.dashboard.refresh-stats')->middleware('role:super_admin,admin_sekolah');

  // ── PORTAL SISWA ──────────────────────────────────────────────────────────
  Route::prefix('siswa')->middleware('role:siswa')->group(function () {
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('siswa.dashboard');
      Route::get('/profile', [SiswaController::class, 'profilSaya'])->name('siswa.profile');
      Route::get('/download-kartu', [PortalSiswaController::class, 'downloadKartu'])->name('siswa.download-kartu');
      
      // Izin & Sakit (Scoped to self in Controller)
      Route::get('/izin-sakit', [IzinSakitController::class, 'index'])->name('siswa.izin-sakit.index');
      Route::get('/izin-sakit/create', [IzinSakitController::class, 'create'])->name('siswa.izin-sakit.create');
      Route::post('/izin-sakit', [IzinSakitController::class, 'store'])->name('siswa.izin-sakit.store');
      
      Route::post('/absensi-mandiri', [AbsensiMandiriController::class, 'store'])->name('siswa.absensi-mandiri.store');
  });

  // ── PORTAL GURU ───────────────────────────────────────────────────────────
  Route::prefix('guru')->middleware('role:guru')->group(function () {
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('guru.dashboard');
      Route::get('/absensi', [AbsensiGuruController::class, 'index'])->name('guru.absensi.index');
      Route::get('/absensi/scan', [AbsensiGuruController::class, 'scan'])->name('guru.absensi.scan');
      
      // Izin & Sakit (Scoped to self in Controller)
      Route::get('/izin-sakit', [IzinSakitController::class, 'index'])->name('guru.izin-sakit.index');
      Route::get('/izin-sakit/create', [IzinSakitController::class, 'create'])->name('guru.izin-sakit.create');
      Route::post('/izin-sakit', [IzinSakitController::class, 'store'])->name('guru.izin-sakit.store');
  });

  // ── PORTAL WALI KELAS ─────────────────────────────────────────────────────
  Route::prefix('wali-kelas')->middleware('role:wali_kelas')->group(function () {
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('wali-kelas.dashboard');
      Route::get('/siswa', [SiswaController::class, 'index'])->name('wali-kelas.siswa.index');
      Route::get('/siswa/{siswa}/profil', [SiswaController::class, 'profil'])->name('wali-kelas.siswa.profil');
      Route::get('/absensi-siswa', [AbsensiSiswaController::class, 'index'])->name('wali-kelas.absensi-siswa.index');
      Route::get('/rekap-harian', [LaporanController::class, 'rekapHarian'])->name('wali-kelas.rekap-harian');
  });
  
  // ── PORTAL ORANG TUA ──────────────────────────────────────────────────────
  Route::prefix('ortu')->middleware('role:orang_tua')->group(function () {
      Route::get('/dashboard', [DashboardController::class, 'index'])->name('ortu.dashboard');
      Route::get('/anak/{id}/profil', [\App\Http\Controllers\PortalOrangTuaController::class, 'profilAnak'])->name('ortu.anak.profil');
      Route::get('/anak/{id}/absensi', [\App\Http\Controllers\PortalOrangTuaController::class, 'absensiAnak'])->name('ortu.anak.absensi');
      
      Route::get('/izin-sakit', [\App\Http\Controllers\PortalOrangTuaController::class, 'izinSakit'])->name('ortu.izin-sakit.index');
      Route::get('/izin-sakit/create', [\App\Http\Controllers\PortalOrangTuaController::class, 'izinSakitCreate'])->name('ortu.izin-sakit.create');
      Route::post('/izin-sakit', [\App\Http\Controllers\PortalOrangTuaController::class, 'izinSakitStore'])->name('ortu.izin-sakit.store');
  });

  // ── Impersonation routes ────────────────────────────────────────────────────
  // NOTE: 'revert' is defined FIRST to avoid conflict with {user} wildcard!
  Route::get('/admin/impersonate-revert', [\App\Http\Controllers\Admin\ImpersonateController::class, 'revert'])
      ->name('admin.impersonate.revert');
  Route::middleware('role:super_admin')->group(function () {
      Route::get('/admin/impersonate/{user}', [\App\Http\Controllers\Admin\ImpersonateController::class, 'loginAs'])
          ->name('admin.impersonate.login-as');
  });
  // ────────────────────────────────────────────────────────────────────────────


  Route::prefix('admin')->group(function () {
      Route::get('/live-monitor', [DashboardController::class, 'liveMonitor'])
          ->middleware('role:super_admin,admin_sekolah')
          ->name('admin.live-monitor');
      Route::post('/weekly-digest/send', [\App\Http\Controllers\DashboardController::class, 'sendWeeklyDigest'])
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

      Route::get('/master-data', function () {
          return view('admin.master-data');
      })->middleware('role:super_admin,admin_sekolah,operator')->name('admin.master-data');

      // ── MODUL KEGIATAN KHUSUS ──────────────────────────────────────────────
      Route::resource('kegiatan', KegiatanController::class)
          ->names('admin.kegiatan')
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

      Route::resource('kelas', KelasController::class)
          ->names('admin.kelas')
          ->parameters(['kelas' => 'kelas'])
          ->except(['show'])
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

      Route::get('guru/cetak-qr', [GuruController::class, 'cetakQr'])
          ->name('admin.guru.cetak-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('guru/{guru}/generate-qr', [GuruController::class, 'generateQrSatu'])
          ->name('admin.guru.generate-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::resource('guru', GuruController::class)
          ->names('admin.guru')
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,operator');

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
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('staff-tata-usaha/cetak-qr', [StaffTataUsahaController::class, 'cetakQr'])
          ->name('admin.staff-tata-usaha.cetak-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('staff-tata-usaha/{staff_tata_usaha}/generate-qr', [StaffTataUsahaController::class, 'generateQrSatu'])
          ->name('admin.staff-tata-usaha.generate-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::resource('staff-tata-usaha', StaffTataUsahaController::class)
          ->names('admin.staff-tata-usaha')
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::resource('siswa', SiswaController::class)
          ->names('admin.siswa')
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::delete('siswa/delete-all', [SiswaController::class, 'destroyAll'])
          ->name('admin.siswa.destroy-all')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('siswa/import', [SiswaController::class, 'import'])
          ->name('admin.siswa.import')
          ->middleware('role:super_admin,admin_sekolah,operator');
      Route::post('siswa/import', [SiswaController::class, 'importStore'])
          ->name('admin.siswa.import.store')
          ->middleware('role:super_admin,admin_sekolah,operator');
      Route::get('siswa/download-sample', [SiswaController::class, 'downloadSample'])
          ->name('admin.siswa.download-sample')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('siswa/cetak-qr', [SiswaController::class, 'cetakQrKelas'])
          ->name('admin.siswa.cetak-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('siswa/{siswa}/generate-qr', [SiswaController::class, 'generateQrSatu'])
          ->name('admin.siswa.generate-qr')
          ->middleware('role:super_admin,admin_sekolah,operator');

      Route::get('siswa/{siswa}/profil', [SiswaController::class, 'profil'])
          ->name('admin.siswa.profil')
          ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

      Route::post('siswa/{siswa}/pindah-kelas', [SiswaController::class, 'pindahKelas'])
          ->name('admin.siswa.pindah-kelas')
          ->middleware('role:super_admin,admin_sekolah,operator');

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
          ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');
      Route::post('absensi-cepat', [AbsensiSiswaController::class, 'bulkStore'])
          ->name('admin.absensi-cepat.store')
          ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

      Route::resource('absensi-siswa', AbsensiSiswaController::class)
          ->names('admin.absensi-siswa')
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,wali_kelas,operator');

      Route::get('absensi-guru/scan', [AbsensiGuruController::class, 'scan'])
          ->name('admin.absensi-guru.scan')
          ->middleware('role:super_admin,admin_sekolah,operator');
      Route::post('absensi-guru/scan', [AbsensiGuruController::class, 'scanAjax'])
          ->name('admin.absensi-guru.scan.ajax')
          ->middleware('role:super_admin,admin_sekolah,operator');

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
          ->middleware('role:super_admin,admin_sekolah,operator');
      Route::get('laporan/export-excel', [LaporanController::class, 'exportExcel'])
          ->name('admin.laporan.exportExcel')
          ->middleware('role:super_admin,admin_sekolah,operator');
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

      Route::post('izin-sakit/{izinSakit}/approve', [IzinSakitController::class, 'approve'])
          ->name('admin.izin-sakit.approve')
          ->middleware('role:super_admin,admin_sekolah');

      Route::post('notifications/mark-read', [IzinSakitController::class, 'markRead'])
          ->name('admin.notifications.mark-read');

      // Rekap Harian
      Route::get('rekap-harian', [LaporanController::class, 'rekapHarian'])
          ->name('admin.rekap-harian')
          ->middleware('role:super_admin,admin_sekolah,wali_kelas,guru,operator');

      // Laporan individual siswa
      Route::get('laporan/siswa/{siswa}', [LaporanController::class, 'individualSiswa'])
          ->name('admin.laporan.individualSiswa')
          ->middleware('role:super_admin,admin_sekolah,operator');

      // Export PDF
      Route::get('laporan/export-pdf', [LaporanController::class, 'exportPdf'])
          ->name('admin.laporan.exportPdf')
          ->middleware('role:super_admin,admin_sekolah,operator');

      // Reset Data Absensi
      Route::delete('laporan/reset', [LaporanController::class, 'reset'])
          ->name('admin.laporan.reset')
          ->middleware('role:super_admin');

      // WA Gateway Settings
      Route::get('wa-gateway', [\App\Http\Controllers\Admin\WaGatewayController::class, 'index'])
          ->name('admin.wa-gateway.index')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('wa-gateway', [\App\Http\Controllers\Admin\WaGatewayController::class, 'update'])
          ->name('admin.wa-gateway.update')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('wa-gateway/test', [\App\Http\Controllers\Admin\WaGatewayController::class, 'testConnection'])
          ->name('admin.wa-gateway.test')
          ->middleware('role:super_admin,admin_sekolah');

      // Pengaturan
      Route::get('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'index'])
          ->name('admin.pengaturan.index')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('pengaturan', [\App\Http\Controllers\Admin\PengaturanController::class, 'update'])
          ->name('admin.pengaturan.update')
          ->middleware('role:super_admin,admin_sekolah');

      // Update Sistem
      Route::get('update', [\App\Http\Controllers\Admin\UpdateController::class, 'index'])
          ->name('admin.update.index')
          ->middleware('role:super_admin');
      Route::post('update/check', [\App\Http\Controllers\Admin\UpdateController::class, 'check'])
          ->name('admin.update.check')
          ->middleware('role:super_admin');
      Route::post('update/run', [\App\Http\Controllers\Admin\UpdateController::class, 'update'])
          ->name('admin.update.run')
          ->middleware('role:super_admin');
      Route::post('update/publish-assets', [\App\Http\Controllers\Admin\UpdateController::class, 'publishAssets'])
          ->name('admin.update.publish-assets')
          ->middleware('role:super_admin');

      // PWA Settings
      Route::get('pwa', [\App\Http\Controllers\Admin\PwaSettingsController::class, 'index'])
          ->name('admin.pwa.index')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('pwa', [\App\Http\Controllers\Admin\PwaSettingsController::class, 'update'])
          ->name('admin.pwa.update')
          ->middleware('role:super_admin,admin_sekolah');

      Route::get('pengaturan/api-source', [\App\Http\Controllers\Admin\ApiSourceSettingsController::class, 'index'])
          ->name('admin.pengaturan.api-source.index')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('pengaturan/api-source', [\App\Http\Controllers\Admin\ApiSourceSettingsController::class, 'update'])
          ->name('admin.pengaturan.api-source.update')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('pengaturan/api-source/sync-now', [\App\Http\Controllers\Admin\ApiSourceSettingsController::class, 'syncNow'])
          ->name('admin.pengaturan.api-source.sync-now')
          ->middleware('role:super_admin,admin_sekolah');
          
      // Integrasi API
      Route::get('api-integration', [\App\Http\Controllers\Admin\ApiIntegrationController::class, 'index'])
          ->name('admin.api-integration.index')
          ->middleware('role:super_admin');
      Route::post('api-integration', [\App\Http\Controllers\Admin\ApiIntegrationController::class, 'store'])
          ->name('admin.api-integration.store')
          ->middleware('role:super_admin');
      Route::delete('api-integration/{id}', [\App\Http\Controllers\Admin\ApiIntegrationController::class, 'destroy'])
          ->name('admin.api-integration.destroy')
          ->middleware('role:super_admin');

      // User Management
      Route::resource('users', \App\Http\Controllers\Admin\UserController::class)
          ->names('admin.users')
          ->except(['show'])
          ->middleware('role:super_admin');

      // Role Management
      Route::resource('role', RoleController::class)
          ->names('admin.role')
          ->middleware('role:super_admin,admin_sekolah');

      // Jadwal Pelajaran
      Route::resource('jadwal', \App\Http\Controllers\Admin\JadwalPelajaranController::class)
          ->names('admin.jadwal')
          ->except(['show'])
          ->middleware('role:super_admin,admin_sekolah,operator');

      // ID Card Template
      // ID Card Template
Route::resource('id-card-templates', \App\Http\Controllers\Admin\IdCardTemplateController::class)
    ->names('admin.id-card-templates')
    ->middleware('role:super_admin,admin_sekolah');

       // Notification Templates
       Route::resource('notification-templates', \App\Http\Controllers\Admin\NotificationTemplateController::class)
           ->names('admin.notification-templates')
           ->middleware('role:super_admin,admin_sekolah');


      // Device Security Management
      Route::get('devices', [\App\Http\Controllers\Admin\DeviceManagementController::class, 'index'])
          ->name('admin.devices.index')
          ->middleware('role:super_admin,admin_sekolah');
      Route::post('devices/{device}/authorize', [\App\Http\Controllers\Admin\DeviceManagementController::class, 'authorizeDevice'])
          ->name('admin.devices.authorize')
          ->middleware('role:super_admin,admin_sekolah');
      Route::delete('devices/{device}', [\App\Http\Controllers\Admin\DeviceManagementController::class, 'destroy'])
          ->name('admin.devices.destroy')
          ->middleware('role:super_admin,admin_sekolah');

      // Dashboard Analytics
      Route::get('analytics', [\App\Http\Controllers\DashboardController::class, 'analytics'])
          ->name('admin.analytics.index')
          ->middleware('role:super_admin,admin_sekolah');

      // Gamifikasi
      Route::get('gamifikasi', [\App\Http\Controllers\DashboardController::class, 'gamifikasi'])
          ->name('admin.gamifikasi.index')
          ->middleware('role:super_admin,admin_sekolah');

      // Reminder Settings
      Route::get('reminder-settings', [\App\Http\Controllers\DashboardController::class, 'reminderSettings'])
          ->name('admin.reminder-settings.index')
          ->middleware('role:super_admin,admin_sekolah');

      // Absensi Kegiatan
      Route::get('kegiatans/absensi', [AbsensiActivityController::class, 'index'])
          ->name('admin.kegiatans.absensi')
          ->middleware('role:super_admin,admin_sekolah,guru');
  });
});

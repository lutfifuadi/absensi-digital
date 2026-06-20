<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ── License Verification (dipanggil oleh installer klien) ─────────────────────
Route::post('/license/verify', [\App\Http\Controllers\Api\LicenseVerifyController::class, 'verify'])
    ->middleware('throttle:30,1')
    ->name('api.license.verify');
// ─────────────────────────────────────────────────────────────────────────────

// Jalur Sinkronisasi (Dilindungi oleh token Sanctum)
Route::prefix('v1/sync')->middleware(['auth:sanctum', 'tenant'])->group(function () {
    Route::post('/siswa', [\App\Http\Controllers\Api\V1\SiswaSyncController::class, 'sync']);
    Route::post('/guru', [\App\Http\Controllers\Api\V1\GuruSyncController::class, 'sync']);
    Route::post('/staff', [\App\Http\Controllers\Api\V1\StaffTuSyncController::class, 'sync']);
    Route::post('/kelas', [\App\Http\Controllers\Api\V1\KelasSyncController::class, 'sync']);
    Route::post('/tahun-akademik', [\App\Http\Controllers\Api\V1\TahunAkademikSyncController::class, 'sync']);
});

// Webhook PMBM (Push dari sistem pendaftaran, dilindungi oleh X-API-KEY header)
Route::prefix('v1/pmbm')->middleware('pmbm.api.key')->group(function () {
    Route::post('/presensi', [\App\Http\Controllers\Api\V1\PmbmWebhookController::class, 'receive']);
});

// Inovasi Presensi
Route::prefix('v1/innovation')->middleware(['tenant'])->group(function () {
    Route::get('/notification-templates', [\App\Http\Controllers\Api\InnovationController::class, 'getNotificationTemplates']);
    Route::put('/notification-templates/{id}', [\App\Http\Controllers\Api\InnovationController::class, 'updateNotificationTemplate']);
    
    Route::get('/analytics', [\App\Http\Controllers\Api\InnovationController::class, 'getAttendanceAnalytics']);
    Route::post('/analytics/analyze', [\App\Http\Controllers\Api\InnovationController::class, 'analyzeAttendance']);
    
    Route::get('/badges', [\App\Http\Controllers\Api\InnovationController::class, 'getBadges']);
    Route::get('/badges/history', [\App\Http\Controllers\Api\InnovationController::class, 'getStudentBadgesHistory']);
    Route::post('/badges', [\App\Http\Controllers\Api\InnovationController::class, 'storeBadge']);
    Route::post('/badges/assign', [\App\Http\Controllers\Api\InnovationController::class, 'assignBadge']);
    
    Route::get('/leaderboard', [\App\Http\Controllers\Api\InnovationController::class, 'getLeaderboard']);
    Route::post('/leaderboard/calculate', [\App\Http\Controllers\Api\InnovationController::class, 'calculateLeaderboard']);
    
    Route::post('/offline/queue', [\App\Http\Controllers\Api\InnovationController::class, 'queueOfflineEvent']);
    Route::post('/offline/sync', [\App\Http\Controllers\Api\InnovationController::class, 'syncOfflineEvents']);
    
    Route::get('/reminder-settings', [\App\Http\Controllers\Api\InnovationController::class, 'getReminderSettings']);
    Route::put('/reminder-settings/{id}', [\App\Http\Controllers\Api\InnovationController::class, 'updateReminderSettings']);
    Route::post('/reminder/send', [\App\Http\Controllers\Api\InnovationController::class, 'sendReminder']);
    
    Route::get('/activity-attendance', [\App\Http\Controllers\Api\InnovationController::class, 'getActivityAttendance']);
    Route::post('/activity-attendance', [\App\Http\Controllers\Api\InnovationController::class, 'recordActivityAttendance']);
    
    Route::put('/device/offline-mode/{id}', [\App\Http\Controllers\Api\InnovationController::class, 'updateDeviceOfflineMode']);

    Route::prefix('holidays')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\HolidayController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\HolidayController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\HolidayController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\HolidayController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\HolidayController::class, 'destroy']);
        Route::get('/check/{date}', [\App\Http\Controllers\Api\HolidayController::class, 'checkHoliday']);
    });
});

// ── Ekskul Absensi Scan QR (publik — digunakan oleh halaman scan siswa) ──
Route::prefix('ekskul/absensi')->group(function () {
    Route::post('/scan/{token}', [\App\Http\Controllers\Api\EkskulAbsensiScanController::class, 'scan'])
        ->name('api.ekskul.absensi.scan')
        ->middleware('throttle:30,1');
});

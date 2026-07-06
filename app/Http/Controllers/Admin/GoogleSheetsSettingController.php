<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GoogleSheetsSyncJob;
use App\Models\GoogleSheetSetting;
use App\Services\GoogleSheetsService;
use App\Services\GoogleSheetTemplateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSettingController extends Controller
{
    public function index(Request $request)
    {
        $setting = GoogleSheetSetting::where('type', 'siswa')->first() ?? GoogleSheetSetting::whereNull('type')->first() ?? new GoogleSheetSetting;

        // Force return JSON if ajax/expectsJson/wantsJson or any XMLHttpRequest/json accept header is present
        if ($request->wantsJson() || $request->ajax() || $request->expectsJson() || str_contains($request->headers->get('Accept') ?? '', 'application/json') || $request->header('X-Requested-With') === 'XMLHttpRequest' || str_contains($request->header('Accept') ?? '', 'application/json') || $request->has('ajax') || $request->ajax() || str_contains($request->headers->get('X-Requested-With') ?? '', 'XMLHttpRequest') || $request->isXmlHttpRequest() || str_contains($request->headers->get('accept') ?? '', 'application/json') || $request->query('ajax') == 1) {
            return response()->json([
                'id' => $setting->id,
                'last_sync_at' => $setting->last_sync_at ? Carbon::parse($setting->last_sync_at)->format('d M Y H:i:s') : '-',
                'last_sync_status' => $setting->last_sync_status,
                'status_badge_text' => $setting->status_badge_text,
                'last_sync_message' => $setting->last_sync_message,
                'sync_total_rows' => $setting->sync_total_rows,
                'sync_processed_rows' => $setting->sync_processed_rows,
            ]);
        }

        return redirect()->route('admin.pengaturan.index', ['tab' => 'google-sheets-siswa']);
    }

    public function update(Request $request)
    {
        $rules = [
            'spreadsheet_id' => 'required|string|max:255',
            'sheet_range' => 'required|string|max:50',
        ];

        $setting = GoogleSheetSetting::first();

        if (! $setting) {
            $rules['credentials_json'] = 'required|json';
        } else {
            $rules['credentials_json'] = 'nullable|json';
        }

        $validated = $request->validate($rules);

        $data = [
            'spreadsheet_id' => $validated['spreadsheet_id'],
            'sheet_range' => $validated['sheet_range'],
        ];

        if ($request->filled('credentials_json')) {
            $data['credentials_json'] = $validated['credentials_json'];
        }

        if ($setting) {
            $setting->update($data);
        } else {
            $data['is_active'] = true;
            GoogleSheetSetting::create($data);
        }

        return back()->with('success', 'Pengaturan Google Sheets berhasil disimpan.');
    }

    public function testConnection(Request $request)
    {
        $rules = [
            'spreadsheet_id' => 'required|string|max:255',
            'sheet_range' => 'nullable|string|max:50',
        ];

        $setting = GoogleSheetSetting::first();

        if ($request->filled('credentials_json')) {
            $rules['credentials_json'] = 'json';
        }

        $validated = $request->validate($rules);

        $credentialsJson = $request->filled('credentials_json')
            ? $validated['credentials_json']
            : ($setting ? $setting->credentials_json : null);

        if (empty($credentialsJson)) {
            return response()->json([
                'success' => false,
                'message' => 'Credentials JSON tidak valid atau rusak (kemungkinan karena perubahan kunci aplikasi APP_KEY). Silakan isi/upload ulang Service Account JSON Anda.',
            ], 422);
        }

        try {
            $service = new GoogleSheetsService;
            $result = $service->testConnection([
                'spreadsheet_id' => $validated['spreadsheet_id'],
                'credentials_json' => $credentialsJson,
                'sheet_range' => $validated['sheet_range'] ?? 'Sheet1!A1:Z1',
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Test koneksi Google Sheets error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal. Silakan periksa konfigurasi dan coba lagi.',
            ], 500);
        }
    }

    public function syncNow(Request $request)
    {
        $setting = GoogleSheetSetting::first();

        if (! $setting || ! $setting->is_active) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi Google Sheets belum diatur atau tidak aktif.',
                ], 422);
            }

            return back()->with('sync_error', 'Konfigurasi Google Sheets belum diatur atau tidak aktif.');
        }

        if (empty($setting->credentials_json)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.',
                ], 422);
            }

            return back()->with('sync_error', 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.');
        }

        if ($setting->last_sync_status === 'in_progress') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sinkronisasi sedang berlangsung. Tunggu hingga selesai sebelum memulai ulang.',
                ], 409);
            }

            return back()->with('sync_error', 'Sinkronisasi sedang berlangsung. Tunggu hingga selesai sebelum memulai ulang.');
        }

        try {
            $setting->update([
                'last_sync_status' => 'in_progress',
                'last_sync_message' => 'Menjadwalkan sinkronisasi...',
                'sync_total_rows' => 0,
                'sync_processed_rows' => 0,
                'sync_offset' => 0,
            ]);

            GoogleSheetsSyncJob::dispatch($setting->id, 0);

            Log::info('Sinkronisasi Google Sheets manual telah dijadwalkan.');

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang. Proses akan berlanjut meskipun halaman ditutup.',
                ]);
            }

            return back()->with('sync_success', 'Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang. Proses akan berlanjut meskipun halaman ditutup.');
        } catch (\Exception $e) {
            Log::error('Sinkronisasi Google Sheets gagal dijadwalkan.', ['error' => $e->getMessage()]);

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Sinkronisasi gagal dijadwalkan.'], 500);
            }

            return back()->with('sync_error', 'Sinkronisasi gagal. Silakan periksa log sistem untuk detail lebih lanjut.');
        }
    }

    /**
     * Proses antrian queue sinkronisasi.
     * Menjalankan queue worker satu kali untuk memproses job yang tertunda.
     */
    public function processQueue(Request $request)
    {
        abort_if(! $request->ajax(), 403);

        try {
            Artisan::call('queue:work', [
                'connection' => 'database',
                '--once' => true,
                '--queue' => 'syncs',
            ]);

            $output = Artisan::output();

            // Refresh setting untuk cek status terbaru
            $setting = GoogleSheetSetting::first();

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil diproses.',
                'output' => $output,
                'last_sync_status' => $setting?->last_sync_status,
                'last_sync_message' => $setting?->last_sync_message,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal memproses antrian queue', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses antrian: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset antrian sinkronisasi Google Sheets.
     * Menghapus job di queue 'syncs' yang terkait dengan setting siswa saja,
     * agar tidak menghapus job milik setting guru.
     */
    public function resetAntrian(Request $request)
    {
        abort_if(! $request->ajax(), 403);

        try {
            // 1. Ambil semua setting siswa (type != 'guru' atau type = null/siswa)
            $siswaSetting = GoogleSheetSetting::where(function ($query) {
                $query->where('type', 'siswa')
                    ->orWhereNull('type')
                    ->orWhere('type', '!=', 'guru');
            })->get();

            $siswaSettingIds = $siswaSetting->pluck('id')->toArray();
            $deletedJobsCount = 0;

            if (! empty($siswaSettingIds)) {
                // Baca semua job di queue 'syncs'
                $jobs = DB::table('jobs')
                    ->where('queue', 'syncs')
                    ->get(['id', 'payload']);

                // Filter hanya job yang merujuk ke setting_id milik siswa
                $jobIdsToDelete = [];
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    if (! empty($payload['data']['command'])) {
                        // Ekstrak settingId dari serialized PHP command object
                        if (preg_match('/settingId\";i:(\d+);/', $payload['data']['command'], $matches)) {
                            if (in_array((int) $matches[1], $siswaSettingIds)) {
                                $jobIdsToDelete[] = $job->id;
                            }
                        }
                    }
                }

                if (! empty($jobIdsToDelete)) {
                    $deletedJobsCount = DB::table('jobs')
                        ->whereIn('id', $jobIdsToDelete)
                        ->delete();
                }
            }

            // 2. Reset status sinkronisasi di model GoogleSheetSetting (type siswa)
            $setting = GoogleSheetSetting::where(function ($query) {
                $query->where('type', 'siswa')
                    ->orWhereNull('type')
                    ->orWhere('type', '!=', 'guru');
            })->first();

            if ($setting) {
                $setting->update([
                    'last_sync_status' => null,
                    'last_sync_message' => 'Sinkronisasi dibatalkan dan antrian di-reset.',
                    'sync_total_rows' => null,
                    'sync_processed_rows' => null,
                    'sync_offset' => null,
                ]);
            }

            Log::info('Google Sheets Siswa sync queue and status reset by admin.', [
                'deleted_jobs' => $deletedJobsCount,
                'siswa_setting_ids' => $siswaSettingIds,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Antrian berhasil di-reset dan status dikembalikan ke idle.',
                'deleted_jobs' => $deletedJobsCount,
                'last_sync_status' => null,
                'last_sync_message' => 'Sinkronisasi dibatalkan dan antrian di-reset.',
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal mereset antrian Google Sheets Siswa', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset antrian: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download template Excel (.xlsx) dengan 13 kolom standar + 2 baris data sampel.
     */
    public function downloadTemplate()
    {
        $service = new GoogleSheetTemplateService;

        return $service->downloadTemplate();
    }

    /**
     * Buat Google Sheet template (atau update spreadsheet yang sudah ada) dengan header standar.
     * Hanya untuk super_admin. Ajax-only.
     */
    public function createSheetTemplate(Request $request)
    {
        abort_if(! $request->ajax(), 403);

        $setting = GoogleSheetSetting::first();

        if (! $setting || ! $setting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi Google Sheets belum diatur atau tidak aktif.',
            ], 422);
        }

        if (empty($setting->credentials_json)) {
            return response()->json([
                'success' => false,
                'message' => 'Credentials JSON tidak valid. Silakan upload ulang Service Account JSON.',
            ], 422);
        }

        try {
            $service = new GoogleSheetTemplateService;
            $result = $service->createGoogleSheet(
                ['credentials_json' => $setting->credentials_json],
                $setting->spreadsheet_id
            );

            return response()->json([
                'success' => true,
                'spreadsheet_id' => $result['spreadsheet_id'],
                'url' => $result['url'],
            ]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Gagal membuat template Google Sheet', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dapatkan preview mapping dari header sheet ke kolom database.
     * Ajax-only.
     */
    public function previewMapping(Request $request)
    {
        abort_if(! $request->ajax(), 403);

        $setting = GoogleSheetSetting::first();

        if (! $setting || ! $setting->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Konfigurasi Google Sheets belum diatur atau tidak aktif.',
            ], 422);
        }

        try {
            $config = [
                'spreadsheet_id' => $setting->spreadsheet_id,
                'sheet_range' => $setting->sheet_range,
                'credentials_json' => $setting->credentials_json,
                'column_mapping' => $setting->column_mapping,
            ];

            $service = new GoogleSheetsService;
            $result = $service->getMappingPreview($config);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::channel('daily')->error('Gagal mendapatkan preview mapping', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan preview mapping: '.$e->getMessage(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GoogleSheetsSyncJob;
use App\Models\GoogleSheetSetting;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSettingController extends Controller
{
    public function index(Request $request)
    {
        $setting = GoogleSheetSetting::first() ?? new GoogleSheetSetting;

        if ($request->wantsJson() || $request->ajax()) {
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

        return view('admin.pengaturan.google-sheets', compact('setting'));
    }

    public function update(Request $request)
    {
        $rules = [
            'spreadsheet_id' => 'required|string|max:255',
            'sheet_range' => 'required|string|max:50',
            'column_mapping' => 'nullable|json',
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

        if ($request->filled('column_mapping')) {
            $data['column_mapping'] = json_decode($validated['column_mapping'], true);
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
}

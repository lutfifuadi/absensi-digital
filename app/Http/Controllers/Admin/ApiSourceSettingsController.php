<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncMasterDataJob;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiSourceSettingsController extends Controller
{
    public function index()
    {
        $keys = [
            'master_db_sync_enabled',
            'master_db_sync_mode',
            'master_db_sync_time',
            'master_db_api_url',
            'master_db_api_key',
        ];

        $settings = Pengaturan::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        // Default jika belum ada
        $settings['master_db_sync_mode'] = $settings['master_db_sync_mode'] ?? 'otomatis';

        return view('admin.pengaturan.api-source', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'master_db_sync_enabled' => 'required|in:Ya,Tidak',
            'master_db_sync_mode'    => 'required|in:otomatis,manual',
            'master_db_sync_time'    => 'required_if:master_db_sync_mode,otomatis|nullable|date_format:H:i',
            'master_db_api_url'      => 'required|url',
            'master_db_api_key'      => 'nullable|string|max:255',
        ]);

        $settingsToSave = [
            'master_db_sync_enabled' => $validated['master_db_sync_enabled'],
            'master_db_sync_mode'    => $validated['master_db_sync_mode'],
            'master_db_sync_time'    => $validated['master_db_sync_time'] ?? '03:00',
            'master_db_api_url'      => $validated['master_db_api_url'],
        ];

        if ($request->filled('master_db_api_key')) {
            $settingsToSave['master_db_api_key'] = $validated['master_db_api_key'];
        }

        $schoolId = app()->has('current_school') ? app('current_school')->id : \App\Models\School::first()->id;

        foreach ($settingsToSave as $key => $value) {
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'api_source', 'school_id' => $schoolId]
            );
        }

        return back()->with('success', 'Pengaturan API sumber data berhasil disimpan.');
    }

    public function syncNow()
    {
        $apiUrl = Pengaturan::where('key', 'master_db_api_url')->value('value');
        $apiKey = Pengaturan::where('key', 'master_db_api_key')->value('value');

        if (empty($apiUrl)) {
            return back()->with('sync_error', 'Sinkronisasi gagal: URL API belum dikonfigurasi.');
        }

        if (empty($apiKey)) {
            return back()->with('sync_error', 'Sinkronisasi gagal: API Key belum dikonfigurasi.');
        }

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(0);
            }

            SyncMasterDataJob::dispatch();

            Log::info('Sinkronisasi manual telah dijadwalkan oleh admin.');
            return back()->with('sync_success', 'Sinkronisasi data master telah dijadwalkan dan akan diproses di latar belakang.');

        } catch (\Exception $e) {
            Log::error('Sinkronisasi manual gagal dijadwalkan.', ['error' => $e->getMessage()]);
            return back()->with('sync_error', 'Sinkronisasi gagal. Silakan periksa log sistem untuk detail lebih lanjut.');
        }
    }
}

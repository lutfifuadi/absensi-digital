<?php

namespace App\Jobs;

use App\Models\GoogleSheetSetting;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GoogleSheetsSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $backoff = 30;
    public $timeout = 300;

    protected int $settingId;

    public function __construct(int $settingId)
    {
        $this->settingId = $settingId;
        $this->onQueue('syncs');
    }

    public function handle(): void
    {
        $setting = GoogleSheetSetting::find($this->settingId);

        if (!$setting) {
            Log::channel('daily')->error('GoogleSheetsSyncJob: Setting tidak ditemukan', [
                'setting_id' => $this->settingId,
            ]);
            return;
        }

        $setting->update([
            'last_sync_status' => 'in_progress',
            'last_sync_message' => 'Sinkronisasi sedang berlangsung...',
        ]);

        try {
            $service = new GoogleSheetsService();
            $result = $service->syncSiswa([
                'spreadsheet_id' => $setting->spreadsheet_id,
                'sheet_range' => $setting->sheet_range,
                'credentials_json' => $setting->credentials_json,
                'column_mapping' => $setting->column_mapping ?? [],
            ]);

            $setting->update([
                'last_sync_at' => now(),
                'last_sync_status' => $result['success'] ? 'success' : 'failed',
                'last_sync_message' => $result['success']
                    ? 'Sinkronisasi berhasil. Imported: ' . $result['imported'] . ', Gagal: ' . $result['failed']
                    : 'Sinkronisasi gagal: ' . implode('; ', $result['errors']),
            ]);

            Log::channel('daily')->info('GoogleSheetsSyncJob: Selesai', [
                'setting_id' => $this->settingId,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            $setting->update([
                'last_sync_at' => now(),
                'last_sync_status' => 'failed',
                'last_sync_message' => 'Error: ' . $e->getMessage(),
            ]);

            Log::channel('daily')->error('GoogleSheetsSyncJob: Error', [
                'setting_id' => $this->settingId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

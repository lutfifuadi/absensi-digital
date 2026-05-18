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

    public $tries = 3;

    public $backoff = [30, 60, 120];

    public $timeout = 120;

    protected int $settingId;

    protected int $offset;

    public function __construct(int $settingId, int $offset = 0)
    {
        $this->settingId = $settingId;
        $this->offset = $offset;
        $this->onQueue('syncs');
    }

    public function handle(): void
    {
        $setting = GoogleSheetSetting::find($this->settingId);

        if (! $setting) {
            Log::channel('daily')->error('GoogleSheetsSyncJob: Setting tidak ditemukan', [
                'setting_id' => $this->settingId,
            ]);

            return;
        }

        if ($this->offset === 0) {
            $setting->update([
                'last_sync_status' => 'in_progress',
                'last_sync_message' => 'Sinkronisasi sedang berlangsung...',
            ]);
        }

        try {
            $service = new GoogleSheetsService;
            $chunkSize = GoogleSheetSetting::CHUNK_SIZE;

            $result = $service->syncSiswa(
                [
                    'spreadsheet_id' => $setting->spreadsheet_id,
                    'sheet_range' => $setting->sheet_range,
                    'credentials_json' => $setting->credentials_json,
                    'column_mapping' => $setting->column_mapping ?? [],
                ],
                $this->settingId,
                $this->offset,
                $chunkSize
            );

            if ($result['more']) {
                GoogleSheetsSyncJob::dispatch($this->settingId, $result['offset'])
                    ->onQueue('syncs')
                    ->delay(now()->addSeconds(2));
            }

            Log::channel('daily')->info('GoogleSheetsSyncJob: Chunk selesai', [
                'setting_id' => $this->settingId,
                'offset' => $this->offset,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            $setting->update([
                'last_sync_message' => 'Error pada offset '.$this->offset.': '.$e->getMessage(),
            ]);

            Log::channel('daily')->error('GoogleSheetsSyncJob: Error', [
                'setting_id' => $this->settingId,
                'offset' => $this->offset,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

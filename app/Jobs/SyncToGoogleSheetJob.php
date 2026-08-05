<?php

namespace App\Jobs;

use App\Models\GoogleSheetSetting;
use App\Services\GoogleSheetsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncToGoogleSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nama queue untuk job ini.
     */
    public $queue = 'google_sheets';

    public Model $model;
    public string $action;
    public string $type;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $model, string $action = 'updated', ?string $type = null)
    {
        $this->model = $model;
        $this->action = $action;

        if ($type) {
            $this->type = $type;
        } else {
            $className = class_basename($model);
            $this->type = match ($className) {
                'Guru' => 'guru',
                'AbsensiSiswa' => 'absensi_siswa',
                default => 'siswa',
            };
        }
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleSheetsService $sheetsService): void
    {
        $settings = GoogleSheetSetting::where('type', $this->type)
            ->where('is_active', true)
            ->where('auto_sync_on_change', true)
            ->get();

        if ($settings->isEmpty()) {
            return;
        }

        foreach ($settings as $setting) {
            try {
                $sheetsService->syncModelToSheet($setting, $this->model, $this->action);
            } catch (\Exception $e) {
                Log::error('SyncToGoogleSheetJob error: '.$e->getMessage(), [
                    'setting_id' => $setting->id,
                    'type' => $this->type,
                    'action' => $this->action,
                ]);
            }
        }
    }
}

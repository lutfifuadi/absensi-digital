<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\User;
use App\Helpers\WhatsAppHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WaRoutineService
{
    /**
     * Run background routine check without blocking web response
     */
    public static function runRoutineCheck(): void
    {
        // Use lock to prevent race conditions across concurrent requests
        $lock = Cache::lock('wa_routine_check_lock', 30);
        if (!$lock->get()) {
            return;
        }

        try {
            // Frequency control: Only run once every 3 hours
            $lastRun = Cache::get('last_wa_routine_check');
            if ($lastRun && (time() - $lastRun < 10800)) { // 3 hours
                return;
            }

            Cache::put('last_wa_routine_check', time(), 86400);

            $waService = new WhatsAppService();

            // Find un-cached numbers for Siswa (limit to 10 per routine run)
            $siswaList = Siswa::select('id', 'no_hp', 'no_hp_ortu')
                ->where(function ($query) {
                    $query->whereNotNull('no_hp')->orWhereNotNull('no_hp_ortu');
                })
                ->limit(30)
                ->get();

            $checkedCount = 0;
            foreach ($siswaList as $siswa) {
                if ($checkedCount >= 10) break;

                $rawPhone = $siswa->no_hp ?: $siswa->no_hp_ortu;
                $formatted = WhatsAppHelper::formatNumber($rawPhone);
                if (empty($formatted)) continue;

                $cacheKey = 'wa_valid_' . $formatted;
                if (!Cache::has($cacheKey)) {
                    $waService->isNumberValidCached($formatted);
                    $checkedCount++;
                    usleep(150000); // Micro-pause 150ms to keep CPU & gateway light
                }
            }

            // Find un-cached numbers for Guru (limit to 5 per routine run)
            if ($checkedCount < 15) {
                $guruList = Guru::select('id', 'no_hp')
                    ->whereNotNull('no_hp')
                    ->limit(20)
                    ->get();

                foreach ($guruList as $guru) {
                    if ($checkedCount >= 15) break;

                    $formatted = WhatsAppHelper::formatNumber($guru->no_hp);
                    if (empty($formatted)) continue;

                    $cacheKey = 'wa_valid_' . $formatted;
                    if (!Cache::has($cacheKey)) {
                        $waService->isNumberValidCached($formatted);
                        $checkedCount++;
                        usleep(150000);
                    }
                }
            }

            Log::info("WaRoutineService: Checked {$checkedCount} un-cached numbers in background.");

        } catch (\Throwable $e) {
            Log::error('WaRoutineService Error: ' . $e->getMessage());
        } finally {
            $lock->release();
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Pengaturan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PengaturanApiController extends Controller
{
    /**
     * Daftar key toggle yang diizinkan.
     */
    private const ALLOWED_TOGGLE_KEYS = [
        'auto_alpha_siswa_enabled',
        'auto_alpha_wa_notif',
    ];

    /**
     * POST /api/v1/pengaturan/toggle
     *
     * Toggle pengaturan Ya/Tidak via AJAX.
     *
     * Body JSON:
     *   - key: string (auto_alpha_siswa_enabled | auto_alpha_wa_notif)
     *   - value: string (Ya | Tidak)
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key'   => ['required', 'string', 'in:' . implode(',', self::ALLOWED_TOGGLE_KEYS)],
            'value' => ['required', 'string', 'in:Ya,Tidak'],
        ], [
            'key.required'   => 'Key wajib diisi.',
            'key.in'         => 'Key tidak valid.',
            'value.required' => 'Value wajib diisi.',
            'value.in'       => 'Value harus berupa "Ya" atau "Tidak".',
        ]);

        $key   = $validated['key'];
        $value = $validated['value'];

        try {
            DB::transaction(function () use ($key, $value) {
                // Ambil data lama untuk activity log
                $oldRow = Pengaturan::where('key', $key)->first();
                $oldValue = $oldRow?->value ?? null;

                // Update atau buat baru
                Pengaturan::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'group' => 'auto_alpha',
                    ]
                );

                // ── Log perubahan ke activity_log ───────────────────────
                $label = match ($key) {
                    'auto_alpha_siswa_enabled' => 'Auto-Alpha Siswa',
                    'auto_alpha_wa_notif'      => 'Notifikasi WA Auto-Alpha',
                    default                    => $key,
                };

                ActivityLog::record(
                    action: 'toggle',
                    module: 'pengaturan',
                    description: "Mengubah {$label} dari \"{$oldValue}\" menjadi \"{$value}\"",
                    oldData: ['key' => $key, 'value' => $oldValue],
                    newData: ['key' => $key, 'value' => $value],
                );

                // ── Bersihkan cache terkait ──────────────────────────────
                Cache::forget('absensi_settings');
                Cache::forget('pengaturan');
            });

            return response()->json([
                'success' => true,
                'message' => "Pengaturan \"{$key}\" berhasil diubah menjadi \"{$value}\".",
                'data'    => [
                    'key'   => $key,
                    'value' => $value,
                ],
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal toggle pengaturan {$key}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Silakan coba lagi atau hubungi administrator.',
            ], 500);
        }
    }

    /**
     * GET /api/v1/pengaturan/status
     *
     * Ambil status semua toggle auto-alpha.
     */
    public function status(): JsonResponse
    {
        $statuses = [];
        foreach (self::ALLOWED_TOGGLE_KEYS as $key) {
            $statuses[$key] = Pengaturan::where('key', $key)->value('value') ?? 'Ya';
        }

        return response()->json([
            'success' => true,
            'data'    => $statuses,
        ]);
    }
}

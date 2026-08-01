<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SettingsManager;
use App\Support\PengaturanDefaults;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengaturanApiController extends Controller
{
    protected SettingsManager $settings;

    public function __construct(SettingsManager $settings)
    {
        $this->settings = $settings;
    }

    /**
     * POST /api/v1/pengaturan/toggle
     *
     * Toggle pengaturan fitur secara otomatis tanpa whitelist manual.
     *
     * Body JSON:
     *   - key: string (key yang bertipe is_toggle = true)
     *   - value: mixed (true|false, 1|0, "1"|"0", "Ya"|"Tidak")
     */
    public function toggle(Request $request): JsonResponse
    {
        $key = (string) $request->input('key');
        $rawVal = $request->input('value');

        if (!$key || !PengaturanDefaults::isToggle($key)) {
            return response()->json([
                'success' => false,
                'message' => "Pengaturan key \"{$key}\" tidak valid atau bukan merupakan feature toggle.",
            ], 422);
        }

        $meta = PengaturanDefaults::get($key);

        // Permission check
        if (($meta['permission'] ?? 'admin_sekolah') === 'super_admin') {
            $user = $request->user();
            if ($user && method_exists($user, 'hasRole') && !$user->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses super_admin untuk mengubah pengaturan ini.',
                ], 403);
            }
        }

        // Parse boolean input
        $boolValue = false;
        if (is_bool($rawVal)) {
            $boolValue = $rawVal;
        } else {
            $strVal = strtolower(trim((string) $rawVal));
            $boolValue = in_array($strVal, ['1', 'true', 'ya', 'yes', 'on'], true);
        }

        try {
            $this->settings->setBool($key, $boolValue);

            $label = $meta['label'] ?? $key;
            $statusText = $boolValue ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'success' => true,
                'message' => "Fitur \"{$label}\" berhasil {$statusText}.",
                'data'    => [
                    'key'   => $key,
                    'value' => $boolValue,
                    'label' => $label,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("PengaturanApiController: Gagal toggle {$key}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server saat memperbarui pengaturan.',
            ], 500);
        }
    }

    /**
     * GET /api/v1/pengaturan/status
     *
     * Ambil status seluruh feature toggle.
     */
    public function status(): JsonResponse
    {
        $toggles = PengaturanDefaults::toggleFeatures();
        $statuses = [];

        foreach ($toggles as $key => $meta) {
            $statuses[$key] = [
                'key'         => $key,
                'label'       => $meta['label'],
                'description' => $meta['description'],
                'is_on'       => $this->settings->getBool($key),
                'permission'  => $meta['permission'] ?? 'admin_sekolah',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $statuses,
        ]);
    }
}

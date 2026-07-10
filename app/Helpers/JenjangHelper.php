<?php

namespace App\Helpers;

use App\Models\Pengaturan;

class JenjangHelper
{
    /**
     * Get active jenjang from settings.
     */
    public static function getActiveJenjang(): string
    {
        // Default is SMA/MA/SMK
        $setting = Pengaturan::where('key', 'jenjang')->first();
        return $setting ? $setting->value : 'SMA/MA/SMK';
    }

    /**
     * Get list of all available jenjang options.
     */
    public static function getJenjangOptions(): array
    {
        return [
            'SD/MI' => 'SD/MI',
            'SMP/MTs' => 'SMP/MTs',
            'SMA/MA/SMK' => 'SMA/MA/SMK',
            'PKBM' => 'PKBM',
            'Lainnya' => 'Lainnya',
        ];
    }

    /**
     * Get mapping of tingkat based on jenjang.
     */
    public static function getTingkatOptions(?string $jenjang = null): array
    {
        if (empty($jenjang)) {
            $jenjang = self::getActiveJenjang();
        }

        switch ($jenjang) {
            case 'SD/MI':
                return ['I', 'II', 'III', 'IV', 'V', 'VI'];
            case 'SMP/MTs':
                return ['VII', 'VIII', 'IX'];
            case 'SMA/MA/SMK':
                return ['X', 'XI', 'XII'];
            case 'PKBM':
                return ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
            case 'Lainnya':
            default:
                return ['X', 'XI', 'XII'];
        }
    }

    /**
     * Get last tingkat (kelas akhir) based on jenjang.
     */
    public static function getKelasAkhir(?string $jenjang = null): string
    {
        if (empty($jenjang)) {
            $jenjang = self::getActiveJenjang();
        }

        switch ($jenjang) {
            case 'SD/MI':
                return 'VI';
            case 'SMP/MTs':
                return 'IX';
            case 'SMA/MA/SMK':
            case 'PKBM':
            case 'Lainnya':
            default:
                return 'XII';
        }
    }

    /**
     * Check if a given tingkat is the last class.
     */
    public static function isKelasAkhir(string $tingkat, ?string $jenjang = null): bool
    {
        return strtoupper($tingkat) === self::getKelasAkhir($jenjang);
    }
}

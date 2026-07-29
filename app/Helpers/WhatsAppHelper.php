<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Format dan normalisasi nomor WhatsApp / HP.
     *
     * Aturan:
     * - Bersihkan semua karakter non-digit (spasi, +, -, titik, dll).
     * - Jika diawali '08...', konversi '0' menjadi '62' -> '628...'
     * - Jika diawali '8...' (tanpa 0 dan tanpa 62), tambahkan '62' -> '628...'
     * - Jika diawali '0' (lokal non-8), ubah '0' menjadi '62'
     * - Jika nomor luar negeri (misal '60...', '65...', '1...'), biarkan tetap bersih.
     *
     * @param  string|null $number
     * @return string
     */
    public static function formatNumber(?string $number): string
    {
        if (empty($number)) {
            return '';
        }

        // 1. Bersihkan dari semua karakter non-digit
        $clean = preg_replace('/\D/', '', trim($number));

        if (empty($clean)) {
            return '';
        }

        // 2. Jika diawali '08...', ubah '0' di depan menjadi '62' -> '628...'
        if (str_starts_with($clean, '08')) {
            return '62' . substr($clean, 1);
        }

        // 3. Jika diawali '8...' (misal 8123456789), tambahkan '62' di depan
        if (str_starts_with($clean, '8')) {
            return '62' . $clean;
        }

        // 4. Jika diawali '0' diikuti digit lain (misal 021...), ubah '0' menjadi '62'
        if (str_starts_with($clean, '0')) {
            return '62' . substr($clean, 1);
        }

        // 5. Jika format internasional lain (misal 628..., 601..., 659..., 1415...), biarkan tetap bersih
        return $clean;
    }
}

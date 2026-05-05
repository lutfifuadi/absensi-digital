<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Logger khusus untuk aktivitas Scan QR Publik (Guru Piket).
 *
 * - Menulis ke channel log 'qr_scan' dengan rotasi harian
 * - Juga menulis ke laravel.log utama dengan tag [QR-SCAN]
 */
class QrScanLogger
{
    private const CHANNEL = 'qr_scan';

    // ─────────────────────────────────────────────
    //  Public API
    // ─────────────────────────────────────────────

    /**
     * Log level INFO — aktivitas normal / berhasil.
     *
     * Contoh: login berhasil, absensi tercatat, logout.
     */
    public static function info(string $event, array $context = []): void
    {
        self::write('INFO', $event, $context);
    }

    /**
     * Log level WARNING — kondisi perlu perhatian tapi tidak error.
     *
     * Contoh: siswa sudah scan hari ini, password belum diset.
     */
    public static function warning(string $event, array $context = []): void
    {
        self::write('WARNING', $event, $context);
    }

    /**
     * Log level ERROR — kegagalan / kesalahan.
     *
     * Contoh: password salah, QR tidak dikenal, exception.
     */
    public static function error(string $event, array $context = []): void
    {
        self::write('ERROR', $event, $context);
    }

    // ─────────────────────────────────────────────
    //  Internal
    // ─────────────────────────────────────────────

    private static function write(string $level, string $event, array $context): void
    {
        $contextStr = self::formatContext($context);
        $message    = "{$event}" . ($contextStr ? " | {$contextStr}" : '');

        // 1. Tulis ke channel qr_scan dengan rotasi harian
        Log::channel(self::CHANNEL)->log(strtolower($level), $message);

        // 2. Juga catat di laravel.log utama dengan prefix [QR-SCAN]
        $laravelMsg = "[QR-SCAN] {$message}";
        match ($level) {
            'INFO'    => Log::info($laravelMsg),
            'WARNING' => Log::warning($laravelMsg),
            'ERROR'   => Log::error($laravelMsg),
            default   => Log::debug($laravelMsg),
        };
    }

    /**
     * Format array context menjadi string key=value yang mudah dibaca.
     */
    private static function formatContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        $parts = [];
        foreach ($context as $key => $value) {
            $value    = is_bool($value)   ? ($value ? 'true' : 'false') : $value;
            $value    = is_null($value)   ? 'null' : $value;
            $value    = is_array($value)  ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            $parts[]  = "{$key}=\"{$value}\"";
        }

        return implode(' ', $parts);
    }
}

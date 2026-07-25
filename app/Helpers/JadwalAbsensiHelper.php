<?php

namespace App\Helpers;

use App\Models\KelasJadwalAbsensi;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\Cache;

/**
 * Helper untuk mengelola jadwal absensi per kelas per hari.
 *
 * PRD-016: Setiap kelas bisa punya jadwal berbeda per hari.
 * Field yang NULL akan fallback ke pengaturan global.
 */
class JadwalAbsensiHelper
{
    /**
     * Daftar hari valid dalam Bahasa Indonesia.
     */
    const HARI_LIST = [
        'senin',
        'selasa',
        'rabu',
        'kamis',
        'jumat',
        'sabtu',
        'minggu',
    ];

    /**
     * Ambil jadwal absensi untuk kelas tertentu pada hari tertentu.
     *
     * Jika jadwal tidak ditemukan atau field NULL, akan fallback ke
     * pengaturan global (tabel pengaturan).
     *
     * @param  int       $kelasId  ID kelas
     * @param  string|null $hari   Nama hari (senin-minggu). Jika null, gunakan hari ini.
     * @return array     Array berisi jam_mulai_absensi, jam_masuk, jam_pulang, jam_akhir_pulang, is_libur
     */
    public static function getJadwalForKelas(int $kelasId, ?string $hari = null): array
    {
        // Default ke hari ini dalam Bahasa Indonesia
        $hari = $hari ?? strtolower(now()->locale('id')->isoFormat('dddd'));

        // Normalisasi hari (handle case Carbon locale fallback)
        $hari = self::normalizeHari($hari);

        // Ambil jadwal spesifik kelas + hari
        $jadwal = KelasJadwalAbsensi::where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->first();

        // Ambil pengaturan global sebagai fallback
        $settings = Cache::get('absensi_settings', function () {
            return Pengaturan::whereIn('key', [
                'jam_mulai_absensi',
                'jam_masuk',
                'jam_pulang',
                'jam_akhir_pulang',
            ])->pluck('value', 'key')->toArray();
        });

        return [
            'jam_mulai_absensi' => self::formatTime($jadwal?->jam_mulai_absensi) ?? $settings['jam_mulai_absensi'] ?? '06:00',
            'jam_masuk'         => self::formatTime($jadwal?->jam_masuk) ?? $settings['jam_masuk'] ?? '07:00',
            'jam_pulang'        => self::formatTime($jadwal?->jam_pulang) ?? $settings['jam_pulang'] ?? '15:00',
            'jam_akhir_pulang'  => self::formatTime($jadwal?->jam_akhir_pulang) ?? $settings['jam_akhir_pulang'] ?? '17:00',
            'is_libur'          => $jadwal?->is_libur ?? false,
        ];
    }

    /**
     * Normalisasi nama hari.
     *
     * Carbon locale 'id' bisa mengembalikan format seperti "Senin" atau "senin".
     * Kita pastikan selalu lowercase dan cocok dengan enum di database.
     *
     * @param  string $hari
     * @return string
     */
    private static function normalizeHari(string $hari): string
    {
        $hari = strtolower(trim($hari));

        // Mapping jika Carbon mengembalikan nama Inggris
        $mapping = [
            'monday'    => 'senin',
            'tuesday'   => 'selasa',
            'wednesday' => 'rabu',
            'thursday'  => 'kamis',
            'friday'    => 'jumat',
            'saturday'  => 'sabtu',
            'sunday'    => 'minggu',
        ];

        return $mapping[$hari] ?? $hari;
    }

    /**
     * Cek apakah hari tertentu adalah hari libur untuk kelas tertentu.
     *
     * @param  int    $kelasId
     * @param  string|null $hari
     * @return bool
     */
    public static function isLibur(int $kelasId, ?string $hari = null): bool
    {
        $jadwal = self::getJadwalForKelas($kelasId, $hari);
        return $jadwal['is_libur'];
    }

    /**
     * Format waktu untuk response JSON.
     * Menghandle Carbon object dari model cast datetime:H:i,
     * string H:i:s, atau string H:i.
     *
     * @param  mixed $time  Carbon object, string H:i, atau string H:i:s
     * @return string|null  String format H:i
     */
    public static function formatTime(mixed $time): ?string
    {
        if ($time === null) {
            return null;
        }

        // Handle Carbon object dari model cast datetime:H:i
        if ($time instanceof \Carbon\Carbon) {
            return $time->format('H:i');
        }

        // Handle string H:i:s (8 karakter), ambil H:i saja
        if (is_string($time) && strlen($time) === 8) {
            return substr($time, 0, 5);
        }

        return (string) $time;
    }
}

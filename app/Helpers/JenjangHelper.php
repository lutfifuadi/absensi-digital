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
        return setting('jenjang', 'SMA/MA/SMK');
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

    /**
     * Calculate estimated graduation year for a student based on:
     * - School study duration (jumlah_tahun_sekolah) from settings
     * - Student's class level (tingkat)
     * - End year of student's academic year (tanggal_selesai of semester genap or TA name)
     */
    public static function getTahunLulusSiswa($siswa): ?int
    {
        if (!$siswa) {
            return null;
        }

        $jumlahTahunSekolah = settingInt('jumlah_tahun_sekolah', 3);
        $jenjang = self::getActiveJenjang();
        $tingkatOptions = self::getTingkatOptions($jenjang);

        // Get class level
        $tingkat = $siswa->kelas ? $siswa->kelas->tingkat : null;

        // Determine current grade index (1-indexed, default 1)
        $urutanTingkat = 1;
        if ($tingkat) {
            $tingkatUpper = strtoupper(trim($tingkat));
            $pos = array_search($tingkatUpper, array_map('strtoupper', $tingkatOptions));
            if ($pos !== false) {
                $urutanTingkat = $pos + 1;
            } else {
                // Fallback numeric parsing if tingkat is numeric (e.g. 10, 11, 12 or 7, 8, 9)
                if (is_numeric($tingkatUpper)) {
                    $num = (int) $tingkatUpper;
                    if ($num >= 10 && $num <= 12) {
                        $urutanTingkat = $num - 9; // 10 -> 1, 11 -> 2, 12 -> 3
                    } elseif ($num >= 7 && $num <= 9) {
                        $urutanTingkat = $num - 6; // 7 -> 1, 8 -> 2, 9 -> 3
                    } elseif ($num >= 1 && $num <= 6) {
                        $urutanTingkat = $num;
                    }
                }
            }
        }

        // Get end year of academic year (prioritize student's class academic year or student academic year)
        $ta = $siswa->kelas ? $siswa->kelas->tahunAkademik : ($siswa->tahunAkademik ?? null);
        if (!$ta) {
            $ta = \App\Models\TahunAkademik::where('is_aktif', true)->first();
        }

        $tahunAkhir = null;
        if ($ta) {
            if (!empty($ta->nama) && preg_match('/(\d{4})[^\d]*$/', trim($ta->nama), $matches)) {
                $tahunAkhir = (int) $matches[1];
            } elseif ($ta->tanggal_selesai) {
                $tahunAkhir = (int) \Carbon\Carbon::parse($ta->tanggal_selesai)->format('Y');
                if (($ta->semester ?? 'ganjil') === 'ganjil') {
                    $tahunAkhir += 1;
                }
            }
        }

        if (!$tahunAkhir) {
            $tahunAkhir = (int) date('Y');
        }

        $sisaTahun = max(0, $jumlahTahunSekolah - $urutanTingkat);

        return $tahunAkhir + $sisaTahun;
    }
}

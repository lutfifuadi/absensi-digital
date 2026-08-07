<?php

namespace App\Helpers;

use App\Models\JadwalPiket;
use App\Models\User;
use Carbon\Carbon;

class JadwalPiketHelper
{
    /**
     * Peta konversi hari dari Carbon ke Bahasa Indonesia.
     */
    public static function getHariIndonesian(?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];

        $englishDay = $date->format('l');
        return $map[$englishDay] ?? 'Senin';
    }

    /**
     * Cek apakah user bertugas piket pada tanggal/hari tertentu.
     */
    public static function isUserScheduledToday(?User $user, ?Carbon $date = null): bool
    {
        if (!$user) {
            return false;
        }

        // 1. Bypass untuk Super Admin & Admin Sekolah
        if ($user->hasAnyRole(['super_admin', 'admin_sekolah'])) {
            return true;
        }

        // 2. Jika sistem belum memiliki data jadwal piket sama sekali (fallback pasca instalasi)
        if (JadwalPiket::count() === 0) {
            return true;
        }

        // 3. Cek apakah ada jadwal aktif untuk user pada hari tersebut
        $hari = self::getHariIndonesian($date);

        return JadwalPiket::where('user_id', $user->id)
            ->where('hari', $hari)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Dapatkan daftar hari tugas piket user.
     */
    public static function getUserScheduleDays(int $userId): array
    {
        return JadwalPiket::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('hari')
            ->toArray();
    }

    /**
     * Ambil nama resmi/lengkap pencatat piket.
     */
    public static function getPiketName(?User $user): string
    {
        if (!$user) {
            return 'Guru Piket';
        }

        if ($user->guru && !empty($user->guru->nama_lengkap)) {
            return $user->guru->nama_lengkap;
        }

        return $user->name ?? $user->username ?? 'Guru Piket';
    }
}

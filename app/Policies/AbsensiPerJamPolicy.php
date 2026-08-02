<?php

namespace App\Policies;

use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\User;
use App\Services\AbsensiPerJamService;

/**
 * AbsensiPerJamPolicy — otorisasi absensi siswa per jam pelajaran (PRD-006, F-3).
 *
 * Daftarkan via Gate::policy(JadwalPelajaran::class, AbsensiPerJamPolicy::class)
 * di AppServiceProvider::boot() agar `Gate::authorize('isi', [$jadwal, $tanggal])`
 * di controller ter-resolve ke policy ini.
 *
 * Matriks akses (F-3):
 * - Isi: guru (jam miliknya/pengganti), piket (hari ini), admin & super_admin (semua).
 * - Lihat rekap: admin/operator/waka kurikulum/BK (semua), guru (kelas jam miliknya),
 *   wali_kelas (kelas asuhan); ortu → false di sini (portal ortu P2).
 */
class AbsensiPerJamPolicy
{
    /**
     * Bolehkah user mengisi/mengedit absensi siswa pada (jadwal, tanggal)?
     */
    public function isi(User $user, JadwalPelajaran $jadwal, string $tanggal): bool
    {
        // Admin sekolah & super admin → bebas kapan pun
        if ($user->isSuperAdmin() || $user->isRole(User::ROLE_ADMIN_SEKOLAH)) {
            return true;
        }

        // Guru → jadwal miliknya ATAU guru pengganti resmi (F-3)
        if ($user->isRole(User::ROLE_GURU)) {
            $guru = $user->guru;

            if (!$guru) {
                return false;
            }

            if ((int) $jadwal->guru_id === (int) $guru->id) {
                return true;
            }

            return app(AbsensiPerJamService::class)
                ->isGuruPengganti($guru->id, $jadwal->id, $tanggal);
        }

        // Piket → hanya tanggal hari ini (BR-09)
        if ($user->isRole(User::ROLE_PIKET)) {
            return $tanggal === now()->toDateString();
        }

        return false;
    }

    /**
     * Bolehkah user melihat rekap kelas tertentu (bisa null bila belum pilih kelas)?
     */
    public function lihatRekap(User $user, ?Kelas $kelas): bool
    {
        // Admin / operator / waka kurikulum / guru BK → semua kelas
        if ($user->isSuperAdmin()
            || $user->isRole(User::ROLE_ADMIN_SEKOLAH)
            || $user->isRole(User::ROLE_OPERATOR)
            || $user->isWakaKurikulum()
            || $user->isGuruBk()) {
            return true;
        }

        if ($kelas === null) {
            return false;
        }

        // Guru → kelas yang memiliki jam mengajar miliknya
        if ($user->isRole(User::ROLE_GURU)) {
            $guru = $user->guru;

            return $guru !== null
                && JadwalPelajaran::where('kelas_id', $kelas->id)
                    ->where('guru_id', $guru->id)
                    ->exists();
        }

        // Wali kelas → kelas asuhan (kelas.wali_kelas_id)
        if ($user->isRole(User::ROLE_WALI_KELAS)) {
            $guru = $user->guru;

            return $guru !== null && (int) $kelas->wali_kelas_id === (int) $guru->id;
        }

        // Orang tua & lainnya → false (portal ortu = F-11, P2)
        return false;
    }
}

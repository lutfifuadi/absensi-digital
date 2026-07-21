<?php

namespace App\Policies;

use App\Models\PelanggaranSiswa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PelanggaranSiswaPolicy
{
    use HandlesAuthorization;

    /**
     * Tentukan apakah user bisa melihat daftar pelanggaran.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ]);
    }

    /**
     * Tentukan apakah user bisa melihat detail pelanggaran.
     */
    public function view(User $user, PelanggaranSiswa $pelanggaranSiswa): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ]);
    }

    /**
     * Tentukan apakah user bisa membuat pelanggaran baru.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ]);
    }

    /**
     * Tentukan apakah user bisa menyimpan pelanggaran baru.
     */
    public function store(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Tentukan apakah user bisa memperbarui pelanggaran.
     */
    public function update(User $user, PelanggaranSiswa $pelanggaranSiswa): bool
    {
        // super_admin, admin_sekolah, operator bebas mengedit
        if ($user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
        ])) {
            return true;
        }

        // guru, wali_kelas, piket hanya bisa mengedit record yang dicatat sendiri dalam batas waktu 24 jam
        if ($user->hasAnyRole([
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ])) {
            $isOwnRecord = (int) $pelanggaranSiswa->dicatat_oleh === (int) $user->id;
            $isWithin24Hours = $pelanggaranSiswa->created_at->diffInHours(now()) <= 24;

            return $isOwnRecord && $isWithin24Hours;
        }

        return false;
    }

    /**
     * Tentukan apakah user bisa menghapus pelanggaran.
     */
    public function delete(User $user, PelanggaranSiswa $pelanggaranSiswa): bool
    {
        return $user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
        ]);
    }
}

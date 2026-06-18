<?php

namespace App\Policies;

use App\Models\Ekskul;
use App\Models\EkskulPembina;
use App\Models\Guru;
use App\Models\User;

class EkskulPolicy
{
    /**
     * Menentukan apakah user bisa mengakses data absensi suatu ekskul.
     *
     * Admin, super_admin, dan operator bisa mengakses semua ekskul.
     * Guru hanya bisa mengakses ekskul yang diatunya sebagai pembina.
     *
     * @param  User   $user
     * @param  Ekskul $ekskul
     * @return bool
     */
    public function absensiAccess(User $user, Ekskul $ekskul): bool
    {
        // Admin, super_admin, operator bisa akses semua ekskul
        if ($user->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
        ])) {
            return true;
        }

        // Guru hanya bisa akses ekskul yang diatunya sebagai pembina
        if ($user->isRole(User::ROLE_GURU)) {
            $guru = Guru::where('user_id', $user->id)->first();

            if (!$guru) {
                return false;
            }

            return EkskulPembina::where('ekskul_id', $ekskul->id)
                ->where('guru_id', $guru->id)
                ->exists();
        }

        return false;
    }
}

<?php

namespace App\Policies;

use App\Models\MonitoringKehadiranGuru;
use App\Models\User;
use Carbon\Carbon;

class GuruPiketPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MonitoringKehadiranGuru $monitoringKehadiranGuru): bool
    {
        // Admin sekolah dan Super admin bisa update kapanpun
        if ($user->hasRole(['admin_sekolah', 'super_admin'])) {
            return true;
        }

        // Piket hanya bisa update data hari ini yang dia catat
        if ($user->hasRole('piket')) {
            return $monitoringKehadiranGuru->tanggal === Carbon::today()->format('Y-m-d') && 
                   $monitoringKehadiranGuru->dicatat_oleh === $user->id;
        }

        return false;
    }
}

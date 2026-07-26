<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk memperbaiki user yang sudah ada tapi password_plain masih NULL.
 *
 * Jalankan sekali saja:
 *   php artisan db:seed --class=FixPasswordPlainSeeder
 *
 * Password di-generate otomatis: Sekolah{RoleLabel}{ID}2026!
 */
class FixPasswordPlainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari user yang password_plain NULL atau kosong
        $users = User::whereNull('password_plain')
            ->orWhere('password_plain', '')
            ->get();

        if ($users->isEmpty()) {
            $this->command?->info('Semua user sudah memiliki password_plain. Tidak ada yang perlu diperbaiki.');
            return;
        }

        $this->command?->info("Ditemukan {$users->count()} user dengan password_plain NULL/kosong.");
        $this->command?->newLine();

        $successCount = 0;
        $failCount = 0;

        foreach ($users as $user) {
            try {
                $roleLabel = $this->getRoleLabel($user->role);
                $plainPassword = "Sekolah{$roleLabel}{$user->id}2026!";

                DB::transaction(function () use ($user, $plainPassword) {
                    // Set pending plain password sebelum save
                    User::setPendingPlainPassword($plainPassword);

                    // Update password (hashed) — Observer akan sync password_plain
                    $user->update([
                        'password' => Hash::make($plainPassword),
                    ]);
                });

                // Log ke ActivityLog
                ActivityLog::create([
                    'user_id'     => null,
                    'action'      => 'fix_password_plain',
                    'module'      => 'User Management',
                    'description' => "Fix password_plain user \"{$user->name}\" (ID: {$user->id}) yang sebelumnya NULL.",
                    'ip_address'  => '127.0.0.1',
                    'user_agent'  => 'Artisan Command: db:seed --class=FixPasswordPlainSeeder',
                    'old_data'    => [
                        'email'          => $user->email,
                        'role'           => $user->role,
                        'password_plain' => null,
                    ],
                    'new_data'    => [
                        'email'          => $user->email,
                        'role'           => $user->role,
                        'password_plain' => $plainPassword,
                    ],
                ]);

                $this->command?->info("✅ {$user->name} (ID: {$user->id}) → {$plainPassword}");
                $successCount++;
            } catch (\Exception $e) {
                $this->command?->error("❌ {$user->name} (ID: {$user->id}): {$e->getMessage()}");
                $failCount++;
            }
        }

        // Rekap
        $this->command?->newLine();
        $this->command?->line('Rekap Fix Password Plain:');
        $this->command?->line('==========================');
        $this->command?->info("✅ Berhasil: {$successCount} user");

        if ($failCount > 0) {
            $this->command?->error("❌ Gagal: {$failCount} user");
        }

        $this->command?->info('📋 Log tersimpan di ActivityLog');
    }

    /**
     * Convert role constant ke label yang readable.
     */
    private function getRoleLabel(string $role): string
    {
        $labels = [
            User::ROLE_SUPER_ADMIN   => 'SuperAdmin',
            User::ROLE_ADMIN_SEKOLAH => 'AdminSekolah',
            User::ROLE_OPERATOR      => 'Operator',
            User::ROLE_GURU          => 'Guru',
            User::ROLE_WALI_KELAS    => 'WaliKelas',
            User::ROLE_STAFF_TU      => 'StaffTU',
            User::ROLE_SISWA         => 'Siswa',
            User::ROLE_ORANG_TUA     => 'OrangTua',
            User::ROLE_PIKET         => 'Piket',
        ];

        return $labels[$role] ?? ucfirst(str_replace('_', '', $role));
    }
}

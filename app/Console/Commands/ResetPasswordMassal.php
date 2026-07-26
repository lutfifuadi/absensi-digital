<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordMassal extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:reset-password-massal
        {--dry-run : Preview saja, tidak eksekusi update}
        {--role= : Filter berdasarkan role (contoh: siswa, guru, admin_sekolah)}
        {--prefix=Sekolah : Prefix untuk password baru}
        {--suffix=2026! : Suffix untuk password baru}
        {--skip-super-admin : Skip user dengan role super_admin}
        {--exclude-role= : Exclude role tertentu (pisah koma untuk multiple, contoh: super_admin,admin_sekolah)}';

    /**
     * The console command description.
     */
    protected $description = 'Reset password massal semua user dan isi password_plain yang NULL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $roleFilter = $this->option('role');
        $prefix = $this->option('prefix');
        $suffix = $this->option('suffix');
        $skipSuperAdmin = $this->option('skip-super-admin');
        $excludeRoles = $this->option('exclude-role');

        // Build query
        $query = User::query();

        // Filter by specific role (include)
        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        // Build exclude roles array
        $excludedRoles = [];

        // Add super_admin to exclude list if flag is set
        if ($skipSuperAdmin) {
            $excludedRoles[] = User::ROLE_SUPER_ADMIN;
        }

        // Add custom exclude roles (comma separated)
        if ($excludeRoles) {
            $customExcludes = array_map('trim', explode(',', $excludeRoles));
            $excludedRoles = array_merge($excludedRoles, $customExcludes);
        }

        // Remove duplicates and apply exclude filter
        $excludedRoles = array_unique($excludedRoles);

        if (!empty($excludedRoles)) {
            $query->whereNotIn('role', $excludedRoles);
            $this->info('Role yang di-skip: ' . implode(', ', $excludedRoles));
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('Tidak ada user ditemukan.');
            return self::SUCCESS;
        }

        // Generate password list
        $passwordList = $users->map(function (User $user) use ($prefix, $suffix) {
            $password = $this->generatePassword($user, $prefix, $suffix);

            return [
                'user'     => $user,
                'password' => $password,
            ];
        });

        // ── Dry Run Mode ──────────────────────────────────────────────
        if ($isDryRun) {
            $this->info('Preview Reset Password Massal:');
            $this->line('==============================');

            $passwordList->each(function (array $item, int $index) {
                $user = $item['user'];
                $this->line(
                    sprintf(
                        '%d. %s (ID: %d, Role: %s) → Password: %s',
                        $index + 1,
                        $user->name,
                        $user->id,
                        $user->role,
                        $item['password']
                    )
                );
            });

            $this->newLine();
            $this->info("Total: {$users->count()} user akan di-reset.");
            $this->warn('Jalankan tanpa --dry-run untuk eksekusi.');

            return self::SUCCESS;
        }

        // ── Konfirmasi ────────────────────────────────────────────────
        $this->warn("Anda akan mereset password {$users->count()} user secara massal!");
        $this->warn('Password lama TIDAK AKAN BISA digunakan lagi.');

        if (!$this->confirm('Apakah yakin ingin melanjutkan?')) {
            $this->info('Dibatalkan oleh user.');
            return self::SUCCESS;
        }

        // ── Eksekusi Reset ────────────────────────────────────────────
        $successCount = 0;
        $failCount = 0;
        $failedUsers = [];
        $resetResults = [];

        $this->newLine();
        $this->info('Memproses reset password...');
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($passwordList as $item) {
            $user = $item['user'];
            $plainPassword = $item['password'];

            try {
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
                    'user_id'     => null, // System action, bukan user login
                    'action'      => 'reset_password_massal',
                    'module'      => 'User Management',
                    'description' => "Password user \"{$user->name}\" (ID: {$user->id}) di-reset secara massal.",
                    'ip_address'  => '127.0.0.1',
                    'user_agent'  => 'Artisan Command: user:reset-password-massal',
                    'old_data'    => [
                        'email' => $user->email,
                        'role'  => $user->role,
                    ],
                    'new_data'    => [
                        'email'         => $user->email,
                        'role'          => $user->role,
                        'password_plain' => $plainPassword,
                    ],
                ]);

                $resetResults[] = [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'password' => $plainPassword,
                    'status'   => 'success',
                ];

                $successCount++;
            } catch (\Exception $e) {
                $failCount++;
                $failedUsers[] = "{$user->name} (ID: {$user->id}): {$e->getMessage()}";

                $resetResults[] = [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'role'   => $user->role,
                    'status' => 'failed',
                    'error'  => $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // ── Tampilkan Rekap ───────────────────────────────────────────
        $this->line('Reset Password Massal Selesai:');
        $this->line('==============================');

        if ($successCount > 0) {
            $this->info("✅ Berhasil: {$successCount} user");
        }

        if ($failCount > 0) {
            $this->error("❌ Gagal: {$failCount} user");

            $this->newLine();
            $this->warn('Detail gagal:');
            foreach ($failedUsers as $failed) {
                $this->error("  - {$failed}");
            }
        }

        $this->newLine();
        $this->info('📋 Log tersimpan di ActivityLog');

        // ── Export Hasil ke File (opsional) ───────────────────────────
        if ($this->confirm('Simpan hasil reset ke file CSV?')) {
            $exportPath = storage_path('app/exports/reset_password_' . date('Y-m-d_His') . '.csv');

            // Pastikan direktori ada
            $dir = dirname($exportPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = fopen($exportPath, 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Password', 'Status']);

            foreach ($resetResults as $row) {
                fputcsv($file, [
                    $row['id'],
                    $row['name'],
                    $row['email'],
                    $row['role'],
                    $row['password'] ?? '',
                    $row['status'],
                ]);
            }

            fclose($file);
            $this->info("📁 File disimpan di: {$exportPath}");
        }

        // ── Tampilkan Password Summary ────────────────────────────────
        if ($successCount > 0 && $this->confirm('Tampilkan daftar password baru di terminal?')) {
            $this->newLine();
            $this->warn('=== DAFTAR PASSWORD BARU ===');
            $this->warn('Simpan atau bagikan ke user terkait. Tampilkan sekali saja.');

            foreach ($resetResults as $row) {
                if ($row['status'] === 'success') {
                    $this->line(
                        sprintf(
                            '  %s (%s) → %s',
                            $row['name'],
                            $row['email'],
                            $row['password']
                        )
                    );
                }
            }

            $this->newLine();
            $this->warn('=============================');
        }

        return $successCount > 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Generate password berdasarkan role user.
     *
     * @param User $user
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    private function generatePassword(User $user, string $prefix, string $suffix): string
    {
        // Default password format
        $defaultPassword = "{$prefix}{$this->getRoleLabel($user->role)}{$user->id}{$suffix}";

        switch ($user->role) {
            case User::ROLE_SISWA:
                // Password = NISN (kolom nisn di tabel siswa)
                $siswa = Siswa::where('user_id', $user->id)->first();
                if ($siswa && !empty($siswa->nisn)) {
                    return $siswa->nisn;
                }
                // Fallback ke format lama jika siswa tidak ditemukan atau NISN kosong
                return $defaultPassword;

            case User::ROLE_ORANG_TUA:
                // Password = NISN anak (siswa yang terkait via ortu_user_id)
                $siswa = Siswa::where('ortu_user_id', $user->id)->first();
                if ($siswa && !empty($siswa->nisn)) {
                    return $siswa->nisn;
                }
                // Fallback ke format lama jika siswa tidak ditemukan atau NISN kosong
                return $defaultPassword;

            case User::ROLE_GURU:
                // Password = NIP + 4 digit random
                $guru = Guru::where('user_id', $user->id)->first();
                if ($guru && !empty($guru->nip)) {
                    $nip = $guru->nip;
                    $random4 = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    return $nip . $random4;
                }
                // Fallback ke format lama jika guru tidak ditemukan atau NIP kosong
                return $defaultPassword;

            default:
                // Role lain (operator, staff_tu, wali_kelas, admin_sekolah): Tetap pakai format lama
                return $defaultPassword;
        }
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

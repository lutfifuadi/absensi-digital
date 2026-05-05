<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RepairGuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Repair Guru
        $usersGuru = \App\Models\User::where('role', 'guru')->doesntHave('guru')->get();
        $countGuru = 0;
        foreach ($usersGuru as $user) {
            \App\Models\Guru::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'nip' => 'NIP-' . $user->id . '-' . rand(1000, 9999),
                'jenis_kelamin' => 'L',
                'mata_pelajaran' => '-',
                'status' => 'aktif',
                'qr_code' => \App\Support\QrCodeGenerator::generate('GURU')
            ]);
            $countGuru++;
        }

        // Repair Staff
        $usersStaff = \App\Models\User::where('role', 'staff_tu')->doesntHave('staff')->get();
        $countStaff = 0;
        foreach ($usersStaff as $user) {
            \App\Models\StaffTataUsaha::create([
                'user_id' => $user->id,
                'nama_lengkap' => $user->name,
                'nip' => 'NIP-' . $user->id . '-' . rand(1000, 9999),
                'jenis_kelamin' => 'L',
                'jabatan' => 'Staff',
                'status' => 'aktif'
            ]);
            $countStaff++;
        }

        // Note: Siswa is more complex due to kelas_id and tahun_akademik_id requirements.
        // We only repair if we can find a default.
        $usersSiswa = \App\Models\User::where('role', 'siswa')->doesntHave('siswa')->get();
        $countSiswa = 0;
        $defaultKelas = \App\Models\Kelas::first();
        $defaultTa = \App\Models\TahunAkademik::where('is_aktif', true)->first() ?? \App\Models\TahunAkademik::first();

        if ($defaultKelas && $defaultTa) {
            foreach ($usersSiswa as $user) {
                \App\Models\Siswa::create([
                    'user_id' => $user->id,
                    'nis' => 'NIS-' . $user->id . '-' . rand(1000, 9999),
                    'nisn' => 'NISN' . $user->id . '-' . rand(1000, 9999),
                    'nama_lengkap' => $user->name,
                    'jenis_kelamin' => 'L',
                    'tempat_lahir' => '-',
                    'tanggal_lahir' => '2000-01-01',
                    'kelas_id' => $defaultKelas->id,
                    'tahun_akademik_id' => $defaultTa->id,
                    'status' => 'aktif',
                    'qr_code' => \App\Support\QrCodeGenerator::generate('SISWA')
                ]);
                $countSiswa++;
            }
        }

        $this->command->info("Repaired: $countGuru Guru, $countStaff Staff, $countSiswa Siswa.");
    }
}

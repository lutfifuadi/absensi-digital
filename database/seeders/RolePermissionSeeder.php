<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleUsers = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@madrasah.test',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin_sekolah@madrasah.test',
                'role' => User::ROLE_ADMIN_SEKOLAH,
            ],
            [
                'name' => 'Guru Sample',
                'email' => 'guru@madrasah.test',
                'role' => User::ROLE_GURU,
            ],
            [
                'name' => 'Wali Kelas Sample',
                'email' => 'walikelas@madrasah.test',
                'role' => User::ROLE_WALI_KELAS,
            ],
            [
                'name' => 'Operator Sample',
                'email' => 'operator@madrasah.test',
                'role' => User::ROLE_OPERATOR,
            ],
            [
                'name' => 'Staff TU Sample',
                'email' => 'stafftu@madrasah.test',
                'role' => User::ROLE_STAFF_TU,
            ],
            [
                'name' => 'Siswa Sample',
                'email' => 'siswa@madrasah.test',
                'role' => User::ROLE_SISWA,
            ],
            [
                'name' => 'Guru Piket Sample',
                'email' => 'piket@madrasah.test',
                'role' => User::ROLE_PIKET,
            ],
        ];

        foreach ($sampleUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => $userData['role'],
                ]
            );

            if (! $user->isRole($userData['role'])) {
                $user->role = $userData['role'];
                $user->save();
            }
        }
    }
}

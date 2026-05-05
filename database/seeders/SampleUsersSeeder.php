<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $sampleUsers = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@sekolah.sch.id',
                'role' => User::ROLE_SUPER_ADMIN,
            ],
            [
                'name' => 'Admin Sekolah',
                'username' => 'adminsekolah',
                'email' => 'adminsekolah@sekolah.sch.id',
                'role' => User::ROLE_ADMIN_SEKOLAH,
            ],
            [
                'name' => 'Operator',
                'username' => 'operator',
                'email' => 'operator@sekolah.sch.id',
                'role' => User::ROLE_OPERATOR,
            ],
            [
                'name' => 'Guru Contoh',
                'username' => 'gurucontoh',
                'email' => 'gurucontoh@sekolah.sch.id',
                'role' => User::ROLE_GURU,
            ],
            [
                'name' => 'Wali Kelas Contoh',
                'username' => 'walikelascontoh',
                'email' => 'walikelascontoh@sekolah.sch.id',
                'role' => User::ROLE_WALI_KELAS,
            ],
            [
                'name' => 'Staff TU Contoh',
                'username' => 'stafftucontoh',
                'email' => 'stafftucontoh@sekolah.sch.id',
                'role' => User::ROLE_STAFF_TU,
            ],
            [
                'name' => 'Siswa Contoh',
                'username' => 'siswaperan',
                'email' => 'siswaperan@sekolah.sch.id',
                'role' => User::ROLE_SISWA,
            ],
            [
                'name' => 'Orang Tua Contoh',
                'username' => 'orangtuacontoh',
                'email' => 'orangtuacontoh@sekolah.sch.id',
                'role' => User::ROLE_ORANG_TUA,
            ],
        ];

        $usernames = array_column($sampleUsers, 'username');
        $emails = array_column($sampleUsers, 'email');

        User::whereIn('username', $usernames)
            ->orWhereIn('email', $emails)
            ->delete();

        foreach ($sampleUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'role' => $userData['role'],
            ]);
        }
    }
}

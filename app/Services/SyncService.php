<?php

namespace App\Services;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\StaffTataUsaha;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Support\QrCodeGenerator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncService
{
    /**
     * Sinkronisasi data User
     */
    private function syncUser(array $userData, string $role)
    {
        // Cari user berdasarkan email
        $user = User::where('username', $userData['username'])->first();

        if ($user) {
            // Hanya update jika role-nya sama (siswa sync ke siswa)
            // Jangan ubah role akun yang bukan role target (misal orang tua / guru)
            if ($user->role === $role) {
                if (!empty($userData['password'])) {
                    $user->password = Hash::make($userData['password']);
                }
                $user->name = $userData['name'];
                $user->email = $userData['email'];
                $user->save();
            }
            // Jika role berbeda, kembalikan user yang ada tanpa modifikasi
        } else {
            // Gunakan SAVEPOINT agar transaksi tidak rusak jika terjadi duplicate key (MariaDB)
            DB::statement('SAVEPOINT save_user_create');
            try {
                $user = User::create([
                    'name' => $userData['name'],
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password'] ?? 'password123'),
                    'role' => $role,
                ]);
                DB::statement('RELEASE SAVEPOINT save_user_create');
            } catch (\Illuminate\Database\QueryException $e) {
                // Race condition atau double email: rollback ke savepoint lalu cari ulang
                DB::statement('ROLLBACK TO SAVEPOINT save_user_create');
                $user = User::where('email', $userData['email'])->first();
                if (!$user) {
                    throw $e; // Bukan duplicate key, lempar ulang
                }
            }
        }

        return $user;
    }

    /**
     * Sinkronisasi Siswa
     */
    public function syncSiswa(array $data)
    {
        DB::beginTransaction();
        try {
            // 1. Sync User Account
            $user = $this->syncUser([
                'name' => $data['nama_lengkap'],
                'username' => $data['username'],
                'email' => $data['email'], // Email wajib ada di payload
                'password' => $data['password'] ?? null,
            ], User::ROLE_SISWA);

            // 2. Sync Kelas (jika diberikan)
            $kelasId = null;
            if (!empty($data['kelas_nama'])) {
                $kelas = Kelas::where('nama', $data['kelas_nama'])->first();
                if ($kelas) {
                    $kelasId = $kelas->id;
                }
            }

            // 3. Sync Tahun Akademik (jika diberikan)
            $tahunAkademikId = $data['tahun_akademik_id_default'] ?? null;
            if (!empty($data['tahun_akademik_nama'])) {
                $ta = TahunAkademik::where('nama', $data['tahun_akademik_nama'])->first();
                if ($ta) {
                    $tahunAkademikId = $ta->id;
                }
            }

            $qrCode = $data['qr_code'] ?? $data['nisn'] ?? QrCodeGenerator::generate('SISWA');

            // 4. Upsert Siswa berdasarkan NISN
            $siswa = Siswa::updateOrCreate(
                ['nisn' => $data['nisn']], // Kondisi pencarian
                [
                    'user_id' => $user->id,
                    'nis' => $data['nis'] ?? $data['nisn'] ?? null,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                    'tempat_lahir' => $data['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                    'alamat' => $data['alamat'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'no_hp_ortu' => $data['no_hp_ortu'] ?? null,
                    'kelas_id' => $kelasId,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'status' => $data['status'] ?? 'aktif',
                    'qr_code' => $qrCode,
                ]
            );

            DB::commit();
            return $siswa;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync siswa: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }

    /**
     * Sinkronisasi Guru
     */
    public function syncGuru(array $data)
    {
        DB::beginTransaction();
        try {
            $user = $this->syncUser([
                'name' => $data['nama_lengkap'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'] ?? null,
            ], User::ROLE_GURU);

            $guru = Guru::updateOrCreate(
                ['nip' => $data['nip']], // Kondisi pencarian
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                    'mata_pelajaran' => $data['mata_pelajaran'] ?? null,
                    'jabatan' => $data['jabatan'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'status' => $data['status'] ?? 'aktif',
                ]
            );

            DB::commit();
            return $guru;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync guru: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }

    /**
     * Sinkronisasi Staff TU
     */
    public function syncStaff(array $data)
    {
        DB::beginTransaction();
        try {
            $user = $this->syncUser([
                'name' => $data['nama_lengkap'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'] ?? null,
            ], User::ROLE_STAFF_TU);

            $staff = StaffTataUsaha::updateOrCreate(
                ['nip' => $data['nip']], // NIP bisa dipakai juga untuk Staff
                [
                    'user_id' => $user->id,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                    'jabatan' => $data['jabatan'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'status' => $data['status'] ?? 'aktif',
                ]
            );

            DB::commit();
            return $staff;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync staff: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }

    /**
     * Sinkronisasi Kelas
     */
    public function syncKelas(array $data)
    {
        try {
            $waliKelasId = null;
            if (!empty($data['wali_kelas_nip'])) {
                $guru = Guru::where('nip', $data['wali_kelas_nip'])->first();
                if ($guru) {
                    $waliKelasId = $guru->id;
                }
            }

            $tahunAkademikId = null;
            if (!empty($data['tahun_akademik_nama'])) {
                $ta = TahunAkademik::where('nama', $data['tahun_akademik_nama'])->first();
                if ($ta) {
                    $tahunAkademikId = $ta->id;
                }
            }

            $kelas = Kelas::updateOrCreate(
                ['nama' => $data['nama']], // Asumsi nama kelas unik
                [
                    'tingkat' => $data['tingkat'] ?? null,
                    'jurusan' => $data['jurusan'] ?? null,
                    'wali_kelas_id' => $waliKelasId,
                    'tahun_akademik_id' => $tahunAkademikId,
                ]
            );

            return $kelas;
        } catch (\Exception $e) {
            Log::error('Gagal sync kelas: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }

    /**
     * Sinkronisasi Tahun Akademik
     */
    public function syncTahunAkademik(array $data)
    {
        try {
            $ta = TahunAkademik::updateOrCreate(
                ['nama' => $data['nama'], 'semester' => $data['semester']],
                [
                    'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
                    'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                    'is_aktif' => $data['is_aktif'] ?? false,
                ]
            );

            return $ta;
        } catch (\Exception $e) {
            Log::error('Gagal sync tahun akademik: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }
    /**
     * Sinkronisasi Pengaturan Sekolah (Identitas Lembaga)
     */
    public function syncSchoolSettings(array $data)
    {
        try {
            $mapping = [
                'nama' => 'nama_lembaga',
                'nama_sekolah' => 'nama_lembaga',
                'nama_lembaga' => 'nama_lembaga',
                'alamat' => 'alamat_lembaga',
                'alamat_lembaga' => 'alamat_lembaga',
                'telp' => 'no_telp_lembaga',
                'no_telp' => 'no_telp_lembaga',
                'kontak' => 'kontak_lembaga',
                'email' => 'email_lembaga',
                'kepala_sekolah' => 'nama_kepala_lembaga',
                'nama_kepala' => 'nama_kepala_lembaga',
                'nip_kepala' => 'nip_kepala_lembaga',
                'website' => 'website_lembaga',
                'akreditasi' => 'status_akreditasi',
            ];

            foreach ($mapping as $sourceKey => $dbKey) {
                if (isset($data[$sourceKey])) {
                    \App\Models\Pengaturan::updateOrCreate(
                        ['key' => $dbKey],
                        ['value' => $data[$sourceKey], 'group' => 'umum']
                    );
                }
            }

            Log::info('SyncService: Pengaturan sekolah berhasil disinkronisasi dari pusat.');
            return true;
        } catch (\Exception $e) {
            Log::error('Gagal sync pengaturan sekolah: ' . $e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }
}

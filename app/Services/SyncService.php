<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Support\QrCodeGenerator;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                if (! empty($userData['password'])) {
                    $user->password = Hash::make($userData['password']);
                }
                $user->name = $userData['name'];
                $user->email = $userData['email'];
                if (array_key_exists('no_hp', $userData)) {
                    $user->no_hp = $userData['no_hp'];
                }
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
                    'no_hp' => $userData['no_hp'] ?? null,
                ]);
                DB::statement('RELEASE SAVEPOINT save_user_create');
            } catch (QueryException $e) {
                // Race condition atau double email: rollback ke savepoint lalu cari ulang
                DB::statement('ROLLBACK TO SAVEPOINT save_user_create');
                $user = User::where('email', $userData['email'])->first();
                if (! $user) {
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
            $tanggalLahir = null;
            if (! empty($data['tanggal_lahir'])) {
                $dateStr = trim($data['tanggal_lahir']);
                // Coba format d/m/Y (dari Google Sheets / Excel)
                if (! $tanggalLahir) {
                    try {
                        $tanggalLahir = Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // lanjut ke format berikutnya
                    }
                }
                // Coba format Y-m-d (ISO)
                if (! $tanggalLahir) {
                    try {
                        $tanggalLahir = Carbon::createFromFormat('Y-m-d', $dateStr)->format('Y-m-d');
                    } catch (\Exception $e) {
                        // lanjut ke format berikutnya
                    }
                }
                // Fallback: parse otomatis
                if (! $tanggalLahir) {
                    try {
                        $tanggalLahir = Carbon::parse($dateStr)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $tanggalLahir = null;
                    }
                }
            }

            $email = $data['email'] ?? '';
            if (empty($email)) {
                $domain = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
                $email = strtolower(trim($data['nisn'] ?? $data['nis'] ?? $data['username'])).'@'.$domain;
            }

            // 1. Sync User Account
            $user = $this->syncUser([
                'name' => $data['nama_lengkap'],
                'username' => $data['username'],
                'email' => $email,
                'password' => $data['password'] ?? null,
            ], User::ROLE_SISWA);

            // 1b. Sync Ortu Account
            $identifier = strtolower(trim($data['nisn'] ?? $data['nis'] ?? ''));
            $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
            $emailOrtu = 'ortu.'.$identifier.'@'.$domainEmail;
            $usernameOrtu = 'ortu.'.$identifier;
            
            $userOrtu = $this->syncUser([
                'name' => 'Wali Murid '.$data['nama_lengkap'],
                'username' => $usernameOrtu,
                'email' => $emailOrtu,
                'password' => $data['password'] ?? null,
                'no_hp' => $data['no_hp_ortu'] ?? null,
            ], User::ROLE_ORANG_TUA);

            // 2. Sync Kelas (jika diberikan)
            $kelasId = null;
            if (! empty($data['forced_kelas_id'])) {
                $kelasId = $data['forced_kelas_id'];
            } elseif (! empty($data['kelas_nama'])) {
                $kelas = Kelas::where('nama', $data['kelas_nama'])->first();
                if ($kelas) {
                    $kelasId = $kelas->id;
                }
            }

            // 3. Sync Tahun Akademik (jika diberikan)
            $tahunAkademikId = $data['forced_tahun_akademik_id'] ?? $data['tahun_akademik_id_default'] ?? null;
            if (empty($data['forced_tahun_akademik_id']) && ! empty($data['tahun_akademik_nama'])) {
                $ta = TahunAkademik::where('nama', $data['tahun_akademik_nama'])->first();
                if ($ta) {
                    $tahunAkademikId = $ta->id;
                }
            }
            // Fallback: gunakan tahun akademik aktif atau terbaru jika masih null
            if (empty($tahunAkademikId)) {
                $tahunAkademikId = TahunAkademik::where('is_aktif', true)->value('id')
                    ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->value('id');
            }

            $qrCode = $data['qr_code'] ?? $data['nisn'] ?? QrCodeGenerator::generate('SISWA');

            $nis = !empty($data['nis']) ? trim($data['nis']) : (!empty($data['nisn']) ? trim($data['nisn']) : null);
            if ($nis) {
                $exists = Siswa::where('nis', $nis)->where('nisn', '!=', $data['nisn'])->exists();
                if ($exists) {
                    $nis = $nis . '-' . $data['nisn'];
                }
            }

            // 4. Upsert Siswa berdasarkan NISN
            $siswa = Siswa::updateOrCreate(
                ['nisn' => $data['nisn']], // Kondisi pencarian
                [
                    'user_id' => $user->id,
                    'ortu_user_id' => $userOrtu->id,
                    'nis' => $nis,
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jenis_kelamin' => $data['jenis_kelamin'] ?? 'L',
                    'tempat_lahir' => $data['tempat_lahir'] ?? null,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $data['alamat'] ?? null,
                    'no_hp' => $data['no_hp'] ?? null,
                    'no_hp_ortu' => $data['no_hp_ortu'] ?? null,
                    'kelas_id' => $kelasId,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'status' => $data['status'] ?? 'aktif',
                    'qr_code' => $qrCode,
                ]
            );

            // 5. Hubungkan ke tabel pivot siswa_ortu jika belum ada
            if ($siswa->ortu_user_id) {
                $siswa->ortu()->syncWithoutDetaching([$siswa->ortu_user_id]);
            }

            DB::commit();

            return $siswa;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal sync siswa: '.$e->getMessage(), ['payload' => $data]);
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
            Log::error('Gagal sync guru: '.$e->getMessage(), ['payload' => $data]);
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
            Log::error('Gagal sync staff: '.$e->getMessage(), ['payload' => $data]);
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
            if (! empty($data['wali_kelas_nip'])) {
                $guru = Guru::where('nip', $data['wali_kelas_nip'])->first();
                if ($guru) {
                    $waliKelasId = $guru->id;
                }
            }

            $tahunAkademikId = null;
            if (! empty($data['tahun_akademik_nama'])) {
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
            Log::error('Gagal sync kelas: '.$e->getMessage(), ['payload' => $data]);
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
            Log::error('Gagal sync tahun akademik: '.$e->getMessage(), ['payload' => $data]);
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
                    Pengaturan::updateOrCreate(
                        ['key' => $dbKey],
                        ['value' => $data[$sourceKey], 'group' => 'umum']
                    );
                }
            }

            Log::info('SyncService: Pengaturan sekolah berhasil disinkronisasi dari pusat.');

            return true;
        } catch (\Exception $e) {
            Log::error('Gagal sync pengaturan sekolah: '.$e->getMessage(), ['payload' => $data]);
            throw $e;
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaBelumAbsenAjaxTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test endpoint AJAX siswa belum absen hari ini.
     */
    public function test_siswa_belum_absen_endpoint(): void
    {
        // 1. Setup Tahun Akademik, Guru (Wali Kelas), Kelas, Siswa
        $tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $userWali = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $userWali->id,
            'nip' => '123456789',
            'nama_lengkap' => 'Wali Kelas Ganteng',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        $kelasAktif = Kelas::create([
            'nama' => 'XII IPA 1',
            'tingkat' => 'XII',
            'wali_kelas_id' => $guru->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'is_aktif_absensi' => true,
        ]);

        $kelasNonAktifAbsen = Kelas::create([
            'nama' => 'XII IPA 2',
            'tingkat' => 'XII',
            'wali_kelas_id' => $guru->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'is_aktif_absensi' => false,
        ]);

        // Siswa 1: Aktif, wajib absen, belum absen hari ini
        $siswa1 = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'nis' => '10001',
            'nisn' => '10001',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'aktif',
            'kelas_id' => $kelasAktif->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'no_hp_ortu' => '08123456789',
        ]);

        // Siswa 2: Aktif, wajib absen, sudah absen hari ini
        $siswa2 = Siswa::create([
            'nama_lengkap' => 'Adit Nugroho',
            'nis' => '10002',
            'nisn' => '10002',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'aktif',
            'kelas_id' => $kelasAktif->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'no_hp_ortu' => '08987654321',
        ]);

        AbsensiSiswa::create([
            'siswa_id' => $siswa2->id,
            'kelas_id' => $kelasAktif->id,
            'tanggal' => now()->toDateString(),
            'jam_masuk' => '07:00:00',
            'status' => 'hadir',
        ]);

        // Siswa 3: Tidak aktif, belum absen
        $siswa3 = Siswa::create([
            'nama_lengkap' => 'Candra Wijaya',
            'nis' => '10003',
            'nisn' => '10003',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'non_aktif',
            'kelas_id' => $kelasAktif->id,
            'tahun_akademik_id' => $tahunAkademik->id,
        ]);

        // Siswa 4: Aktif, di kelas yang is_aktif_absensi = false, belum absen
        $siswa4 = Siswa::create([
            'nama_lengkap' => 'Deni Prasetyo',
            'nis' => '10004',
            'nisn' => '10004',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'aktif',
            'kelas_id' => $kelasNonAktifAbsen->id,
            'tahun_akademik_id' => $tahunAkademik->id,
        ]);

        // 2. Login sebagai admin
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Mock session tahun_akademik_id
        $response = $this->actingAs($admin)
            ->withSession(['tahun_akademik_id' => $tahunAkademik->id])
            ->getJson(route('admin.dashboard.siswa-belum-absen'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'nama_lengkap',
                        'kelas',
                        'wali_kelas',
                        'no_hp_ortu',
                        'wa_url',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ]
            ]);

        // Memastikan hanya siswa1 (Budi Santoso) yang masuk daftar
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Budi Santoso', $data[0]['nama_lengkap']);
        $this->assertEquals('XII IPA 1', $data[0]['kelas']);
        $this->assertEquals('Wali Kelas Ganteng', $data[0]['wali_kelas']);
        $this->assertEquals('08123456789', $data[0]['no_hp_ortu']);
        
        // Memeriksa URL WhatsApp
        $expectedWaUrl = 'https://wa.me/628123456789?text=' . rawurlencode('Halo Bapak/Ibu, menginfokan bahwa putra/putri Anda Budi Santoso belum melakukan absensi masuk sekolah hari ini. Terima kasih.');
        $this->assertEquals($expectedWaUrl, $data[0]['wa_url']);
    }
}


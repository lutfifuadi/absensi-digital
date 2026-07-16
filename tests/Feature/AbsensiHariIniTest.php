<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiHariIniTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_absensi_hari_ini(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->actingAs($user)->get('/admin/absensi-hari-ini');

        $response->assertStatus(200);
    }

    public function test_admin_can_filter_absensi_hari_ini_by_search_and_kelas(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $tahunAkademik = \App\Models\TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X IPA 1',
            'tingkat' => '10',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $tahunAkademik->id,
        ]);

        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nama_lengkap' => 'Budi Santoso',
            'nis' => '12345',
            'nisn' => '1234567890',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
        ]);

        // Test search filter
        $response = $this->actingAs($user)->get('/admin/absensi-hari-ini?search=Budi&status=belum_absen');
        $response->assertStatus(200);

        // Test kelas filter
        $response = $this->actingAs($user)->get("/admin/absensi-hari-ini?kelas_id={$kelas->id}&status=belum_absen");
        $response->assertStatus(200);

        // Test search and kelas combined filter
        $response = $this->actingAs($user)->get("/admin/absensi-hari-ini?search=Budi&kelas_id={$kelas->id}&status=belum_absen");
        $response->assertStatus(200);
    }
}

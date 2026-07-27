<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalSiswaTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_absensi_route_renders_correctly()
    {
        // 1. Arrange: Create a user with student role, and the corresponding Siswa record
        $user = User::factory()->create([
            'role' => 'siswa',
            'username' => 'siswa1',
            'password' => bcrypt('password123'),
        ]);

        $tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'tahun_akademik_id' => $tahunAkademik->id,
        ]);

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nisn' => '1234567890',
            'nis' => '12345',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'status' => 'aktif',
        ]);

        // 2. Act: Log in as student, call the /siswa/absensi route
        $response = $this->actingAs($user)
            ->get('/siswa/absensi');

        // 3. Assert: Verify success status and view structure
        $response->assertStatus(200);
        $response->assertViewIs('siswa.absensi');
        $response->assertViewHasAll(['siswa', 'absensi', 'month', 'year']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'is_aktif' => true
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum'
        ]);

        $this->userSiswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'username' => '0012345678'
        ]);

        $this->siswa = Siswa::create([
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456789',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'user_id' => $this->userSiswa->id
        ]);
    }

    public function test_siswa_can_view_portal_dashboard()
    {
        $response = $this->actingAs($this->userSiswa)->get(route('siswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Portal Siswa');
    }

    public function test_siswa_can_view_own_profile()
    {
        $response = $this->actingAs($this->userSiswa)->get(route('siswa.profile'));

        $response->assertStatus(200);
        $response->assertSee('Siswa Test');
    }

    public function test_siswa_cannot_view_other_student_profile()
    {
        $otherUser = User::factory()->create(['role' => User::ROLE_SISWA, 'username' => 'other']);
        $otherSiswa = Siswa::create([
            'nisn' => '0099999999',
            'nama_lengkap' => 'Other Student',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'user_id' => $otherUser->id
        ]);

        $response = $this->actingAs($this->userSiswa)->get(route('admin.siswa.profil', $otherSiswa));

        $response->assertStatus(403);
    }
}

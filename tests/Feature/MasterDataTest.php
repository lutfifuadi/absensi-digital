<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Admin User
        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_test'
        ]);

        // Setup Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'is_aktif' => true
        ]);

        // Setup Kelas
        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum',
            'tingkat' => 'X'
        ]);
    }

    public function test_admin_can_view_siswa_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_siswa()
    {
        $siswaData = [
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456789',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.siswa.store'), $siswaData);

        $response->assertRedirect(route('admin.siswa.index'));
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Test'
        ]);

        // Verify User accounts are created
        $this->assertDatabaseHas('users', ['username' => '0012345678', 'role' => User::ROLE_SISWA]);
        $this->assertDatabaseHas('users', ['username' => 'ortu.0012345678', 'role' => User::ROLE_ORANG_TUA]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalOrangTuaProfilAnakTest extends TestCase
{
    use RefreshDatabase;

    protected $tahunAkademik;
    protected $kelas;
    protected $userOrangTua;
    protected $siswaAnak;

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
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum'
        ]);

        $this->userOrangTua = User::create([
            'name' => 'Orang Tua Test',
            'username' => 'ortu_test',
            'email' => 'ortu@test.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
            'no_hp' => '08123456789',
            'hubungan' => 'Ayah',
            'alamat' => 'Jl. Test No. 123',
        ]);

        $userSiswa = User::create([
            'name' => 'Siswa Anak Test',
            'username' => 'siswa_anak_test',
            'email' => 'siswa@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
            'status' => 'aktif',
        ]);

        $this->siswaAnak = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Anak Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456789',
            'alamat' => 'Jl. Test No. 123',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'user_id' => $userSiswa->id,
            'ortu_user_id' => $this->userOrangTua->id
        ]);
    }

    public function test_parent_can_view_child_profile_page()
    {
        $response = $this->actingAs($this->userOrangTua)
            ->get(route('ortu.anak.profil', $this->siswaAnak->id));

        $response->assertStatus(200);
        $response->assertViewIs('portal-ortu.profil-anak');
        $response->assertSee('Siswa Anak Test');
        $response->assertSee('0012345678');
        $response->assertSee('12345');
        $response->assertSee('X-A');
    }

    public function test_parent_cannot_view_other_child_profile_page()
    {
        $otherParent = User::create([
            'name' => 'Orang Tua Lain',
            'username' => 'ortu_lain',
            'email' => 'ortulain@test.com',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($otherParent)
            ->get(route('ortu.anak.profil', $this->siswaAnak->id));

        $response->assertStatus(404);
    }
}

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

    public function test_admin_can_access_siswa_create_and_edit_pages_filtered_by_session_ta()
    {
        // 1. Create a second inactive TahunAkademik
        $inactiveTa = TahunAkademik::create([
            'nama' => '2026-2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->addYear()->startOfYear(),
            'tanggal_selesai' => now()->addYear()->endOfYear(),
            'is_aktif' => false
        ]);

        // 2. Create class for the inactive TA
        $inactiveKelas = Kelas::create([
            'nama' => 'XI-B',
            'tahun_akademik_id' => $inactiveTa->id,
            'jurusan' => 'Umum',
            'tingkat' => 'XI'
        ]);

        // 3. Create a Siswa for testing edit
        $siswa = Siswa::create([
            'nis' => '54321',
            'nisn' => '0054321000',
            'nama_lengkap' => 'Siswa Edit Test',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        // Test with session pointing to active TA
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('admin.siswa.create'));
        
        $response->assertStatus(200);
        $response->assertSee($this->kelas->nama);
        $response->assertDontSee($inactiveKelas->nama);

        // Test edit with session pointing to inactive TA
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $inactiveTa->id])
            ->get(route('admin.siswa.edit', $siswa));

        $response->assertStatus(200);
        $response->assertSee($inactiveKelas->nama);
        $response->assertDontSee($this->kelas->nama);

        // Test cetak Qr Kelas page filtering by session TA
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('admin.siswa.cetak-qr'));
        
        $response->assertStatus(200);
        $response->assertSee($this->kelas->nama);
        $response->assertDontSee($inactiveKelas->nama);
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

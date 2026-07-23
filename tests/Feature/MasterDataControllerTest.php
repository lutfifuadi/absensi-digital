<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\IzinSakit;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
    }

    /**
     * Test index page returns status 200 and correct metrics view variables.
     */
    public function test_index_page_loads_correct_metrics_for_authorized_users(): void
    {
        // Arrange
        // Create 2 active Siswa
        $siswaUser1 = User::factory()->create(['role' => 'siswa']);
        Siswa::create([
            'user_id' => $siswaUser1->id,
            'nama_lengkap' => 'Active Student A',
            'nis' => '10001',
            'nisn' => '1000000001',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
        ]);

        $siswaUser2 = User::factory()->create(['role' => 'siswa']);
        Siswa::create([
            'user_id' => $siswaUser2->id,
            'nama_lengkap' => 'Inactive Student B',
            'nis' => '10002',
            'nisn' => '1000000002',
            'jenis_kelamin' => 'P',
            'status' => 'non_aktif',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-02',
        ]);

        // Create Guru
        $guruUser = User::factory()->create(['role' => 'guru']);
        Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '199001012015011001',
            'nama_lengkap' => 'Active Teacher A',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'RPL',
            'jabatan' => 'Guru',
            'status' => 'aktif',
        ]);

        // Create Kelas
        Kelas::create([
            'nama' => 'Kelas X RPL 1',
            'tingkat' => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi' => true,
        ]);

        // Create Mapel
        Mapel::create([
            'kode_mapel' => 'IND',
            'nama_mapel' => 'Bahasa Indonesia',
            'kelompok' => 'umum',
            'status' => 1,
        ]);
        Mapel::create([
            'kode_mapel' => 'ENG',
            'nama_mapel' => 'Bahasa Inggris',
            'kelompok' => 'umum',
            'status' => 0, // inactive
        ]);

        // Create IzinSakit
        IzinSakit::create([
            'tipe' => 'siswa',
            'reference_id' => 1,
            'tanggal_mulai' => '2026-07-23',
            'tanggal_selesai' => '2026-07-24',
            'jenis' => 'sakit',
            'keterangan' => 'Sakit demam',
            'status' => 'pending',
        ]);

        // Act
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id, 'active_role' => 'super_admin'])
            ->get(route('admin.master-data'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('totalSiswa', 1);
        $response->assertViewHas('totalGuru', 1);
        $response->assertViewHas('totalKelas', 1);
        $response->assertViewHas('totalMapel', 1);
        $response->assertViewHas('pendingIzinCount', 1);
    }

    /**
     * Test access is restricted for unauthorized roles.
     */
    public function test_index_page_denies_unauthorized_users(): void
    {
        $unauthorizedUser = User::factory()->create([
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($unauthorizedUser)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id, 'active_role' => 'siswa'])
            ->get(route('admin.master-data'));

        // Since role selector / role middleware is active in application, it should redirect or abort
        $response->assertStatus(403);
    }

    /**
     * Test global search returns expected JSON structure and filters records.
     */
    public function test_search_returns_expected_json_structure_and_records(): void
    {
        // Arrange
        $siswaUser = User::factory()->create(['role' => 'siswa']);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nama_lengkap' => 'Budi Santoso',
            'nis' => '10003',
            'nisn' => '1000000003',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-03',
        ]);

        $guruUser = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nip' => '199001012015011002',
            'nama_lengkap' => 'Budi Raharjo',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'RPL',
            'jabatan' => 'Guru',
            'status' => 'aktif',
        ]);

        $kelas = Kelas::create([
            'nama' => 'Kelas X RPL 1',
            'tingkat' => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi' => true,
        ]);

        $mapel = Mapel::create([
            'kode_mapel' => 'IND',
            'nama_mapel' => 'Bahasa Indonesia',
            'kelompok' => 'umum',
            'status' => 1,
        ]);

        // Act - search with term 'Budi'
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id, 'active_role' => 'super_admin'])
            ->getJson(route('admin.master-data.search', ['q' => 'Budi']));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'siswa',
                'guru',
                'kelas',
                'mapel',
            ]);

        $data = $response->json();
        $this->assertCount(1, $data['siswa']);
        $this->assertEquals('Budi Santoso', $data['siswa'][0]['nama_lengkap']);

        $this->assertCount(1, $data['guru']);
        $this->assertEquals('Budi Raharjo', $data['guru'][0]['nama_lengkap']);

        // Since 'Budi' is not in Kelas name or Mapel name, they should be empty
        $this->assertCount(0, $data['kelas']);
        $this->assertCount(0, $data['mapel']);
    }

    /**
     * Test search with empty query returns empty lists.
     */
    public function test_search_with_empty_query_returns_empty_results(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id, 'active_role' => 'super_admin'])
            ->getJson(route('admin.master-data.search', ['q' => '']));

        $response->assertStatus(200)
            ->assertJson([
                'siswa' => [],
                'guru' => [],
                'kelas' => [],
                'mapel' => [],
            ]);
    }
}

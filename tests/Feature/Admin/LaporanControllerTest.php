<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin
        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_test'
        ]);

        // Setup necessary configuration/settings
        Pengaturan::updateOrCreate(
            ['key' => 'website_lembaga'],
            ['value' => 'madrasah.sch.id', 'group' => 'umum']
        );

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => '10',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);
    }

    /**
     * Test verification for requirements:
     * 1. Pagination runs successfully with 10 items per page.
     * 2. Filters (kelas_id, bulan, tahun) are retained in pagination links.
     * 3. The page loads successfully without crash.
     */
    public function test_laporan_absensi_siswa_pagination_and_filters()
    {
        // 1. Create 15 siswa in this class manually (since there is no Siswa factory)
        for ($i = 1; $i <= 15; $i++) {
            $user = User::factory()->create([
                'name' => "Siswa Test $i",
                'username' => "siswa_test_" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'role' => User::ROLE_SISWA,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nis' => "100" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'nisn' => "000100" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'nama_lengkap' => "Siswa Test $i",
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2010-01-01',
                'no_hp_ortu' => '08123456780',
                'kelas_id' => $this->kelas->id,
                'tahun_akademik_id' => $this->tahunAkademik->id,
                'status' => 'aktif',
            ]);
        }

        // 2. Perform GET request with filters to LaporanController@index
        $params = [
            'kelas_id' => $this->kelas->id,
            'bulan' => 7,
            'tahun' => 2025,
            'page' => 1
        ];

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', $params));

        // 3. Assert status is successful (loads without crash - Requirement 3)
        $response->assertStatus(200);

        // 4. Assert pagination data contains 10 items on page 1 (Requirement 1)
        $siswaList = $response->viewData('siswaList');
        $this->assertNotNull($siswaList);
        $this->assertEquals(10, $siswaList->count());
        $this->assertEquals(15, $siswaList->total());
        $this->assertEquals(1, $siswaList->currentPage());
        $this->assertEquals(2, $siswaList->lastPage());

        // 5. Assert filter parameters are retained in pagination links (Requirement 2)
        // Next page link should contain &kelas_id=..., &bulan=7, &tahun=2025
        $nextPageUrl = $siswaList->nextPageUrl();
        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('kelas_id=' . $this->kelas->id, $nextPageUrl);
        $this->assertStringContainsString('bulan=7', $nextPageUrl);
        $this->assertStringContainsString('tahun=2025', $nextPageUrl);
    }

    /**
     * Test verification that:
     * 1. Kelas options displayed in dropdown of LaporanController only come from active Academic Year (is_aktif = 1).
     * 2. Kelas options from inactive Academic Years do not appear in the options.
     */
    public function test_kelas_options_only_from_active_academic_year()
    {
        // Create an inactive academic year
        $inactiveTahunAkademik = TahunAkademik::create([
            'nama' => '2024/2025',
            'semester' => 'genap',
            'tanggal_mulai' => '2025-01-01',
            'tanggal_selesai' => '2025-06-30',
            'is_aktif' => false,
        ]);

        // Create a class in the inactive academic year
        $inactiveKelas = Kelas::create([
            'nama' => 'IX-B',
            'tingkat' => '9',
            'jurusan' => 'IPS',
            'tahun_akademik_id' => $inactiveTahunAkademik->id,
        ]);

        // Send request to index and check the options in monthly report
        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index'));
        $response->assertStatus(200);

        $kelasOptions = $response->viewData('kelasOptions');
        $this->assertNotNull($kelasOptions);

        // Verify that active class is present
        $this->assertTrue($kelasOptions->contains('id', $this->kelas->id));

        // Verify that inactive class is NOT present
        $this->assertFalse($kelasOptions->contains('id', $inactiveKelas->id));

        // Send request to rekapHarian and check the options in daily report
        $responseHarian = $this->actingAs($this->admin)->get(route('admin.rekap-harian'));
        $responseHarian->assertStatus(200);

        $kelasOptionsHarian = $responseHarian->viewData('kelasOptions');
        $this->assertNotNull($kelasOptionsHarian);

        // Verify that active class is present
        $this->assertTrue($kelasOptionsHarian->contains('id', $this->kelas->id));

        // Verify that inactive class is NOT present
        $this->assertFalse($kelasOptionsHarian->contains('id', $inactiveKelas->id));
    }

    /**
     * Test verification that:
     * 1. The month dropdown contains Indonesian month translation (e.g. Juli).
     * 2. The report table header contains Indonesian month and year format (e.g. Juli 2025).
     */
    public function test_laporan_absensi_siswa_indonesian_translations()
    {
        // 1. Create a student to ensure table is rendered
        $user = User::factory()->create([
            'name' => 'Siswa Ind',
            'username' => 'siswa_ind',
            'role' => User::ROLE_SISWA,
        ]);

        Siswa::create([
            'user_id' => $user->id,
            'nis' => '100999',
            'nisn' => '000100999',
            'nama_lengkap' => 'Siswa Ind',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $params = [
            'kelas_id' => $this->kelas->id,
            'bulan' => 7, // July
            'tahun' => 2025,
        ];

        $response = $this->actingAs($this->admin)->get(route('admin.laporan.index', $params));
        $response->assertStatus(200);

        // Assert dropdown contains Indonesian month names, e.g., "Juli" instead of "July"
        $response->assertSee('Juli');
        
        // Assert the table header title contains Indonesian format, e.g., "Juli 2025"
        $response->assertSee('Tabel Rekap —');
        $response->assertSee('Juli 2025');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaBarcodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $studentUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected TahunAkademik $ta;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard admin
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Create academic setup
        $this->ta = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->ta->id,
        ]);

        // Create student user
        $this->studentUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        // Create student record (with null NIS to test generation/verification)
        $this->siswa = Siswa::create([
            'user_id' => $this->studentUser->id,
            'nis' => null, // Left null to test generateAllBarcode
            'nisn' => '1234567890',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->ta->id,
            'status' => 'aktif',
        ]);
    }

    /** @test */
    public function admin_can_verify_and_generate_barcode_for_all_siswa()
    {
        // Assert initial NIS is null
        $this->assertNull($this->siswa->nis);

        // Run the generate all barcode action
        $response = $this->actingAs($this->admin)
            ->post(route('admin.siswa.generate-all-barcode'));

        $response->assertStatus(302);
        
        // Refresh the student record
        $this->siswa->refresh();

        // Assert NIS has been filled with the fallback NISN
        $this->assertEquals('1234567890', $this->siswa->nis);
    }

    /** @test */
    public function admin_can_download_bulk_barcodes_per_kelas()
    {
        // First ensure student has a valid NIS
        $this->siswa->update(['nis' => '12345']);

        // Request the bulk download
        $response = $this->actingAs($this->admin)
            ->get(route('admin.siswa.download-barcode-kelas', [
                'kelas_id' => $this->kelas->id
            ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
        $response->assertHeader('content-disposition', 'attachment; filename="barcode-kelas-X-A.zip"');
    }

    /** @test */
    public function profile_page_renders_1d_barcode()
    {
        // Ensure student has a valid NIS
        $this->siswa->update(['nis' => '12345']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.siswa.profil', $this->siswa));

        $response->assertStatus(200);
        $response->assertSee('Dual QR & Barcode', false);
        $response->assertSee('barcode-svg-container');
    }

    /** @test */
    public function student_dashboard_renders_1d_barcode_and_contains_toggle()
    {
        // Set active role for the session
        session(['active_role' => User::ROLE_SISWA]);

        // Ensure student has a valid NIS
        $this->siswa->update(['nis' => '12345']);

        $response = $this->actingAs($this->studentUser)
            ->get(route('siswa.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Barcode Presensi 1D');
        $response->assertSee('enlarged'); // Alpine.js variable
        $response->assertSee('Perbesar & Terangkan', false);
    }
}

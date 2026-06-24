<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ScanQrSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'tahun_akademik_id' => $tahunAkademik->id,
            'jurusan' => 'Umum',
        ]);

        $user = User::factory()->create();

        $this->siswa = Siswa::create([
            'nisn' => '1234567890',
            'nis' => '72022001',
            'nama_lengkap' => 'Ahmad Rizki',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'status' => 'aktif',
            'qr_code' => 'QR_SISWA_001',
        ]);

        $this->guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '198007202010',
            'nama_lengkap' => 'Siti Nurhaliza',
            'jenis_kelamin' => 'P',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru',
            'status' => 'aktif',
            'qr_code' => 'QR_GURU_001',
        ]);

        $this->siswa2 = Siswa::create([
            'nisn' => '0987654321',
            'nis' => '72022002',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-06-15',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahunAkademik->id,
            'status' => 'aktif',
            'qr_code' => 'QR_SISWA_002',
        ]);
    }

    public function test_route_scan_qr_search_exists()
    {
        $this->assertTrue(Route::has('public.scan-qr.search'));
    }

    public function test_search_returns_json()
    {
        $response = $this->getJson('/scan-qr/search?q=720');
        $response->assertStatus(200);
        $response->assertJsonStructure(['results']);
    }

    public function test_search_by_nis()
    {
        $response = $this->getJson('/scan-qr/search?q=72022001');
        $response->assertStatus(200);
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Ahmad Rizki', $results[0]['nama']);
        $this->assertEquals('siswa', $results[0]['tipe']);
    }

    public function test_search_by_nip()
    {
        $response = $this->getJson('/scan-qr/search?q=198007202010');
        $response->assertStatus(200);
        $results = $response->json('results');
        $this->assertCount(1, $results);
        $this->assertEquals('Siti Nurhaliza', $results[0]['nama']);
        $this->assertEquals('guru', $results[0]['tipe']);
    }

    public function test_search_by_name()
    {
        $response = $this->getJson('/scan-qr/search?q=Ahmad');
        $response->assertStatus(200);
        $results = $response->json('results');
        $this->assertGreaterThanOrEqual(1, count($results));
        $this->assertEquals('Ahmad Rizki', $results[0]['nama']);
    }

    public function test_search_returns_guru_with_kelas_guru()
    {
        $response = $this->getJson('/scan-qr/search?q=Siti');
        $response->assertStatus(200);
        $results = $response->json('results');
        $guru = collect($results)->firstWhere('tipe', 'guru');
        $this->assertNotNull($guru);
        $this->assertEquals('GURU', $guru['kelas']);
    }

    public function test_search_min_2_chars()
    {
        $response = $this->getJson('/scan-qr/search?q=a');
        $response->assertStatus(422);
    }

    public function test_search_empty_result()
    {
        $response = $this->getJson('/scan-qr/search?q=ZZZZXX');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('results'));
    }

    public function test_turbo_localstorage_key()
    {
        $view = file_get_contents(base_path('resources/views/public/scan-qr-scan.blade.php'));
        $this->assertStringContainsString("'scan_turbo'", $view);
    }

    public function test_flash_overlay_exists()
    {
        $view = file_get_contents(base_path('resources/views/public/scan-qr-scan.blade.php'));
        $this->assertStringContainsString('flash-overlay', $view);
    }

    public function test_overlay_bar_exists()
    {
        $view = file_get_contents(base_path('resources/views/public/scan-qr-scan.blade.php'));
        $this->assertStringContainsString('scan-overlay-bar', $view);
    }

    public function test_manual_input_modal_exists()
    {
        $view = file_get_contents(base_path('resources/views/public/scan-qr-scan.blade.php'));
        $this->assertStringContainsString('modalManualInput', $view);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ScanQrStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_pulang'], ['value' => '15:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_pulang'], ['value' => '14:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_akhir_pulang'], ['value' => '17:00']);
        Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15']);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum',
        ]);

        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $this->guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '1234567890',
            'nama_lengkap' => 'Guru Test',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru',
            'status' => 'aktif',
            'qr_code' => 'QR_GURU_STATS_001',
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
            'qr_code' => 'QR_SISWA_STATS_001',
        ]);
    }

    public function test_route_scan_qr_stats_exists()
    {
        $this->assertTrue(Route::has('public.scan-qr.stats'));
    }

    public function test_scan_qr_stats_returns_json()
    {
        $response = $this->get(route('public.scan-qr.stats'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'stats' => [
                'siswa_hadir',
                'siswa_terlambat',
                'siswa_total',
                'guru_hadir',
                'guru_terlambat',
                'guru_total',
            ],
            'recent_logs',
        ]);
    }

    public function test_scan_qr_stats_counts_are_accurate()
    {
        $this->travelTo(now()->setTime(7, 10));

        AbsensiSiswa::create([
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $this->kelas->id,
            'tanggal' => today(),
            'jam_masuk' => '07:05',
            'status' => 'hadir',
            'metode' => 'qr',
        ]);

        AbsensiGuru::create([
            'guru_id' => $this->guru->id,
            'tanggal' => today(),
            'jam_masuk' => '07:08',
            'status' => 'hadir',
            'metode' => 'qr',
        ]);

        $response = $this->get(route('public.scan-qr.stats'));

        $response->assertStatus(200);
        $response->assertJson([
            'stats' => [
                'siswa_hadir' => 1,
                'siswa_terlambat' => 0,
                'siswa_total' => 1,
                'guru_hadir' => 1,
                'guru_terlambat' => 0,
                'guru_total' => 1,
            ],
        ]);
    }

    public function test_scan_qr_stats_includes_recent_logs()
    {
        $this->travelTo(now()->setTime(7, 10));

        AbsensiSiswa::create([
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $this->kelas->id,
            'tanggal' => today(),
            'jam_masuk' => '07:05',
            'status' => 'hadir',
            'metode' => 'qr',
        ]);

        $response = $this->get(route('public.scan-qr.stats'));

        $response->assertStatus(200);
        $logs = $response->json('recent_logs');
        $this->assertCount(1, $logs);
        $this->assertEquals('Siswa Test', $logs[0]['nama']);
        $this->assertEquals('X-A', $logs[0]['kelas']);
        $this->assertEquals('07:05', $logs[0]['jam']);
        $this->assertEquals('siswa', $logs[0]['tipe']);
    }

    private function withQrAuthSession(): static
    {
        return $this->withSession(['qr_scan_authenticated' => true]);
    }

    public function test_scan_guru_success_returns_kelas_guru()
    {
        $this->travelTo(now()->setTime(7, 10));

        $response = $this->withQrAuthSession()->postJson(route('public.scan-qr.process'), [
            'qr_code' => 'QR_GURU_STATS_001',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'siswa' => [
                'nama' => 'Guru Test',
                'kelas' => 'GURU',
            ],
        ]);
    }

    public function test_scan_guru_duplicate_returns_already()
    {
        $this->travelTo(now()->setTime(7, 10));

        $this->withQrAuthSession()->postJson(route('public.scan-qr.process'), [
            'qr_code' => 'QR_GURU_STATS_001',
        ]);

        $response = $this->withQrAuthSession()->postJson(route('public.scan-qr.process'), [
            'qr_code' => 'QR_GURU_STATS_001',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('already', $data);
        $this->assertTrue($data['already']);
        $this->assertStringContainsString('sudah tercatat hadir', $data['message']);
    }

    public function test_scan_siswa_still_works()
    {
        $this->travelTo(now()->setTime(7, 10));

        $response = $this->withQrAuthSession()->postJson(route('public.scan-qr.process'), [
            'qr_code' => 'QR_SISWA_STATS_001',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'siswa' => [
                'nama' => 'Siswa Test',
                'kelas' => 'X-A',
            ],
        ]);
    }

    public function test_scan_invalid_qr_returns_error()
    {
        $response = $this->withQrAuthSession()->postJson(route('public.scan-qr.process'), [
            'qr_code' => 'QR_TIDAK_DIKENAL',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'QR code tidak dikenal. Pastikan QR code siswa atau guru valid.',
        ]);
    }

    public function test_stats_show_terlambat_data()
    {
        $this->travelTo(now()->setTime(7, 30));

        AbsensiSiswa::create([
            'siswa_id' => $this->siswa->id,
            'kelas_id' => $this->kelas->id,
            'tanggal' => today(),
            'jam_masuk' => '07:30',
            'status' => 'terlambat',
            'metode' => 'qr',
        ]);

        AbsensiGuru::create([
            'guru_id' => $this->guru->id,
            'tanggal' => today(),
            'jam_masuk' => '07:25',
            'status' => 'terlambat',
            'metode' => 'qr',
        ]);

        $response = $this->get(route('public.scan-qr.stats'));

        $response->assertStatus(200);
        $response->assertJson([
            'stats' => [
                'siswa_terlambat' => 1,
                'guru_terlambat' => 1,
            ],
        ]);
    }

    public function test_guru_log_includes_guru_badge_in_response()
    {
        $this->travelTo(now()->setTime(7, 10));

        AbsensiGuru::create([
            'guru_id' => $this->guru->id,
            'tanggal' => today(),
            'jam_masuk' => '07:08',
            'status' => 'hadir',
            'metode' => 'qr',
        ]);

        $response = $this->get(route('public.scan-qr.stats'));

        $logs = $response->json('recent_logs');
        $guruLog = collect($logs)->firstWhere('tipe', 'guru');

        $this->assertNotNull($guruLog);
        $this->assertEquals('GURU', $guruLog['kelas']);
        $this->assertEquals('Guru Test', $guruLog['nama']);
        $this->assertEquals('guru', $guruLog['tipe']);
    }
}

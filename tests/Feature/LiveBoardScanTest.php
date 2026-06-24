<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LiveBoardScanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00']);
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

        $this->guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '1234567890',
            'nama_lengkap' => 'Guru Test',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru',
            'status' => 'aktif',
            'qr_code' => 'QR_GURU_001',
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
            'qr_code' => 'QR_SISWA_001',
        ]);
    }

    public function test_route_live_board_scan_exists()
    {
        $this->assertTrue(Route::has('public.live-board.scan'));
    }

    public function test_absensi_guru_model_has_fillable()
    {
        $model = new AbsensiGuru();
        $fillable = $model->getFillable();

        $this->assertContains('guru_id', $fillable);
        $this->assertContains('tanggal', $fillable);
        $this->assertContains('jam_masuk', $fillable);
        $this->assertContains('jam_pulang', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('keterangan', $fillable);
        $this->assertContains('metode', $fillable);
        $this->assertCount(7, $fillable);
    }

    public function test_absensi_guru_model_has_casts()
    {
        $model = new AbsensiGuru();
        $casts = $model->getCasts();

        $this->assertArrayHasKey('tanggal', $casts);
        $this->assertEquals('date', $casts['tanggal']);
    }

    public function test_absensi_guru_model_has_guru_relation()
    {
        $model = new AbsensiGuru();
        $this->assertTrue(method_exists($model, 'guru'));
    }

    public function test_scan_guru_success()
    {
        $this->travelTo(now()->setTime(7, 10));

        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR_GURU_001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Absensi Guru berhasil dicatat!',
            ]);

        $this->assertDatabaseHas('absensi_guru', [
            'guru_id' => $this->guru->id,
            'status' => 'hadir',
        ]);
    }

    public function test_scan_guru_duplicate()
    {
        $this->travelTo(now()->setTime(7, 10));

        // First scan — should succeed
        $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR_GURU_001',
        ]);

        // Second scan — should return already scanned, not 500
        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR_GURU_001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'already' => true,
            ]);

        $this->assertStringContainsString('Guru', $response->json('message'));
        $this->assertStringContainsString('sudah tercatat hadir', $response->json('message'));
    }

    public function test_scan_siswa_still_works()
    {
        $this->travelTo(now()->setTime(7, 10));

        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR_SISWA_001',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Absensi berhasil dicatat!',
            ]);

        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa->id,
            'status' => 'hadir',
        ]);
    }

    public function test_scan_invalid_qr()
    {
        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR_RANDOM_UNKNOWN',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'QR code tidak dikenal.',
            ]);
    }
}

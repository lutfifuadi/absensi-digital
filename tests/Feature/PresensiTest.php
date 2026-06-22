<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PresensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Pengaturan Presensi
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00']);
        Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15']);

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
            'qr_code' => 'QR001'
        ]);
    }

    public function test_public_can_scan_qr_successfully()
    {
        // Mocking time to be 07:10 (within tolerance)
        $this->travelTo(now()->setTime(7, 10));

        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR001'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Absensi berhasil dicatat!'
            ]);

        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa->id,
            'status' => 'hadir'
        ]);
    }

    public function test_public_scan_qr_terlambat()
    {
        // Mocking time to be 07:20 (past 07:00 + 15 min tolerance)
        $this->travelTo(now()->setTime(7, 20));

        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'QR001'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'status' => 'terlambat'
            ]);

        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa->id,
            'status' => 'terlambat'
        ]);
    }

    public function test_public_scan_qr_invalid_code()
    {
        $response = $this->postJson(route('public.live-board.scan'), [
            'qr_code' => 'INVALID_QR'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => false,
                'message' => 'QR code tidak dikenal.'
            ]);
    }
}

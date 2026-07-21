<?php

namespace Tests\Feature;

use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KegiatanMultiDayLiveBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_board_page_accessible_by_admin()
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Persami',
            'jenis' => 'LAINNYA',
            'tanggal_pelaksanaan' => Carbon::today(),
            'tanggal_selesai' => Carbon::today()->addDays(2),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '12:00:00',
            'lokasi' => 'Bumi Perkemahan',
            'qr_code_kegiatan' => 'PERSAMI123',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.absensi-kegiatan.live-board', $kegiatan->id));

        $response->assertStatus(200);
        $response->assertSee('Persami');
    }

    public function test_scan_attendance_within_date_range()
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Ujian Akhir Semester',
            'jenis' => 'UJIAN',
            'tanggal_pelaksanaan' => Carbon::today()->subDay(),
            'tanggal_selesai' => Carbon::today()->addDay(),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '12:00:00',
            'qr_code_kegiatan' => 'UAS123',
        ]);

        $tahun = \App\Models\TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'is_aktif' => true,
            'tanggal_mulai' => Carbon::today()->subMonths(6),
            'tanggal_selesai' => Carbon::today()->addMonths(6),
        ]);

        $kelas = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => '10',
            'tahun_akademik_id' => $tahun->id,
        ]);

        $siswa = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'nisn' => '1234567890',
            'nis' => '123456',
            'qr_code' => 'BUDI123',
            'status' => 'aktif',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'tahun_akademik_id' => $tahun->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.absensi-kegiatan.live-board.scan', $kegiatan->id), [
                'qr_code' => 'BUDI123',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('siswa_nama', 'Budi Santoso');

        $this->assertDatabaseHas('absensi_kegiatan', [
            'kegiatan_id' => $kegiatan->id,
            'siswa_id' => $siswa->id,
            'status' => 'hadir',
        ]);
    }

    public function test_scan_attendance_outside_date_range()
    {
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Pramuka Kemah Bhakti',
            'jenis' => 'LAINNYA',
            'tanggal_pelaksanaan' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->subDays(3),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '12:00:00',
            'qr_code_kegiatan' => 'PRAMUKA123',
        ]);

        $tahun = \App\Models\TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'is_aktif' => true,
            'tanggal_mulai' => Carbon::today()->subMonths(6),
            'tanggal_selesai' => Carbon::today()->addMonths(6),
        ]);

        $kelas = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => '10',
            'tahun_akademik_id' => $tahun->id,
        ]);

        $siswa = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'nisn' => '1234567890',
            'nis' => '123456',
            'qr_code' => 'BUDI123',
            'status' => 'aktif',
            'kelas_id' => $kelas->id,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'tahun_akademik_id' => $tahun->id,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.absensi-kegiatan.live-board.scan', $kegiatan->id), [
                'qr_code' => 'BUDI123',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }
}

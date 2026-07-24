<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\AbsensiSiswa;
use Carbon\Carbon;
use App\Http\Controllers\Api\InnovationController;
use Illuminate\Http\Request;

class AbsensiSiswaGamifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_attendance_method()
    {
        $ta = TahunAkademik::create([
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
            'tanggal_mulai' => Carbon::now()->subMonths(1),
            'tanggal_selesai' => Carbon::now()->addMonths(5)
        ]);
        
        $kelas = Kelas::create([
            'nama' => 'Kelas X RPL 1',
            'tingkat' => 10,
            'tahun_akademik_id' => $ta->id
        ]);
        
        $siswa1 = Siswa::create([
            'nama_lengkap' => 'Siswa 1',
            'nis' => '10001',
            'nisn' => '1000000001',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2005-01-01',
            'agama' => 'Islam',
            'kelas_id' => $kelas->id,
            'status' => 'aktif',
            'tahun_akademik_id' => $ta->id
        ]);

        $date = Carbon::today()->toDateString();

        // Siswa 1: Hadir biasa
        $absen1 = AbsensiSiswa::create([
            'siswa_id' => $siswa1->id,
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $ta->id,
            'tanggal' => $date,
            'status' => 'Hadir',
            'jam_masuk' => '06:30', // Bukan early bird berdasar jam, tapi berdasar rank bisa jadi iya
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/innovation/analytics/analyze', [
            'kelas_id' => $kelas->id,
            'date' => $date,
        ]);
        
        $absen1 = AbsensiSiswa::where('id', $absen1->id)->first();
        
        $this->assertEquals(15, $absen1->points_earned);
        $this->assertTrue((bool)$absen1->is_early_bird);
        $response->assertStatus(200);
    }
}


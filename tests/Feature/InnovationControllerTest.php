<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiSiswa;
use App\Models\TahunAkademik;
use App\Models\StudentGamificationStat;
use Carbon\Carbon;

class InnovationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_attendance_calculates_points_and_early_bird()
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
        $siswa2 = Siswa::create([
            'nama_lengkap' => 'Siswa 2',
            'nis' => '10002',
            'nisn' => '1000000002',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2005-02-02',
            'agama' => 'Kristen',
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
            'status' => 'hadir',
            'jam_masuk' => '06:30', // Bukan early bird berdasar jam, tapi berdasar rank bisa jadi iya
        ]);

        // Siswa 2: Hadir Early Bird berdasar jam
        $absen2 = AbsensiSiswa::create([
            'siswa_id' => $siswa2->id,
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $ta->id,
            'tanggal' => $date,
            'status' => 'hadir',
            'jam_masuk' => '05:50', // Early bird (<= 06:00)
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/innovation/analytics/analyze', [
            'kelas_id' => $kelas->id,
            'date' => $date,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data']);

        $absen1->refresh();
        $absen2->refresh();

        // Absen1: poin = 10 (Hadir) + 5 (Early Bird, krn top 5 absen hari ini, dia rank 2) = 15
        $this->assertEquals(15, $absen1->points_earned);
        $this->assertTrue((bool)$absen1->is_early_bird);

        // Absen2: poin = 10 (Hadir) + 5 (Early Bird) = 15
        $this->assertEquals(15, $absen2->points_earned);
        $this->assertTrue((bool)$absen2->is_early_bird);

        // Check Streak
        $stat2 = StudentGamificationStat::where('siswa_id', $siswa2->id)->first();
        $this->assertEquals(1, $stat2->current_streak);
    }
}


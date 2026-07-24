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
use Illuminate\Support\Facades\Log;

class DatabaseAbsensiLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_log()
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
            'status' => 'hadir',
            'jam_masuk' => '06:30', // Bukan early bird berdasar jam, tapi berdasar rank bisa jadi iya
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/v1/innovation/analytics/analyze', [
            'kelas_id' => $kelas->id,
            'date' => $date,
        ]);

        $absen1->refresh();
        dump($absen1->toArray());
        
        $this->assertTrue(true);
    }
}


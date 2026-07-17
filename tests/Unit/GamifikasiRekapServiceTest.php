<?php

namespace Tests\Unit;

use App\Models\AbsensiSiswa;
use App\Models\ClassLeaderboard;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentLeaderboard;
use App\Models\TahunAkademik;
use App\Services\GamifikasiRekapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GamifikasiRekapServiceTest extends TestCase
{
    use RefreshDatabase;

    private GamifikasiRekapService $service;
    private TahunAkademik $tahunAkademik;
    private Kelas $kelasA;
    private Kelas $kelasB;
    private Siswa $siswa1;
    private Siswa $siswa2;
    private Siswa $siswa3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GamifikasiRekapService();

        // 1. Create Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025/2026 Ganjil',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        // 2. Create Kelas
        $this->kelasA = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'kustom_absensi' => false,
        ]);

        $this->kelasB = Kelas::create([
            'nama' => 'X RPL 2',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'kustom_absensi' => false,
        ]);

        // 3. Create Siswa (Siswa 1 & 2 di Kelas A, Siswa 3 di Kelas B)
        $this->siswa1 = Siswa::create([
            'nama_lengkap' => 'Ahmad Roni',
            'nis' => '10001',
            'nisn' => '0000000001',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelasA->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $this->siswa2 = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'nis' => '10002',
            'nisn' => '0000000002',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'kelas_id' => $this->kelasA->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $this->siswa3 = Siswa::create([
            'nama_lengkap' => 'Cici Paramida',
            'nis' => '10003',
            'nisn' => '0000000003',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Surabaya',
            'tanggal_lahir' => '2010-03-03',
            'kelas_id' => $this->kelasB->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);
    }

    /**
     * Test logic getRekapSiswa() when:
     * - Leaderboard is empty
     * - Dynamic calculation is active
     */
    public function test_rekap_siswa_dynamic_skor_and_rank_when_leaderboard_empty(): void
    {
        // Setup Absensi Siswa
        // Siswa 1: 3 Hadir, 1 Terlambat, 1 Alpha.
        // Skor Dinamis Siswa 1: (3 * 10) + (1 * 5) - (1 * 10) = 30 + 5 - 10 = 25
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'hadir', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-03']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Terlambat', 'tanggal' => '2025-08-04']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Alpha', 'tanggal' => '2025-08-05']);

        // Siswa 2: 1 Hadir, 2 Terlambat, 0 Alpha.
        // Skor Dinamis Siswa 2: (1 * 10) + (2 * 5) - (0 * 10) = 10 + 10 - 0 = 20
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Terlambat', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'terlambat', 'tanggal' => '2025-08-03']);

        // Siswa 3: 4 Hadir, 0 Terlambat, 0 Alpha.
        // Skor Dinamis Siswa 3: (4 * 10) + (0 * 5) - (0 * 10) = 40
        AbsensiSiswa::create(['siswa_id' => $this->siswa3->id, 'kelas_id' => $this->kelasB->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa3->id, 'kelas_id' => $this->kelasB->id, 'status' => 'Hadir', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa3->id, 'kelas_id' => $this->kelasB->id, 'status' => 'hadir', 'tanggal' => '2025-08-03']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa3->id, 'kelas_id' => $this->kelasB->id, 'status' => 'Hadir', 'tanggal' => '2025-08-04']);

        // Leaderboard empty (not created in DB)
        // Call service with 'periode' => 'semua' (no start_date & end_date)
        $rekap = $this->service->getRekapSiswa(['periode' => 'semua']);

        $this->assertCount(3, $rekap);

        // Sorting should be descending by score:
        // Rank 1: Siswa 3 (Skor 40)
        // Rank 2: Siswa 1 (Skor 25)
        // Rank 3: Siswa 2 (Skor 20)
        $first = $rekap->get(0);
        $this->assertEquals($this->siswa3->id, $first['siswa_id']);
        $this->assertEquals(40, $first['skor']);
        $this->assertEquals(1, $first['rank']);

        $second = $rekap->get(1);
        $this->assertEquals($this->siswa1->id, $second['siswa_id']);
        $this->assertEquals(25, $second['skor']);
        $this->assertEquals(2, $second['rank']);

        $third = $rekap->get(2);
        $this->assertEquals($this->siswa2->id, $third['siswa_id']);
        $this->assertEquals(20, $third['skor']);
        $this->assertEquals(3, $third['rank']);
    }

    /**
     * Test logic getRekapSiswa() when:
     * - Leaderboard is NOT empty
     * - Period is active (eg. 'bulan' filter)
     */
    public function test_rekap_siswa_dynamic_skor_and_rank_when_period_is_active(): void
    {
        // Setup Leaderboard in DB
        // Leaderboard says Siswa 2 has score 100, Siswa 1 has score 50 (Siswa 2 Rank 1, Siswa 1 Rank 2)
        StudentLeaderboard::create([
            'siswa_id' => $this->siswa2->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'rank' => 1,
            'score' => 100,
            'total_attendance' => 10,
            'total_present' => 10,
            'calculated_at' => now(),
        ]);
        StudentLeaderboard::create([
            'siswa_id' => $this->siswa1->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'rank' => 2,
            'score' => 50,
            'total_attendance' => 10,
            'total_present' => 5,
            'calculated_at' => now(),
        ]);

        // Setup Absensi Siswa
        // Siswa 1: 3 Hadir (Skor 30)
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'hadir', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-03']);

        // Siswa 2: 1 Hadir, 1 Alpha (Skor 10 - 10 = 0)
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Alpha', 'tanggal' => '2025-08-02']);

        // Call service with 'periode' => 'bulan', 'bulan' => '2025-08'
        $rekap = $this->service->getRekapSiswa([
            'periode' => 'bulan',
            'bulan' => '2025-08',
        ]);

        // Since it's dynamic (period is active), the skor should be dynamic:
        // Siswa 1 has Skor 30, Siswa 2 has Skor 0
        // Expected ranking: Rank 1 Siswa 1 (Skor 30), Rank 2 Siswa 2 (Skor 0)
        $first = $rekap->firstWhere('siswa_id', $this->siswa1->id);
        $this->assertEquals(30, $first['skor']);
        $this->assertEquals(1, $first['rank']);

        $second = $rekap->firstWhere('siswa_id', $this->siswa2->id);
        $this->assertEquals(0, $second['skor']);
        $this->assertEquals(2, $second['rank']);
    }

    /**
     * Test logic getRekapSiswa() when:
     * - Leaderboard is NOT empty
     * - Period is NOT active (eg. 'semua' filter)
     */
    public function test_rekap_siswa_uses_stored_leaderboard_when_period_not_active(): void
    {
        // Setup Leaderboard in DB
        // Leaderboard says Siswa 2 has score 100 (Rank 1), Siswa 1 has score 50 (Rank 2)
        StudentLeaderboard::create([
            'siswa_id' => $this->siswa2->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'rank' => 1,
            'score' => 100,
            'total_attendance' => 10,
            'total_present' => 10,
            'calculated_at' => now(),
        ]);
        StudentLeaderboard::create([
            'siswa_id' => $this->siswa1->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'rank' => 2,
            'score' => 50,
            'total_attendance' => 10,
            'total_present' => 5,
            'calculated_at' => now(),
        ]);

        // Setup Absensi Siswa (skor dinamis would be: Siswa 1 = 30, Siswa 2 = 0)
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-03']);

        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Alpha', 'tanggal' => '2025-08-02']);

        // Call service with 'periode' => 'semua'
        $rekap = $this->service->getRekapSiswa(['periode' => 'semua']);

        // Since period is NOT active and leaderboard is not empty, it should use the stored leaderboard:
        // Siswa 2: Skor 100 (Rank 1)
        // Siswa 1: Skor 50 (Rank 2)
        $first = $rekap->get(0);
        $this->assertEquals($this->siswa2->id, $first['siswa_id']);
        $this->assertEquals(100, $first['skor']);
        $this->assertEquals(1, $first['rank']);

        $second = $rekap->get(1);
        $this->assertEquals($this->siswa1->id, $second['siswa_id']);
        $this->assertEquals(50, $second['skor']);
        $this->assertEquals(2, $second['rank']);
    }

    /**
     * Test logic getRekapKelas() when:
     * - Leaderboard is empty
     * - Dynamic calculation is active
     */
    public function test_rekap_kelas_dynamic_rank_when_leaderboard_empty(): void
    {
        // Setup Absensi Siswa
        // Kelas A: 3 present, 4 total = 75%
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa1->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-02']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Alpha', 'tanggal' => '2025-08-01']);
        AbsensiSiswa::create(['siswa_id' => $this->siswa2->id, 'kelas_id' => $this->kelasA->id, 'status' => 'Hadir', 'tanggal' => '2025-08-02']);

        // Kelas B: 1 present, 1 total = 100%
        AbsensiSiswa::create(['siswa_id' => $this->siswa3->id, 'kelas_id' => $this->kelasB->id, 'status' => 'Hadir', 'tanggal' => '2025-08-01']);

        // Call service with 'periode' => 'semua'
        $rekap = $this->service->getRekapKelas(['periode' => 'semua']);

        $this->assertCount(2, $rekap);

        // Sorting by percentage descending:
        // Rank 1: Kelas B (100%)
        // Rank 2: Kelas A (75%)
        $first = $rekap->get(0);
        $this->assertEquals($this->kelasB->id, $first['kelas_id']);
        $this->assertEquals(100, $first['percentage']);
        $this->assertEquals(1, $first['rank']);

        $second = $rekap->get(1);
        $this->assertEquals($this->kelasA->id, $second['kelas_id']);
        $this->assertEquals(75, $second['percentage']);
        $this->assertEquals(2, $second['rank']);
    }
}

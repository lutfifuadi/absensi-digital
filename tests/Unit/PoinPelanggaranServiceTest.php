<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use App\Models\KonfigurasiPelanggaran;
use App\Models\Kelas;
use App\Services\PoinPelanggaranService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PoinPelanggaranServiceTest extends TestCase
{
    use RefreshDatabase;

    private PoinPelanggaranService $service;
    private Siswa $siswa;
    private TahunAkademik $tahunAkademik;
    private Kelas $kelas;
    private User $user;
    private JenisPelanggaran $jenisPelanggaran;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PoinPelanggaranService();

        // Buat user untuk relasi pencatat / auth
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Buat tahun akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true,
        ]);

        // Buat kelas
        $this->kelas = Kelas::create([
            'nama' => 'Kelas X-A',
            'tingkat' => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // Buat siswa
        $this->siswa = Siswa::create([
            'user_id' => $this->user->id,
            'nis' => '12345',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Ahmad Pelanggar',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        // Buat kategori pelanggaran
        $kategori = KategoriPelanggaran::create([
            'nama' => 'Kedisiplinan',
            'keterangan' => 'Kategori pelanggaran kedisiplinan',
        ]);

        // Buat jenis pelanggaran
        $this->jenisPelanggaran = JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Terlambat Masuk Sekolah',
            'bobot_poin' => 5,
            'deskripsi' => 'Terlambat lebih dari 15 menit',
        ]);
    }

    /** @test */
    public function test_calculate_accumulated_points()
    {
        // Masukkan data pelanggaran
        PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenisPelanggaran->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Terlambat 10 menit',
            'poin_saat_itu' => 5,
            'dicatat_oleh' => $this->user->id,
        ]);

        PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenisPelanggaran->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-22',
            'keterangan' => 'Terlambat 20 menit',
            'poin_saat_itu' => 5,
            'dicatat_oleh' => $this->user->id,
        ]);

        // Pelanggaran di tahun akademik lain (tidak boleh ikut terhitung)
        $taLain = TahunAkademik::create([
            'nama' => '2027/2028',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2027-07-01',
            'tanggal_selesai' => '2028-06-30',
            'is_aktif' => false,
        ]);
        PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenisPelanggaran->id,
            'tahun_akademik_id' => $taLain->id,
            'tanggal_kejadian' => '2027-07-21',
            'keterangan' => 'Terlambat 10 menit',
            'poin_saat_itu' => 10,
            'dicatat_oleh' => $this->user->id,
        ]);

        $totalPoin = $this->service->calculateAccumulatedPoints($this->siswa->id, $this->tahunAkademik->id);

        $this->assertEquals(10, $totalPoin);
    }

    /** @test */
    public function test_check_and_trigger_sp_sequential_creation()
    {
        // Set konfigurasi
        KonfigurasiPelanggaran::create([
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'batas_sp1' => 20,
            'batas_sp2' => 40,
            'batas_sp3' => 60,
            'notif_wa_aktif' => false,
            'created_by' => $this->user->id,
        ]);

        // Simulasikan poin langsung melompat ke 65 (melewati batas SP3)
        $spTerakhir = $this->service->checkAndTriggerSp($this->siswa->id, $this->tahunAkademik->id, 65);

        // Pastikan SP1, SP2, SP3 semua terbit
        $spList = PelanggaranSp::where('siswa_id', $this->siswa->id)
            ->where('tahun_akademik_id', $this->tahunAkademik->id)
            ->orderBy('level_sp')
            ->get();

        $this->assertCount(3, $spList);
        $this->assertEquals('SP1', $spList[0]->level_sp);
        $this->assertEquals('SP2', $spList[1]->level_sp);
        $this->assertEquals('SP3', $spList[2]->level_sp);

        // Instance yang dikembalikan oleh method adalah SP yang paling terakhir dibuat/terpicu
        $this->assertNotNull($spTerakhir);
        $this->assertEquals('SP3', $spTerakhir->level_sp);
        $this->assertEquals(65, $spTerakhir->total_poin_saat_sp);
    }

    /** @test */
    public function test_check_and_trigger_sp_does_not_recreate_existing_sp()
    {
        KonfigurasiPelanggaran::create([
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'batas_sp1' => 20,
            'batas_sp2' => 40,
            'batas_sp3' => 60,
        ]);

        // Buat SP1 secara manual terlebih dahulu
        PelanggaranSp::create([
            'siswa_id' => $this->siswa->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'level_sp' => 'SP1',
            'total_poin_saat_sp' => 22,
            'tanggal_sp' => '2026-07-21',
            'diterbitkan_oleh' => $this->user->id,
        ]);

        // Sekarang poin melompat ke 45 (berhak dapat SP2)
        $spTerakhir = $this->service->checkAndTriggerSp($this->siswa->id, $this->tahunAkademik->id, 45);

        $spList = PelanggaranSp::where('siswa_id', $this->siswa->id)
            ->where('tahun_akademik_id', $this->tahunAkademik->id)
            ->get();

        // SP1 tidak dibuat ulang, jadi total SP saat ini adalah 2 (SP1 lama, dan SP2 baru)
        $this->assertCount(2, $spList);
        $this->assertEquals('SP2', $spTerakhir->level_sp);
    }

    /** @test */
    public function test_recalculate_points_and_sp_keeps_existing_sp_even_if_points_decrease()
    {
        KonfigurasiPelanggaran::create([
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'batas_sp1' => 20,
            'batas_sp2' => 40,
            'batas_sp3' => 60,
        ]);

        // Masukkan data pelanggaran total poin 25
        $pelanggaran1 = PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $this->jenisPelanggaran->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-21',
            'keterangan' => 'Pelanggaran Berat',
            'poin_saat_itu' => 25,
            'dicatat_oleh' => $this->user->id,
        ]);

        // Trigger SP
        $this->service->checkAndTriggerSp($this->siswa->id, $this->tahunAkademik->id);

        $this->assertDatabaseHas('pelanggaran_sp', [
            'siswa_id' => $this->siswa->id,
            'level_sp' => 'SP1',
        ]);

        // Admin menghapus pelanggaran tersebut (soft delete)
        $pelanggaran1->delete();

        // Jalankan rekalkulasi
        $this->service->recalculatePointsAndSp($this->siswa->id, $this->tahunAkademik->id);

        // Total poin sekarang adalah 0
        $totalPoin = $this->service->calculateAccumulatedPoints($this->siswa->id, $this->tahunAkademik->id);
        $this->assertEquals(0, $totalPoin);

        // Sesuai BR-08, SP yang sudah telanjur diterbitkan TETAP berlaku (tidak dihapus)
        $this->assertDatabaseHas('pelanggaran_sp', [
            'siswa_id' => $this->siswa->id,
            'level_sp' => 'SP1',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAlfaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Kelas $kelasA;
    protected Kelas $kelasB;
    protected Siswa $siswaA;
    protected Siswa $siswaB;
    protected TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'username' => 'admin_sekolah',
            'email' => 'admin@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $this->kelasA = Kelas::create([
            'nama' => 'Kelas A',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->kelasB = Kelas::create([
            'nama' => 'Kelas B',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->siswaA = Siswa::create([
            'nis' => '10001',
            'nisn' => '10001',
            'nama_lengkap' => 'Siswa A',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'aktif',
            'kelas_id' => $this->kelasA->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->siswaB = Siswa::create([
            'nis' => '10002',
            'nisn' => '10002',
            'nama_lengkap' => 'Siswa B',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'status' => 'aktif',
            'kelas_id' => $this->kelasB->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);
    }

    public function test_dashboard_alfa_respects_custom_holiday_schedule(): void
    {
        // Setup: Senin 2026-07-27
        // Kelas A libur (is_libur = true)
        // Kelas B masuk (is_libur = false)
        KelasJadwalAbsensi::create([
            'kelas_id' => $this->kelasA->id,
            'hari' => 'senin',
            'is_libur' => true,
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id' => $this->kelasB->id,
            'hari' => 'senin',
            'is_libur' => false,
        ]);

        $this->travelTo(Carbon::create(2026, 7, 27, 12, 0, 0));

        $response = $this->actingAs($this->admin)
            ->withSession([
                'active_role' => User::ROLE_ADMIN_SEKOLAH,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->get(route('admin.dashboard.belum-absen', [
                'start_date' => '2026-07-27',
                'end_date' => '2026-07-27',
            ]));

        $response->assertStatus(200);

        // Siswa A (Kelas A) libur, sehingga tidak masuk dalam daftar belum absen
        // Siswa B (Kelas B) tidak libur, sehingga masuk daftar belum absen
        $response->assertSee('Siswa B');
        $response->assertDontSee('Siswa A');

        $this->travelBack();
    }
}

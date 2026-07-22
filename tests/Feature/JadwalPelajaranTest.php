<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JadwalPelajaranTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected TahunAkademik $tahunAkademik;
    protected Kelas $kelas;
    protected Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user admin dengan role super_admin di field 'role'
        $this->admin = User::factory()->create(['role' => 'super_admin']);

        // Buat user untuk guru agar guru.user_id tidak null
        $guruUser = User::factory()->create(['role' => 'guru']);

        // Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // Buat Kelas
        $this->kelas = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 10,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi' => true,
        ]);

        // Buat Guru
        $this->guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Budi Utomo, S.Pd',
            'nip' => '199001012020011001',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Informatika',
            'status' => 'aktif',
        ]);

        // Buat Mapel options untuk dropdown
        Mapel::create(['kode_mapel' => 'INF', 'nama_mapel' => 'Informatika', 'status' => true]);
        Mapel::create(['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'status' => true]);
    }

    public function test_admin_can_view_jadwal_pelajaran_index()
    {
        $this->actingAs($this->admin);

        // Buat Jadwal Pelajaran untuk tahun akademik aktif
        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Informatika',
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ]);

        // Buat tahun akademik tidak aktif kedua
        $tahunAkademikLain = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Genap',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'is_aktif' => false,
        ]);

        $kelasLain = Kelas::create([
            'nama' => 'XII RPL 2',
            'tingkat' => 12,
            'tahun_akademik_id' => $tahunAkademikLain->id,
            'is_aktif_absensi' => true,
        ]);

        $jadwalLain = JadwalPelajaran::create([
            'kelas_id' => $kelasLain->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Biologi',
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:30',
        ]);

        // SQLite tidak mendukung fungsi MySQL FIELD(), kita bisa mock atau sesuaikan
        // Namun karena ini pengujian feature yang menggunakan controller asli,
        // kita bisa mendefinisikan custom function FIELD untuk driver sqlite di test.
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('FIELD', function (...$args) {
                $value = array_shift($args);
                $pos = array_search($value, $args);
                return $pos !== false ? $pos + 1 : 0;
            });
        }

        $response = $this->get(route('admin.jadwal.index'));

        $response->assertStatus(200);
        
        // Verifikasi format opsi kelas memuat tahun akademik dengan benar untuk kelas aktif
        $response->assertSee('X RPL 1');
        $response->assertSee('Informatika');
        $response->assertSee('Budi Utomo, S.Pd');

        // Verifikasi kelas dan jadwal pelajaran dari tahun akademik non-aktif TIDAK muncul
        $response->assertDontSee('XII RPL 2');
        $response->assertDontSee('Biologi');
    }

    public function test_admin_can_view_jadwal_pelajaran_create_form()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.jadwal.create'));

        $response->assertStatus(200);
        
        // Verifikasi format opsi kelas memuat nama kelas
        $response->assertSee('X RPL 1');
    }

    public function test_admin_can_view_jadwal_pelajaran_edit_form()
    {
        $this->actingAs($this->admin);

        $jadwal = JadwalPelajaran::create([
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Informatika',
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:30:00',
        ]);

        $response = $this->get(route('admin.jadwal.edit', $jadwal));

        $response->assertStatus(200);
        
        // Verifikasi format opsi kelas memuat nama kelas
        $response->assertSee('X RPL 1');
    }

    public function test_admin_can_store_jadwal_pelajaran()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.jadwal.store'), [
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Matematika',
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
        ]);

        $response->assertRedirect(route('admin.jadwal.index'));
        $this->assertDatabaseHas('jadwal_pelajaran', [
            'kelas_id' => $this->kelas->id,
            'mata_pelajaran' => 'Matematika',
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:30',
        ]);
    }

    public function test_filter_tahun_akademik_from_session()
    {
        $this->actingAs($this->admin);

        // Buat tahun akademik lain yang tidak aktif secara default
        $tahunAkademikLain = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Genap',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'is_aktif' => false,
        ]);

        $kelasLain = Kelas::create([
            'nama' => 'XII RPL 2',
            'tingkat' => 12,
            'tahun_akademik_id' => $tahunAkademikLain->id,
            'is_aktif_absensi' => true,
        ]);

        // Buat Jadwal Pelajaran untuk tahun akademik aktif
        $jadwalAktif = JadwalPelajaran::create([
            'kelas_id' => $this->kelas->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Informatika Aktif',
            'hari' => 'Senin',
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:30',
        ]);

        // Buat Jadwal Pelajaran untuk tahun akademik lain
        $jadwalLain = JadwalPelajaran::create([
            'kelas_id' => $kelasLain->id,
            'guru_id' => $this->guru->id,
            'mata_pelajaran' => 'Biologi',
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:30',
        ]);

        // SQLite FIELD mock
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $pdo->sqliteCreateFunction('FIELD', function (...$args) {
                $value = array_shift($args);
                $pos = array_search($value, $args);
                return $pos !== false ? $pos + 1 : 0;
            });
        }

        // Simulasikan session tahun_akademik_id diatur ke tahun akademik lain
        $response = $this->withSession(['tahun_akademik_id' => $tahunAkademikLain->id])
            ->get(route('admin.jadwal.index'));

        $response->assertStatus(200);

        // Verifikasi kelas dan jadwal pelajaran dari tahun akademik non-aktif SEKARANG muncul
        $response->assertSee('XII RPL 2');
        $response->assertSee('Biologi');

        // Verifikasi kelas dan jadwal pelajaran dari tahun akademik aktif default SEKARANG tidak muncul
        $response->assertDontSee('X RPL 1');
        $response->assertDontSee('Informatika Aktif');

        // Verifikasi dropdown di form create
        $responseCreate = $this->withSession(['tahun_akademik_id' => $tahunAkademikLain->id])
            ->get(route('admin.jadwal.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('XII RPL 2');
        $responseCreate->assertDontSee('X RPL 1');

        // Verifikasi dropdown di form edit
        $responseEdit = $this->withSession(['tahun_akademik_id' => $tahunAkademikLain->id])
            ->get(route('admin.jadwal.edit', $jadwalLain));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('XII RPL 2');
        $responseEdit->assertDontSee('X RPL 1');
    }
}

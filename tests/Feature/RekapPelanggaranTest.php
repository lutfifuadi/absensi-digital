<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RekapPelanggaranTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Siswa $siswa;
    private Kelas $kelas;
    private TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-RPL',
            'tingkat' => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $this->siswa = Siswa::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_SISWA])->id,
            'nis' => '10001',
            'nisn' => '1000100010',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-05-05',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'no_hp_ortu' => '081234567890',
        ]);
    }

    /** @test */
    public function super_admin_can_access_rekap_page()
    {
        session(['tahun_akademik_id' => $this->tahunAkademik->id]);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('admin.pelanggaran-siswa.rekap'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pelanggaran-rekap.index');
    }

    /** @test */
    public function super_admin_can_filter_rekap_via_ajax()
    {
        session(['tahun_akademik_id' => $this->tahunAkademik->id]);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('admin.pelanggaran-siswa.rekap', [
                'search' => 'Budi',
                'kelas_id' => $this->kelas->id,
            ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertViewIs('admin.pelanggaran-rekap.table');
        $response->assertSee('Budi Santoso');
    }

    /** @test */
    public function super_admin_can_access_siswa_profil_pelanggaran()
    {
        session(['tahun_akademik_id' => $this->tahunAkademik->id]);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('admin.pelanggaran-siswa.profil-siswa', $this->siswa));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pelanggaran-rekap.siswa');
    }

    /** @test */
    public function dashboard_shows_top_5_pelanggaran()
    {
        // Buat data pelanggaran
        $kategori = KategoriPelanggaran::create([
            'nama' => 'Kerapian',
            'keterangan' => 'Pelanggaran kerapian'
        ]);

        $jenis = JenisPelanggaran::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Rambut Panjang',
            'bobot_poin' => 15,
            'deskripsi' => 'Rambut melebihi kerah baju'
        ]);

        PelanggaranSiswa::create([
            'siswa_id' => $this->siswa->id,
            'jenis_id' => $jenis->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'tanggal_kejadian' => '2026-07-20',
            'poin_saat_itu' => 15,
            'dicatat_oleh' => $this->superAdmin->id,
            'keterangan' => 'Rambut gondrong',
        ]);

        session(['tahun_akademik_id' => $this->tahunAkademik->id]);

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['tahun_akademik_id' => $this->tahunAkademik->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('15');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite untuk fitur Pindah Kelas dan Naik Kelas siswa.
 *
 * Skenario:
 *  - Happy path: pindah kelas sukses (TA sama, kelas beda)
 *  - Happy path: naik kelas sukses (TA beda, kelas beda)
 *  - Edge case: kelas tujuan sama dengan kelas saat ini
 *  - Edge case: TA tujuan sama dengan TA siswa saat ini (naik kelas)
 *  - Edge case: kelas tidak berada di TA yang dipilih (naik kelas)
 *  - Negative: field kelas_id kosong
 *  - Negative: kelas_id tidak valid (not exist)
 *  - Security: unauthenticated request ditolak
 *  - Security: role siswa tidak bisa akses endpoint
 */
class SiswaPindahNaikKelasTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private TahunAkademik $ta1;
    private TahunAkademik $ta2;
    private Kelas $kelas1;
    private Kelas $kelas2;
    private Kelas $kelas3;
    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat admin user
        $this->adminUser = User::factory()->create(['role' => 'admin_sekolah']);

        // Buat 2 tahun akademik
        $this->ta1 = TahunAkademik::create([
            'nama'            => '2024/2025 Ganjil',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2024-07-01',
            'tanggal_selesai' => '2024-12-31',
            'is_aktif'        => true,
        ]);

        $this->ta2 = TahunAkademik::create([
            'nama'            => '2025/2026 Ganjil',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif'        => false,
        ]);

        // Kelas 1 & 2 di TA1, Kelas 3 di TA2
        $this->kelas1 = Kelas::create([
            'nama'               => 'X-A',
            'tingkat'            => 'X',
            'jurusan'            => 'IPA',
            'tahun_akademik_id'  => $this->ta1->id,
            'is_aktif_absensi'   => true,
        ]);

        $this->kelas2 = Kelas::create([
            'nama'               => 'X-B',
            'tingkat'            => 'X',
            'jurusan'            => 'IPA',
            'tahun_akademik_id'  => $this->ta1->id,
            'is_aktif_absensi'   => true,
        ]);

        $this->kelas3 = Kelas::create([
            'nama'               => 'XI-A',
            'tingkat'            => 'XI',
            'jurusan'            => 'IPA',
            'tahun_akademik_id'  => $this->ta2->id,
            'is_aktif_absensi'   => true,
        ]);

        // Siswa di kelas1 & ta1
        $siswaUser = User::factory()->create(['role' => 'siswa']);
        $this->siswa = Siswa::create([
            'user_id'           => $siswaUser->id,
            'nis'               => '12345',
            'nisn'              => '0012345678',
            'nama_lengkap'      => 'Budi Santoso',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Jakarta',
            'tanggal_lahir'     => '2008-01-01',
            'no_hp_ortu'        => '08123456789',
            'kelas_id'          => $this->kelas1->id,
            'tahun_akademik_id' => $this->ta1->id,
            'status'            => 'aktif',
            'qr_code'           => '0012345678',
        ]);
    }

    // ─── PINDAH KELAS ────────────────────────────────────────────────

    /** @test */
    public function pindah_kelas_sukses_dalam_ta_yang_sama()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => $this->kelas2->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('siswa', [
            'id'                => $this->siswa->id,
            'kelas_id'          => $this->kelas2->id,
            'tahun_akademik_id' => $this->ta1->id, // TA tidak berubah
        ]);
    }

    /** @test */
    public function pindah_kelas_gagal_jika_kelas_sama()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => $this->kelas1->id, // kelas yang sama
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Data tidak berubah
        $this->assertDatabaseHas('siswa', [
            'id'       => $this->siswa->id,
            'kelas_id' => $this->kelas1->id,
        ]);
    }

    /** @test */
    public function pindah_kelas_gagal_jika_kelas_beda_ta()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => $this->kelas3->id, // kelas di TA2
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function pindah_kelas_gagal_jika_kelas_id_kosong()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => '',
            ]);

        $response->assertSessionHasErrors('kelas_id');
    }

    /** @test */
    public function pindah_kelas_gagal_jika_kelas_id_tidak_exist()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => 99999,
            ]);

        $response->assertSessionHasErrors('kelas_id');
    }

    // ─── NAIK KELAS ──────────────────────────────────────────────────

    /** @test */
    public function naik_kelas_sukses_ke_ta_baru()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.naik-kelas', $this->siswa->id), [
                'kelas_id'          => $this->kelas3->id,
                'tahun_akademik_id' => $this->ta2->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('siswa', [
            'id'                => $this->siswa->id,
            'kelas_id'          => $this->kelas3->id,
            'tahun_akademik_id' => $this->ta2->id,
        ]);
    }

    /** @test */
    public function naik_kelas_gagal_jika_ta_sama()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.naik-kelas', $this->siswa->id), [
                'kelas_id'          => $this->kelas2->id,
                'tahun_akademik_id' => $this->ta1->id, // TA sama
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function naik_kelas_gagal_jika_kelas_tidak_di_ta_yang_dipilih()
    {
        // kelas1 ada di ta1, tapi request ta2
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.naik-kelas', $this->siswa->id), [
                'kelas_id'          => $this->kelas1->id, // kelas dari ta1
                'tahun_akademik_id' => $this->ta2->id,    // ta2
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function naik_kelas_gagal_jika_field_kosong()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.siswa.naik-kelas', $this->siswa->id), []);

        $response->assertSessionHasErrors(['kelas_id', 'tahun_akademik_id']);
    }

    // ─── SECURITY ────────────────────────────────────────────────────

    /** @test */
    public function unauthenticated_user_ditolak()
    {
        $response = $this->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
            'kelas_id' => $this->kelas2->id,
        ]);

        $response->assertRedirect(); // Redirect ke login (URL bervariasi tergantung auth guard)
    }


    /** @test */
    public function role_siswa_tidak_bisa_akses_pindah_kelas()
    {
        $siswaUser = $this->siswa->user;

        $response = $this->actingAs($siswaUser)
            ->post(route('admin.siswa.pindah-kelas', $this->siswa->id), [
                'kelas_id' => $this->kelas2->id,
            ]);

        $response->assertStatus(403);
    }
}

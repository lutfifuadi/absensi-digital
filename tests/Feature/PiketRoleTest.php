<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiketRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat data penunjang seperti TahunAkademik
        TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'is_aktif' => true,
            'tanggal_mulai' => now()->startOfYear()->toDateString(),
            'tanggal_selesai' => now()->endOfYear()->toDateString(),
        ]);
    }

    /**
     * 1. Pastikan guest ter-redirect ke login.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response1 = $this->get('/piket/dashboard');
        $response1->assertRedirect('/login');

        $response2 = $this->get('/piket/scanner');
        $response2->assertRedirect('/login');
    }

    /**
     * 2. Pastikan user ber-role 'piket' bisa mengakses '/piket/dashboard' dan '/piket/scanner'.
     */
    public function test_piket_user_can_access_piket_routes(): void
    {
        $piketUser = User::factory()->create([
            'role' => User::ROLE_PIKET,
        ]);

        $response1 = $this->actingAs($piketUser)->get('/piket/dashboard');
        $response1->assertStatus(200);

        $response2 = $this->actingAs($piketUser)->get('/piket/scanner');
        $response2->assertStatus(200);
    }

    /**
     * 3. Pastikan user non-piket (seperti siswa atau guest) diblokir (403 atau redirect) saat mengakses rute piket tersebut.
     */
    public function test_non_piket_user_is_blocked_from_piket_routes(): void
    {
        // Siswa
        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $response1 = $this->actingAs($siswaUser)->get('/piket/dashboard');
        $response1->assertStatus(403);

        $response2 = $this->actingAs($siswaUser)->get('/piket/scanner');
        $response2->assertStatus(403);

        // Guru non-piket
        $guruUser = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);

        $response3 = $this->actingAs($guruUser)->get('/piket/dashboard');
        $response3->assertStatus(403);
    }

    /**
     * 4. Pastikan user ber-role 'piket' diblokir (403) saat mencoba mengakses '/admin/pengaturan' atau sejenisnya.
     */
    public function test_piket_user_is_blocked_from_admin_settings(): void
    {
        $piketUser = User::factory()->create([
            'role' => User::ROLE_PIKET,
        ]);

        $response = $this->actingAs($piketUser)->get('/admin/pengaturan');
        $response->assertStatus(403);
    }

    /**
     * 5. Pastikan update status kehadiran di PiketScannerController diblokir (403) jika tanggal edit bukan hari ini.
     */
    public function test_update_rekap_is_blocked_if_date_is_not_today(): void
    {
        $piketUser = User::factory()->create([
            'role' => User::ROLE_PIKET,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'tahun_akademik_id' => TahunAkademik::where('is_aktif', true)->first()->id,
            'jurusan' => 'Umum',
        ]);

        $siswa = Siswa::create([
            'nisn' => '0011223344',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456789',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $kelas->tahun_akademik_id,
            'status' => 'aktif',
        ]);

        // Tanggal kemarin (bukan hari ini)
        $yesterday = now()->subDay()->toDateString();

        $response = $this->actingAs($piketUser)->postJson('/piket/rekap/update', [
            'siswa_id' => $siswa->id,
            'tanggal' => $yesterday,
            'status' => 'hadir',
            'keterangan' => 'Test edit rekap kemarin',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonFragment([
            'success' => false,
        ]);
    }
}

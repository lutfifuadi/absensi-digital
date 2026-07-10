<?php

namespace Tests\Feature;

use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicKegiatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_redirects_if_authenticated()
    {
        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->get(route('public.kegiatan.index'));

        $response->assertRedirect(route('public.kegiatan.scan'));
    }

    public function test_index_shows_login_view_if_not_authenticated()
    {
        $response = $this->get(route('public.kegiatan.index'));

        // View public.kegiatan.login may not exist yet, but it should return 200 or at least look for the view
        $response->assertStatus(200);
        $response->assertViewIs('public.kegiatan.login');
    }

    public function test_auth_success_with_default_password()
    {
        // Default password is kegiatan2026
        $response = $this->post(route('public.kegiatan.auth'), [
            'password' => 'kegiatan2026',
        ]);

        $response->assertRedirect(route('public.kegiatan.scan'));
        $this->assertTrue(session('kegiatan_public_authenticated'));
    }

    public function test_auth_success_with_stored_hash()
    {
        Pengaturan::create([
            'key' => 'password_unlock_scan_qr',
            'value' => Hash::make('rahasia123'),
        ]);

        $response = $this->post(route('public.kegiatan.auth'), [
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('public.kegiatan.scan'));
        $this->assertTrue(session('kegiatan_public_authenticated'));
    }

    public function test_auth_success_with_stored_plaintext()
    {
        Pengaturan::create([
            'key' => 'password_unlock_scan_qr',
            'value' => 'rahasiaPlain',
        ]);

        $response = $this->post(route('public.kegiatan.auth'), [
            'password' => 'rahasiaPlain',
        ]);

        $response->assertRedirect(route('public.kegiatan.scan'));
        $this->assertTrue(session('kegiatan_public_authenticated'));
    }

    public function test_auth_fails_with_wrong_password()
    {
        $response = $this->post(route('public.kegiatan.auth'), [
            'password' => 'salah',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('password');
        $this->assertNull(session('kegiatan_public_authenticated'));
    }

    public function test_scan_requires_authentication()
    {
        $response = $this->get(route('public.kegiatan.scan'));

        $response->assertRedirect(route('public.kegiatan.index'));
    }

    public function test_scan_displays_kegiatans_when_authenticated()
    {
        $ta = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addYear(),
            'is_aktif' => true,
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Upacara Bendera',
            'tanggal_pelaksanaan' => now(),
            'waktu_mulai' => '07:00:00',
            'waktu_selesai' => '08:00:00',
            'qr_code_kegiatan' => 'UPACARA-123',
            'tahun_akademik_id' => $ta->id,
            'target_tingkat' => null,
            'target_jurusan' => null,
            'target_peserta' => null,
        ]);

        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->get(route('public.kegiatan.scan'));

        $response->assertStatus(200);
        $response->assertViewIs('public.kegiatan.scan');
        $response->assertViewHas('kegiatans');
    }

    public function test_process_requires_authentication()
    {
        $response = $this->postJson(route('public.kegiatan.process'), [
            'qr_code' => '123456',
            'kegiatan_id' => 1,
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi tidak valid. Silakan masuk kembali.',
        ]);
    }

    public function test_process_success_with_matching_target()
    {
        $ta = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addYear(),
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $ta->id,
        ]);

        $siswa = Siswa::create([
            'nis' => '1111',
            'nisn' => '2222',
            'nama_lengkap' => 'Budi',
            'kelas_id' => $kelas->id,
            'qr_code' => 'KARTU-BUDI',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'tahun_akademik_id' => $ta->id,
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Workshop IT',
            'tanggal_pelaksanaan' => now(),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
            'qr_code_kegiatan' => 'WORKSHOP-123',
            'tahun_akademik_id' => $ta->id,
            'target_tingkat' => ['XII'],
            'target_jurusan' => ['RPL'],
            'target_peserta' => [$kelas->id],
        ]);

        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->postJson(route('public.kegiatan.process'), [
                'qr_code' => 'KARTU-BUDI',
                'kegiatan_id' => $kegiatan->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_new' => true,
            'siswa_nama' => 'Budi',
            'siswa_kelas' => 'XII RPL 1',
            'total_hadir' => 1,
        ]);

        $this->assertDatabaseHas('absensi_kegiatan', [
            'kegiatan_id' => $kegiatan->id,
            'siswa_id' => $siswa->id,
            'status' => 'hadir',
        ]);
    }

    public function test_process_fails_if_not_target()
    {
        $ta = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addYear(),
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $ta->id,
        ]);

        $siswa = Siswa::create([
            'nis' => '1111',
            'nisn' => '2222',
            'nama_lengkap' => 'Budi',
            'kelas_id' => $kelas->id,
            'qr_code' => 'KARTU-BUDI',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'tahun_akademik_id' => $ta->id,
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Workshop IT khusus XII',
            'tanggal_pelaksanaan' => now(),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
            'qr_code_kegiatan' => 'WORKSHOP-123',
            'tahun_akademik_id' => $ta->id,
            'target_tingkat' => ['XII'],
        ]);

        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->postJson(route('public.kegiatan.process'), [
                'qr_code' => 'KARTU-BUDI',
                'kegiatan_id' => $kegiatan->id,
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.',
        ]);
    }

    public function test_process_returns_duplicate_message()
    {
        $ta = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addYear(),
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $ta->id,
        ]);

        $siswa = Siswa::create([
            'nis' => '1111',
            'nisn' => '2222',
            'nama_lengkap' => 'Budi',
            'kelas_id' => $kelas->id,
            'qr_code' => 'KARTU-BUDI',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'tahun_akademik_id' => $ta->id,
        ]);

        $kegiatan = Kegiatan::create([
            'nama_kegiatan' => 'Workshop IT',
            'tanggal_pelaksanaan' => now(),
            'waktu_mulai' => '08:00:00',
            'waktu_selesai' => '10:00:00',
            'qr_code_kegiatan' => 'WORKSHOP-123',
            'tahun_akademik_id' => $ta->id,
        ]);

        AbsensiKegiatan::create([
            'kegiatan_id' => $kegiatan->id,
            'siswa_id' => $siswa->id,
            'jam_absen' => now(),
            'status' => 'hadir',
        ]);

        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->postJson(route('public.kegiatan.process'), [
                'qr_code' => 'KARTU-BUDI',
                'kegiatan_id' => $kegiatan->id,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'is_new' => false,
            'message' => 'Siswa sudah melakukan absensi pada kegiatan ini.',
            'siswa_nama' => 'Budi',
            'total_hadir' => 1,
        ]);
    }

    public function test_logout_clears_session()
    {
        $response = $this->withSession(['kegiatan_public_authenticated' => true])
            ->post(route('public.kegiatan.logout'));

        $response->assertRedirect(route('public.kegiatan.index'));
        $this->assertNull(session('kegiatan_public_authenticated'));
    }
}

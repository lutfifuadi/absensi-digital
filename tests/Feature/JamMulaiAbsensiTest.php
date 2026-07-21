<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class JamMulaiAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $siswaUser;
    protected Siswa $siswa;
    protected Kelas $kelas;
    protected TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat User Admin
        $this->admin = User::create([
            'name' => 'Admin Sekolah',
            'username' => 'admin_sekolah',
            'email' => 'admin@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        // 2. Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 3. Buat Kelas
        $this->kelas = Kelas::create([
            'nama' => 'X MIPA 1',
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // 4. Buat User Siswa dan Siswa
        $this->siswaUser = User::create([
            'name' => 'Siswa Test',
            'username' => '12345',
            'email' => 'siswa@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'siswa',
            'email_verified_at' => now(),
        ]);

        $this->siswa = Siswa::create([
            'user_id' => $this->siswaUser->id,
            'nis' => '12345',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'qr_code' => 'QR-SISWA-TEST-123',
        ]);

        // Setup default Pengaturan
        Pengaturan::updateOrCreate(['key' => 'latitude'], ['value' => '-6.922405', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'longitude'], ['value' => '107.5717651', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'radius_jarak_absen'], ['value' => '900', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'minimal_akurasi_gps'], ['value' => '100', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'deteksi_fake_gps'], ['value' => 'Tidak', 'group' => 'absensi']);
    }

    /**
     * Test Case 1: Pengaturan jam_mulai_absensi
     * - Sukses jika jam_mulai_absensi sebelum jam_masuk
     * - Gagal jika jam_mulai_absensi setelah/sama dengan jam_masuk
     */
    public function test_pengaturan_jam_mulai_absensi_validation(): void
    {
        // Hubungkan session jika role selector / middleware membutuhkan
        $this->actingAs($this->admin)->withSession(['active_role' => User::ROLE_ADMIN_SEKOLAH]);

        // Scenario 1: Sukses - jam_mulai_absensi sebelum jam_masuk (misal 06:00 < 07:00)
        $response = $this->post('/admin/pengaturan', [
            'jam_mulai_absensi' => '06:00',
            'jam_masuk' => '07:00',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
        
        // Scenario 2: Gagal - jam_mulai_absensi setelah jam_masuk (misal 08:00 >= 07:00)
        $response2 = $this->post('/admin/pengaturan', [
            'jam_mulai_absensi' => '08:00',
            'jam_masuk' => '07:00',
        ]);

        $response2->assertStatus(302);
        $response2->assertSessionHas('error', 'Jam mulai absensi harus bernilai lebih awal dari jam masuk.');

        // Scenario 3: Gagal - jam_mulai_absensi sama dengan jam_masuk (misal 07:00 >= 07:00)
        $response3 = $this->post('/admin/pengaturan', [
            'jam_mulai_absensi' => '07:00',
            'jam_masuk' => '07:00',
        ]);

        $response3->assertStatus(302);
        $response3->assertSessionHas('error', 'Jam mulai absensi harus bernilai lebih awal dari jam masuk.');
    }

    /**
     * Test Case 2: Absensi mandiri siswa
     * - Ditolak jika sebelum jam_mulai_absensi
     * - Tidak ditolak/diterima jika sesudah jam_mulai_absensi
     */
    public function test_absensi_mandiri_siswa_time_validation(): void
    {
        // Izinkan lokasi absensi mandiri
        Pengaturan::updateOrCreate(['key' => 'izinkan_lokasi_absensi_mandiri'], ['value' => 'Ya', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '06:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00', 'group' => 'absensi']);

        // Scenario 1: Ditolak sebelum jam mulai absensi (Jam 05:30)
        Carbon::setTestNow(Carbon::create(2026, 7, 22, 5, 30, 0));

        $response = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => 'siswa'])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Silakan kembali setelah pukul 06:00 WIB.'
        ]);

        // Scenario 2: Tidak ditolak karena waktu mulai absensi (Jam 06:15)
        Carbon::setTestNow(Carbon::create(2026, 7, 22, 6, 15, 0));

        $response2 = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => 'siswa'])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        // Cek response, harusnya tidak ditolak karena "Absensi belum dibuka..."
        $response2->assertStatus(200);
        $this->assertNotEquals('Absensi belum dibuka. Silakan kembali setelah pukul 06:00 WIB.', $response2->json('message'));
        // Jika sukses absensi dicatat atau ada validasi lain (misal sudah absen/hadir), itu ok selama tidak ditolak karena "belum dibuka"
    }

    /**
     * Test Case 3: Scan QR Publik
     * - Ditolak jika sebelum jam_mulai_absensi
     * - Berhasil/proses dilanjutkan jika sesudah jam_mulai_absensi
     */
    public function test_scan_qr_publik_time_validation(): void
    {
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '06:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15', 'group' => 'absensi']);

        // Authenticate qr_scan_authenticated session (required by qr.scan.auth middleware)
        $this->withSession(['qr_scan_authenticated' => true]);

        // Scenario 1: Ditolak sebelum jam mulai absensi (Jam 05:45)
        Carbon::setTestNow(Carbon::create(2026, 7, 22, 5, 45, 0));

        $response = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Sesi scan dimulai pukul 06:00 WIB.'
        ]);

        // Scenario 2: Sukses/proses dilanjutkan setelah jam mulai absensi (Jam 06:30)
        Carbon::setTestNow(Carbon::create(2026, 7, 22, 6, 30, 0));

        $response2 = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123'
        ]);

        $response2->assertStatus(200);
        // Pastikan sukses mencatat kehadiran
        $response2->assertJson([
            'success' => true
        ]);
        
        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa->id,
            'status' => 'hadir'
        ]);
    }
}

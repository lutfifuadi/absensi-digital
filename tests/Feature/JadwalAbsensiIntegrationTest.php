<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Test Cases untuk integrasi JadwalAbsensiHelper dengan controller absensi.
 *
 * PRD-016: Menggunakan jadwal per kelas per hari di
 * PublicQrScanController dan AbsensiMandiriController.
 *
 * Test ini memverifikasi bahwa kedua controller membaca
 * jadwal absensi per kelas per hari dengan benar.
 */
class JadwalAbsensiIntegrationTest extends TestCase
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

        // Buat User Admin
        $this->admin = User::create([
            'name'             => 'Admin Sekolah',
            'username'         => 'admin_sekolah',
            'email'            => 'admin@sekolah.sch.id',
            'password'         => bcrypt('password123'),
            'role'             => User::ROLE_ADMIN_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        // Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama'            => '2026/2027',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif'        => true,
        ]);

        // Buat Kelas
        $this->kelas = Kelas::create([
            'nama'               => 'X MIPA 1',
            'tingkat'            => 'X',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Buat User Siswa dan Siswa
        $this->siswaUser = User::create([
            'name'             => 'Siswa Test',
            'username'         => '12345',
            'email'            => 'siswa@sekolah.sch.id',
            'password'         => bcrypt('password123'),
            'role'             => User::ROLE_SISWA,
            'email_verified_at' => now(),
        ]);

        $this->siswa = Siswa::create([
            'user_id'           => $this->siswaUser->id,
            'nis'               => '12345',
            'nisn'              => '1234567890',
            'nama_lengkap'      => 'Siswa Test',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Jakarta',
            'tanggal_lahir'     => '2010-01-01',
            'kelas_id'          => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status'            => 'aktif',
            'qr_code'           => 'QR-SISWA-TEST-123',
        ]);

        // Setup default Pengaturan
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '06:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_pulang'], ['value' => '15:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_pulang'], ['value' => '14:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_akhir_pulang'], ['value' => '17:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'latitude'], ['value' => '-6.922405', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'longitude'], ['value' => '107.5717651', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'radius_jarak_absen'], ['value' => '900', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'minimal_akurasi_gps'], ['value' => '100', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'deteksi_fake_gps'], ['value' => 'Tidak', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'izinkan_lokasi_absensi_mandiri'], ['value' => 'Ya', 'group' => 'absensi']);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: PublicQrScanController — Jadwal Per Kelas
    // ════════════════════════════════════════════════════════════════════════

    public function test_qr_scan_uses_jadwal_per_kelas(): void
    {
        // Setup: Jadwal khusus untuk kelas senin jam_mulai_absensi = 06:30
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin', // Senin
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        // Set waktu test ke Senin 2026-07-27 pukul 06:20 (sebelum jam_mulai_absensi kelas)
        $this->travelTo(Carbon::create(2026, 7, 27, 6, 20, 0));

        $this->withSession(['qr_scan_authenticated' => true]);

        $response = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Sesi scan dimulai pukul 06:30 WIB.',
        ]);

        $this->travelBack();
    }

    public function test_qr_scan_rejects_on_libur(): void
    {
        // Setup: Jadwal kelas sabtu = libur
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'sabtu',
            'is_libur'   => true,
        ]);

        // Set waktu test ke Sabtu 2026-07-25 pukul 07:00
        $this->travelTo(Carbon::create(2026, 7, 25, 7, 0, 0));

        $this->withSession(['qr_scan_authenticated' => true]);

        $response = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);

        $message = $response->json('message');
        $this->assertStringContainsString('hari libur', strtolower($message));

        $this->travelBack();
    }

    public function test_qr_scan_falls_back_to_global_when_no_jadwal(): void
    {
        // Tidak ada jadwal per kelas → gunakan global jam_mulai_absensi = 06:00
        // Set waktu test ke Senin 2026-07-27 pukul 06:10 (sebelum global 06:00? Tidak, setelah)
        // Kita set jam 05:30 — sebelum global 06:00
        $this->travelTo(Carbon::create(2026, 7, 27, 5, 30, 0));

        $this->withSession(['qr_scan_authenticated' => true]);

        $response = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Sesi scan dimulai pukul 06:00 WIB.',
        ]);

        $this->travelBack();
    }

    public function test_qr_scan_uses_jam_mulai_absensi_from_jadwal(): void
    {
        // Setup: Jadwal khusus jam_mulai_absensi = 08:00 (lebih late dari global)
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'selasa',
            'jam_mulai_absensi'  => '08:00',
            'jam_masuk'          => '09:00',
            'jam_pulang'         => '16:00',
            'jam_akhir_pulang'   => '18:00',
            'is_libur'           => false,
        ]);

        // Set waktu test ke Selasa 2026-07-28 pukul 07:30 (setelah global 06:00, sebelum kelas 08:00)
        $this->travelTo(Carbon::create(2026, 7, 28, 7, 30, 0));

        $this->withSession(['qr_scan_authenticated' => true]);

        $response = $this->postJson('/scan-qr/process', [
            'qr_code' => 'QR-SISWA-TEST-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);

        // Harus ditolak karena waktu 07:30 < jam_mulai_absensi kelas 08:00
        $message = $response->json('message');
        $this->assertStringContainsString('belum dibuka', strtolower($message));
        $this->assertStringContainsString('08:00', $message);

        $this->travelBack();
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: AbsensiMandiriController — Jadwal Per Kelas
    // ════════════════════════════════════════════════════════════════════════

    public function test_absensi_mandiri_uses_jadwal_per_kelas(): void
    {
        // Setup: Jadwal khusus jam_mulai_absensi = 06:30
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        // Set waktu test ke Senin 2026-07-27 pukul 06:20 (sebelum 06:30)
        $this->travelTo(Carbon::create(2026, 7, 27, 6, 20, 0));

        $response = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => User::ROLE_SISWA])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Silakan kembali setelah pukul 06:30 WIB.',
        ]);

        $this->travelBack();
    }

    public function test_absensi_mandiri_rejects_on_libur(): void
    {
        // Setup: Jadwal kelas minggu = libur
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'minggu',
            'is_libur'   => true,
        ]);

        // Set waktu test ke Minggu 2026-07-26 pukul 07:00
        $this->travelTo(Carbon::create(2026, 7, 26, 7, 0, 0));

        $response = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => User::ROLE_SISWA])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);

        $message = $response->json('message');
        $this->assertStringContainsString('libur', strtolower($message));

        $this->travelBack();
    }

    public function test_absensi_mandiri_falls_back_to_global_when_no_jadwal(): void
    {
        // Tidak ada jadwal per kelas → gunakan global jam_mulai_absensi = 06:00
        // Set waktu test ke Senin 2026-07-27 pukul 05:30 (sebelum global 06:00)
        $this->travelTo(Carbon::create(2026, 7, 27, 5, 30, 0));

        $response = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => User::ROLE_SISWA])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi belum dibuka. Silakan kembali setelah pukul 06:00 WIB.',
        ]);

        $this->travelBack();
    }

    public function test_absensi_mandiri_allows_after_jam_mulai_absensi_from_jadwal(): void
    {
        // Setup: Jadwal khusus jam_mulai_absensi = 06:30
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        // Set waktu test ke Senin 2026-07-27 pukul 06:35 (setelah jam_mulai_absensi 06:30)
        $this->travelTo(Carbon::create(2026, 7, 27, 6, 35, 0));

        $response = $this->actingAs($this->siswaUser)
            ->withSession(['active_role' => User::ROLE_SISWA])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
            ]);

        $response->assertStatus(200);

        // Tidak boleh ditolak karena "belum dibuka"
        $message = $response->json('message');
        $this->assertStringNotContainsString('belum dibuka', strtolower($message));

        $this->travelBack();
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: JadwalAbsensiHelper Integration
    // ════════════════════════════════════════════════════════════════════════

    public function test_helper_returns_class_schedule_when_exists(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        $jadwal = \App\Helpers\JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');

        $this->assertEquals('06:30', $jadwal['jam_mulai_absensi']);
        $this->assertEquals('07:30', $jadwal['jam_masuk']);
        $this->assertEquals('15:30', $jadwal['jam_pulang']);
        $this->assertEquals('17:30', $jadwal['jam_akhir_pulang']);
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_helper_falls_back_to_global_when_field_null(): void
    {
        // Buat jadwal dengan semua field time = null (hanya is_libur = false)
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => null,
            'jam_masuk'          => null,
            'jam_pulang'         => null,
            'jam_akhir_pulang'   => null,
            'is_libur'           => false,
        ]);

        $jadwal = \App\Helpers\JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');

        // Harus fallback ke default global
        $this->assertEquals('06:00', $jadwal['jam_mulai_absensi']);
        $this->assertEquals('07:00', $jadwal['jam_masuk']);
        $this->assertEquals('15:00', $jadwal['jam_pulang']);
        $this->assertEquals('17:00', $jadwal['jam_akhir_pulang']);
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_helper_falls_back_to_global_when_no_record(): void
    {
        // Tidak ada record sama sekali
        $jadwal = \App\Helpers\JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');

        $this->assertEquals('06:00', $jadwal['jam_mulai_absensi']);
        $this->assertEquals('07:00', $jadwal['jam_masuk']);
        $this->assertEquals('15:00', $jadwal['jam_pulang']);
        $this->assertEquals('17:00', $jadwal['jam_akhir_pulang']);
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_helper_is_libur_true_when_set(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'sabtu',
            'is_libur'   => true,
        ]);

        $isLibur = \App\Helpers\JadwalAbsensiHelper::isLibur($this->kelas->id, 'sabtu');

        $this->assertTrue($isLibur);
    }

    public function test_helper_is_libur_false_when_not_set(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'is_libur'   => false,
        ]);

        $isLibur = \App\Helpers\JadwalAbsensiHelper::isLibur($this->kelas->id, 'senin');

        $this->assertFalse($isLibur);
    }

    public function test_helper_is_libur_false_when_no_record(): void
    {
        // Tidak ada record → default false
        $isLibur = \App\Helpers\JadwalAbsensiHelper::isLibur($this->kelas->id, 'sabtu');

        $this->assertFalse($isLibur);
    }
}

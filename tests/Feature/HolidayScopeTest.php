<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use App\Models\Holiday;
use App\Models\AbsensiSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HolidayScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can store holidays with various scopes.
     */
    public function test_admin_can_store_holiday_with_various_scopes(): void
    {
        // 1. Autentikasi sebagai User dengan role super_admin
        $user = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // 2. Buat TahunAkademik aktif dan simpan id-nya di session
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 3. Buat data Kelas dummy (misal tingkat 'XI')
        $kelas = Kelas::create([
            'nama' => 'XI RPL 1',
            'tingkat' => 'XI',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        // Libur Global (tingkat & kelas_id null)
        $responseGlobal = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->post(route('admin.holidays.store'), [
                'tanggal' => '2026-08-01',
                'nama' => 'Libur Global',
                'jenis' => 'school',
                'tingkat' => null,
                'kelas_id' => null,
            ]);

        $responseGlobal->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'nama' => 'Libur Global',
            'tingkat' => null,
            'kelas_id' => null,
        ]);

        // Libur Tingkat (tingkat = 'XI', kelas_id null)
        $responseTingkat = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->post(route('admin.holidays.store'), [
                'tanggal' => '2026-08-02',
                'nama' => 'Libur Tingkat XI',
                'jenis' => 'school',
                'tingkat' => 'XI',
                'kelas_id' => null,
            ]);

        $responseTingkat->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'nama' => 'Libur Tingkat XI',
            'tingkat' => 'XI',
            'kelas_id' => null,
        ]);

        // Libur Kelas (tingkat = null, kelas_id = $kelas->id)
        $responseKelas = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->post(route('admin.holidays.store'), [
                'tanggal' => '2026-08-03',
                'nama' => 'Libur Kelas XI RPL 1',
                'jenis' => 'school',
                'tingkat' => null,
                'kelas_id' => $kelas->id,
            ]);

        $responseKelas->assertRedirect();
        $this->assertDatabaseHas('holidays', [
            'nama' => 'Libur Kelas XI RPL 1',
            'tingkat' => null,
            'kelas_id' => $kelas->id,
        ]);

        // Libur dengan tingkat & kelas yang bertentangan (tingkat = 'XII', tapi kelas_id = kelas dengan tingkat 'XI')
        $responseConflict = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->from(route('admin.holidays'))
            ->post(route('admin.holidays.store'), [
                'tanggal' => '2026-08-04',
                'nama' => 'Libur Konflik',
                'jenis' => 'school',
                'tingkat' => 'XII',
                'kelas_id' => $kelas->id,
            ]);

        $responseConflict->assertRedirect(route('admin.holidays'));
        $responseConflict->assertSessionHasErrors(['tingkat']);
    }

    /**
     * Test auto mark alpha command ignores students on holiday.
     */
    public function test_auto_mark_alpha_command_ignores_students_on_holiday(): void
    {
        // 1. Buat data TahunAkademik aktif
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 2. Buat Kelas K1 tingkat 'X' dan Kelas K2 tingkat 'XI'
        $k1 = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        $k2 = Kelas::create([
            'nama' => 'XI RPL 1',
            'tingkat' => 'XI',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        // 3. Buat Siswa S1 aktif di kelas K1, dan Siswa S2 aktif di kelas K2
        $s1User = User::factory()->create(['role' => User::ROLE_SISWA]);
        $s1 = Siswa::create([
            'user_id' => $s1User->id,
            'nama_lengkap' => 'Siswa X',
            'nis' => '10001',
            'nisn' => '1000000001',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'kelas_id' => $k1->id,
            'tahun_akademik_id' => $tahun->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
        ]);

        $s2User = User::factory()->create(['role' => User::ROLE_SISWA]);
        $s2 = Siswa::create([
            'user_id' => $s2User->id,
            'nama_lengkap' => 'Siswa XI',
            'nis' => '20001',
            'nisn' => '2000000001',
            'jenis_kelamin' => 'P',
            'status' => 'aktif',
            'kelas_id' => $k2->id,
            'tahun_akademik_id' => $tahun->id,
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2009-01-01',
        ]);

        // 4. Buat hari libur tingkat 'XI' pada tanggal hari ini
        $today = now()->toDateString();
        Holiday::create([
            'tanggal' => $today,
            'nama' => 'Libur Khusus XI',
            'jenis' => 'school',
            'is_national_holiday' => false,
            'tingkat' => 'XI',
            'kelas_id' => null,
        ]);

        // 5. Jalankan command absensi:auto-alpha
        $this->artisan('absensi:auto-alpha')
            ->assertExitCode(0);

        // 6. Assert status 'alpha' untuk Siswa S1 (tingkat X)
        $this->assertTrue(AbsensiSiswa::where([
            'siswa_id' => $s1->id,
            'status' => 'alpha',
        ])->whereDate('tanggal', $today)->exists());

        // 7. Assert TIDAK terbuat data status 'alpha' untuk Siswa S2 (tingkat XI)
        $this->assertFalse(AbsensiSiswa::where([
            'siswa_id' => $s2->id,
            'status' => 'alpha',
        ])->whereDate('tanggal', $today)->exists());
    }

    /**
     * Test parent dashboard filters holidays correctly.
     */
    public function test_parent_dashboard_filters_holidays_correctly(): void
    {
        // 1. Buat TahunAkademik
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 2. Buat Kelas (tingkat XI)
        $kelasXI = Kelas::create([
            'nama' => 'XI RPL 1',
            'tingkat' => 'XI',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        // Buat kelas lain (misal tingkat XII atau kelas lain)
        $kelasXII = Kelas::create([
            'nama' => 'XII RPL 1',
            'tingkat' => 'XII',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        // 3. Buat Siswa di kelas tersebut
        $siswaUser = User::factory()->create(['role' => User::ROLE_SISWA]);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nama_lengkap' => 'Siswa XI',
            'nis' => '20002',
            'nisn' => '2000000002',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'kelas_id' => $kelasXI->id,
            'tahun_akademik_id' => $tahun->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2009-05-05',
        ]);

        // 4. Buat user Orang Tua, hubungkan dengan Siswa
        $parentUser = User::factory()->create(['role' => User::ROLE_ORANG_TUA]);
        // Coba hubungkan siswa ke ortu menggunakan table pivot siswa_ortu jika ada, atau lewat kolom `ortu_user_id` di siswa
        $siswa->update(['ortu_user_id' => $parentUser->id]);
        
        // Coba insert ke pivot table siswa_ortu jika tabelnya ada
        try {
            DB::table('siswa_ortu')->insert([
                'siswa_id' => $siswa->id,
                'user_id' => $parentUser->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            // Silently ignore if table doesn't exist
        }

        // Tentukan range tanggal bulan ini agar masuk ke query DashboardController
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        // Kita letakkan tanggal libur di tanggal 10 bulan ini
        $dateLibur = now()->startOfMonth()->addDays(9)->toDateString();

        // 5. Buat hari libur:
        // Libur A (Global, tingkat & kelas null)
        $liburA = Holiday::create([
            'tanggal' => $dateLibur,
            'nama' => 'Libur Global',
            'jenis' => 'school',
            'is_national_holiday' => false,
            'tingkat' => null,
            'kelas_id' => null,
        ]);

        // Libur B (Tingkat XI, kelas null)
        $liburB = Holiday::create([
            'tanggal' => $dateLibur,
            'nama' => 'Libur Tingkat XI',
            'jenis' => 'school',
            'is_national_holiday' => false,
            'tingkat' => 'XI',
            'kelas_id' => null,
        ]);

        // Libur C (Tingkat XII, kelas null)
        $liburC = Holiday::create([
            'tanggal' => $dateLibur,
            'nama' => 'Libur Tingkat XII',
            'jenis' => 'school',
            'is_national_holiday' => false,
            'tingkat' => 'XII',
            'kelas_id' => null,
        ]);

        // Libur D (Kelas lain, kelas_id = kelas lain)
        $liburD = Holiday::create([
            'tanggal' => $dateLibur,
            'nama' => 'Libur Kelas XII',
            'jenis' => 'school',
            'is_national_holiday' => false,
            'tingkat' => null,
            'kelas_id' => $kelasXII->id,
        ]);

        // 6. Lakukan request sebagai user Orang Tua ke dashboard orang tua.
        // Di routes/web.php, dashboard orang tua diakses dengan route 'ortu.dashboard'
        // route: ortu.dashboard
        $response = $this->actingAs($parentUser)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_ORANG_TUA])
            ->get(route('ortu.dashboard'));

        $response->assertStatus(200);

        // 7. Assert view data holidays hanya berisi Libur A dan Libur B, serta tidak berisi Libur C dan Libur D.
        $holidaysInView = $response->viewData('holidays');

        // $holidaysInView bertipe array (atau Map) dengan key = tanggal, value = nama libur ter-gabung (seperti "Libur Global | Libur Tingkat XI")
        $this->assertArrayHasKey($dateLibur, $holidaysInView);
        
        $holidayNames = $holidaysInView[$dateLibur];
        
        $this->assertStringContainsString('Libur Global', $holidayNames);
        $this->assertStringContainsString('Libur Tingkat XI', $holidayNames);
        
        $this->assertStringNotContainsString('Libur Tingkat XII', $holidayNames);
        $this->assertStringNotContainsString('Libur Kelas XII', $holidayNames);
    }

    /**
     * Test siswa attendance is rejected on holidays.
     */
    public function test_absensi_siswa_is_rejected_on_holiday(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-05 07:30:00'));
        $today = '2026-08-05';

        // 1. Buat Tahun Akademik
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 2. Buat Kelas
        $kelas = Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'tahun_akademik_id' => $tahun->id,
            'is_aktif_absensi' => true,
        ]);

        // 3. Buat User Siswa dan Siswa
        $siswaUser = User::factory()->create(['role' => User::ROLE_SISWA]);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nis' => '12345',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Siswa Test Holiday',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahun->id,
            'status' => 'aktif',
            'qr_code' => 'QR-HOLIDAY-TEST',
        ]);

        // Setup default Pengaturan
        \App\Models\Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '06:00', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '08:00', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'izinkan_lokasi_absensi_mandiri'], ['value' => 'Ya', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'latitude'], ['value' => '-6.922405', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'longitude'], ['value' => '107.5717651', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'radius_jarak_absen'], ['value' => '900', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'minimal_akurasi_gps'], ['value' => '100', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'deteksi_fake_gps'], ['value' => 'Tidak', 'group' => 'absensi']);
        \App\Models\Pengaturan::updateOrCreate(['key' => 'lock_device_pc'], ['value' => 'Tidak', 'group' => 'absensi']);

        // 4. Buat Hari Libur
        Holiday::create([
            'tanggal' => $today,
            'nama' => 'Libur Nasional Keren',
            'jenis' => 'national',
            'is_national_holiday' => true,
            'tingkat' => null,
            'kelas_id' => null,
        ]);

        // A. Public QR Scan Process
        $response = $this->withSession(['qr_scan_authenticated' => true])
            ->postJson('/scan-qr/process', [
                'qr_code' => 'QR-HOLIDAY-TEST'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'message' => 'Absensi ditolak. Hari ini adalah Hari Libur: Libur Nasional Keren.'
        ]);

        // B. Live Board Scan Process
        $responseLive = $this->postJson('/live-board/scan', [
            'qr_code' => 'QR-HOLIDAY-TEST',
            'mode' => 'masuk'
        ]);

        $responseLive->assertStatus(200);
        $responseLive->assertJson([
            'success' => false,
            'message' => 'Absensi ditolak. Hari ini adalah Hari Libur: Libur Nasional Keren.'
        ]);

        // C. Absensi Mandiri Process
        $responseMandiri = $this->actingAs($siswaUser)
            ->withSession(['active_role' => 'siswa'])
            ->postJson('/siswa/absensi-mandiri', [
                'lat' => '-6.922405',
                'lng' => '107.5717651',
                'accuracy' => 10
            ]);

        $responseMandiri->assertStatus(200);
        $responseMandiri->assertJson([
            'success' => false,
            'message' => 'Absensi mandiri ditolak. Hari ini adalah Hari Libur: Libur Nasional Keren.'
        ]);
    }
}

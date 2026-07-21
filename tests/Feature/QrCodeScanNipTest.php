<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guru;
use App\Models\StaffTataUsaha;
use App\Models\Pengaturan;
use App\Models\AbsensiGuru;
use App\Models\AbsensiStaff;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeScanNipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat settings default agar pencarian dan scan absensi tidak error karena setting kosong
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_batas_masuk'], ['value' => '23:59']);
        Pengaturan::updateOrCreate(['key' => 'jam_pulang'], ['value' => '15:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_pulang'], ['value' => '23:00']);
        Pengaturan::updateOrCreate(['key' => 'jam_akhir_pulang'], ['value' => '23:59']);
        Pengaturan::updateOrCreate(['key' => 'toleransi_terlambat'], ['value' => '15']);
    }

    public function test_piket_scanner_can_scan_guru_using_qr_code(): void
    {
        $piketUser = User::factory()->create(['role' => User::ROLE_PIKET]); // guru piket
        
        $guruUser = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Ahmad Guru',
            'nip' => '198801012020011001',
            'qr_code' => 'QR-GURU-01',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
        ]);

        $response = $this->actingAs($piketUser)->postJson(route('piket.scanner.process'), [
            'qr_code' => 'QR-GURU-01',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('siswa.nama', 'Ahmad Guru');

        $this->assertDatabaseHas('absensi_guru', [
            'guru_id' => $guru->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_piket_scanner_can_scan_guru_using_nip(): void
    {
        $piketUser = User::factory()->create(['role' => User::ROLE_PIKET]);
        
        $guruUser = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Budi Guru',
            'nip' => '198801012020011002',
            'qr_code' => 'QR-GURU-02',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Fisika',
        ]);

        // Scan menggunakan NIP, bukan qr_code
        $response = $this->actingAs($piketUser)->postJson(route('piket.scanner.process'), [
            'qr_code' => '198801012020011002',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('siswa.nama', 'Budi Guru');

        $this->assertDatabaseHas('absensi_guru', [
            'guru_id' => $guru->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_admin_scanner_can_scan_guru_using_qr_code(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        
        $guruUser = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Cici Guru',
            'nip' => '198801012020011003',
            'qr_code' => 'QR-GURU-03',
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
            'mata_pelajaran' => 'Kimia',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.scan-qr.store'), [
            'scan_type' => 'guru',
            'qr_code' => 'QR-GURU-03',
        ]);

        $response->assertRedirect(route('admin.scan-qr.index'));
        $response->assertSessionHas('success', 'Absensi Cici Guru berhasil dicatat.');

        $this->assertDatabaseHas('absensi_guru', [
            'guru_id' => $guru->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_admin_scanner_can_scan_guru_using_nip(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        
        $guruUser = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $guruUser->id,
            'nama_lengkap' => 'Dedi Guru',
            'nip' => '198801012020011004',
            'qr_code' => 'QR-GURU-04',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Biologi',
        ]);

        // Scan menggunakan NIP, bukan qr_code
        $response = $this->actingAs($admin)->post(route('admin.scan-qr.store'), [
            'scan_type' => 'guru',
            'qr_code' => '198801012020011004',
        ]);

        $response->assertRedirect(route('admin.scan-qr.index'));
        $response->assertSessionHas('success', 'Absensi Dedi Guru berhasil dicatat.');

        $this->assertDatabaseHas('absensi_guru', [
            'guru_id' => $guru->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_admin_scanner_can_scan_staff_using_qr_code(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        
        $staffUser = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff = StaffTataUsaha::create([
            'user_id' => $staffUser->id,
            'nama_lengkap' => 'Eri Staff',
            'nip' => '198801012020011005',
            'qr_code' => 'QR-STAFF-01',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.scan-qr.store'), [
            'scan_type' => 'pegawai',
            'qr_code' => 'QR-STAFF-01',
        ]);

        $response->assertRedirect(route('admin.scan-qr.index'));
        $response->assertSessionHas('success', 'Absensi Eri Staff berhasil dicatat.');

        $this->assertDatabaseHas('absensi_staff', [
            'staff_id' => $staff->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }

    public function test_admin_scanner_can_scan_staff_using_nip(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        
        $staffUser = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff = StaffTataUsaha::create([
            'user_id' => $staffUser->id,
            'nama_lengkap' => 'Fani Staff',
            'nip' => '198801012020011006',
            'qr_code' => 'QR-STAFF-02',
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
        ]);

        // Scan menggunakan NIP, bukan qr_code
        $response = $this->actingAs($admin)->post(route('admin.scan-qr.store'), [
            'scan_type' => 'pegawai',
            'qr_code' => '198801012020011006',
        ]);

        $response->assertRedirect(route('admin.scan-qr.index'));
        $response->assertSessionHas('success', 'Absensi Fani Staff berhasil dicatat.');

        $this->assertDatabaseHas('absensi_staff', [
            'staff_id' => $staff->id,
            'tanggal' => now()->toDateString() . ' 00:00:00',
        ]);
    }
}

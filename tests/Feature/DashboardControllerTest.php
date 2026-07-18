<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test refreshStats method returns valid 200 JSON response with expected stats keys.
     */
    public function test_refresh_stats_returns_valid_json_response(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
        
        session(['tahun_akademik_id' => $tahun->id]);

        // Act
        $response = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->getJson(route('admin.dashboard.refresh-stats'));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'totalSiswa',
                'totalGuru',
                'totalStaff',
                'totalKelas',
                'totalSiswaWajibAbsen',
                'totalAbsensiHariIni',
                'totalIzinPending',
                'hadirCount',
                'sakitCount',
                'izinCount',
                'alphaCount',
                'terlambatCount',
                'belumAbsen',
                'tingkatKehadiran',
                'chartDays',
                'chartHadir',
                'chartSakit',
                'chartIzin',
                'chartAlpha',
                'absensiGuruHariIni',
                'absensiStaffHariIni',
                'palingAwal',
                'palingAkhir',
                'pengaturanArr',
                'tahunAkademikAktif',
                'kehadiranPerKelas',
                'monthlyStats',
                'metodeAbsensi',
                'recentLogs'
            ]);
    }

    /**
     * Test waliKelasData logic works correctly when user has the WALI_KELAS role.
     */
    public function test_wali_kelas_dashboard_works_without_query_exception(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);
        
        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '123456789012345678',
            'nama_lengkap' => 'Guru Wali Kelas',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'RPL',
            'jabatan' => 'Guru',
            'status' => 'aktif'
        ]);
        
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
        
        session(['tahun_akademik_id' => $tahun->id]);

        $kelas = Kelas::create([
            'wali_kelas_id' => $guru->id,
            'tahun_akademik_id' => $tahun->id,
            'nama' => 'XII RPL 1',
            'tingkat' => '12',
            'is_aktif_absensi' => true
        ]);

        $siswaUser = User::factory()->create(['role' => User::ROLE_SISWA]);
        $siswa = Siswa::create([
            'user_id' => $siswaUser->id,
            'nama_lengkap' => 'Siswa A',
            'nis' => '11111',
            'nisn' => '1111111111',
            'jenis_kelamin' => 'L',
            'status' => 'aktif',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $tahun->id,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
        ]);

        // Act
        $response = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_WALI_KELAS])
            ->get(route('wali-kelas.dashboard'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('kelas_nama', 'XII RPL 1');
        $response->assertViewHas('total_siswa', 1);
        $response->assertViewHas('has_class', true);
    }

    /**
     * Test waliKelasData logic handles guru user who is not a wali kelas.
     */
    public function test_wali_kelas_dashboard_when_guru_has_no_class(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);
        
        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '123456789012345678',
            'nama_lengkap' => 'Guru Biasa',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'RPL',
            'jabatan' => 'Guru',
            'status' => 'aktif'
        ]);
        
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
        session(['tahun_akademik_id' => $tahun->id]);

        // Act
        $response = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_WALI_KELAS])
            ->get(route('wali-kelas.dashboard'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('has_class', false);
    }
}

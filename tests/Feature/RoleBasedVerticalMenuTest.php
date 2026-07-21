<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class RoleBasedVerticalMenuTest extends TestCase
{
    use RefreshDatabase;

    private ?TahunAkademik $tahun = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Set multipurpose_mode ke 'multitenant' agar menu khusus super_admin
        // (seperti Manajemen Sekolah) tidak difilter oleh MenuServiceProvider
        config(['app.multipurpose_mode' => 'multitenant']);

        // Buat Tahun Akademik aktif untuk kebutuhan session
        $this->tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-1: Super Admin
    // ─────────────────────────────────────────────────────────────
    public function test_super_admin_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Menu admin umum harus muncul
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Kelola Presensi');
        $response->assertSee('Data Civitas');
        $response->assertSee('Kurikulum');
        $response->assertSee('Pengaturan');

        // Item khusus super_admin harus muncul
        $response->assertSee('Manajemen Sekolah');
        $response->assertSee('Update Sistem');
        $response->assertSee('Manajemen Lisensi');
        $response->assertSee('Log Sistem');
        $response->assertSee('Update Aplikasi');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-2: Admin Sekolah
    // ─────────────────────────────────────────────────────────────
    public function test_admin_sekolah_menus_appear_super_admin_menus_hidden(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN_SEKOLAH]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_ADMIN_SEKOLAH,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Menu admin umum harus muncul
        $response->assertSee('Dashboard Utama');
        $response->assertSee('Kelola Presensi');
        $response->assertSee('Cetak Kartu');

        // Item khusus super_admin TIDAK boleh muncul
        $response->assertDontSee('Manajemen Sekolah');
        $response->assertDontSee('Update Sistem');
        $response->assertDontSee('Manajemen Lisensi');
        $response->assertDontSee('Log Sistem');
        $response->assertDontSee('Update Aplikasi');
        $response->assertDontSee('Pembelian');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-3: Wali Kelas
    // ─────────────────────────────────────────────────────────────
    public function test_wali_kelas_menus_appear_guru_menus_hidden(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_WALI_KELAS,
            ])
            ->get(route('wali-kelas.dashboard'));

        $response->assertStatus(200);

        // Menu wali kelas yang harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Kelas Saya');
        $response->assertSee('Input Absensi Kelas');
        $response->assertSee('Absensi Harian Kelas');
        $response->assertSee('Data Siswa Kelas');
        $response->assertSee('Laporan Rekap');
        $response->assertSee('Belum Absen');
        $response->assertSee('Absensi Manual Cepat');

        // Menu Tugas Piket
        $response->assertSee('Tugas Piket');
        $response->assertSee('Monitor Piket');

        // Menu Guru harus TIDAK muncul
        $response->assertDontSee('Absensi Saya');
        $response->assertDontSee('Pengajuan Izin');
        $response->assertDontSee('Penugasan Siswa');
        $response->assertDontSee('Portal Guru');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-4: Guru
    // ─────────────────────────────────────────────────────────────
    public function test_guru_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_GURU,
            ])
            ->get(route('guru.dashboard'));

        $response->assertStatus(200);

        // Menu guru harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Portal Guru');
        $response->assertSee('Absensi Saya');
        $response->assertSee('Pengajuan Izin');
        $response->assertSee('Penugasan Siswa');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-5: Siswa
    // ─────────────────────────────────────────────────────────────
    public function test_siswa_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SISWA]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SISWA,
            ])
            ->get(route('siswa.dashboard'));

        $response->assertStatus(200);

        // Menu siswa harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Portal Siswa');
        $response->assertSee('Profil Saya');
        $response->assertSee('Izin & Sakit');
        $response->assertSee('Tugas Mandiri');
        $response->assertSee('Papan Peringkat');
        $response->assertSee('Kartu Pelajar');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-6: Operator
    // ─────────────────────────────────────────────────────────────
    public function test_operator_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_OPERATOR,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Menu operator harus muncul (dari vertical_operator.json)
        $response->assertSee('Dashboard');
        $response->assertSee('Asisten AI');
        $response->assertSee('Akademik');
        $response->assertSee('Tahun Ajaran');
        $response->assertSee('Master Jurusan');
        $response->assertSee('Data Kelas');
        $response->assertSee('Mata Pelajaran');
        $response->assertSee('Jadwal Pelajaran');
        $response->assertSee('Data Pengguna');
        $response->assertSee('Data Siswa');
        $response->assertSee('Data Guru');
        $response->assertSee('Data Staff TU');
        $response->assertSee('Absensi');
        $response->assertSee('Absensi Siswa');
        $response->assertSee('Absensi Guru');
        $response->assertSee('Kegiatan');
        $response->assertSee('Master Kegiatan');
        $response->assertSee('Poin Pelanggaran');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-7: Staff TU
    // ─────────────────────────────────────────────────────────────
    public function test_staff_tu_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_STAFF_TU]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_STAFF_TU,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Menu staff TU harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Staff');
        $response->assertSee('Absensi Saya');
        $response->assertSee('Izin & Sakit');
        $response->assertSee('Ajukan Izin');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-8: Orang Tua
    // ─────────────────────────────────────────────────────────────
    public function test_orang_tua_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ORANG_TUA]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_ORANG_TUA,
            ])
            ->get(route('ortu.dashboard'));

        $response->assertStatus(200);

        // Menu orang tua harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Portal Orang Tua');
        $response->assertSee('Data Anak');
        $response->assertSee('Absensi Anak');
        $response->assertSee('Izin & Sakit');
        $response->assertSee('Pengaturan & Password');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-9: Piket
    // ─────────────────────────────────────────────────────────────
    public function test_piket_menus_appear(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PIKET]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_PIKET,
            ])
            ->get(route('piket.dashboard'));

        $response->assertStatus(200);

        // Menu piket harus muncul
        $response->assertSee('Dashboard');
        $response->assertSee('Tugas Piket');
        $response->assertSee('Scanner Gerbang');
        $response->assertSee('Rekap Harian Piket');
        $response->assertSee('Poin Pelanggaran');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-10: Multi-Role Switching
    // ─────────────────────────────────────────────────────────────
    public function test_multi_role_switching_changes_menu(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'roles' => [User::ROLE_GURU],
        ]);

        // ── Login sebagai super_admin ──
        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN,
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Sekolah');
        $response->assertSee('Log Sistem');

        // ── Switch role ke guru ──
        $switchResponse = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN,
            ])
            ->post(route('role.switch'), ['role' => User::ROLE_GURU]);

        $switchResponse->assertRedirect();
        $this->assertEquals(User::ROLE_GURU, session('active_role'));

        // Verifikasi menu berganti ke guru
        $guruResponse = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_GURU,
            ])
            ->get(route('guru.dashboard'));

        $guruResponse->assertStatus(200);
        $guruResponse->assertSee('Portal Guru');
        $guruResponse->assertSee('Absensi Saya');
        $guruResponse->assertDontSee('Manajemen Sekolah');
        $guruResponse->assertDontSee('Log Sistem');

        // ── Switch role kembali ke super_admin ──
        $switchBackResponse = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_GURU,
            ])
            ->post(route('role.switch'), ['role' => User::ROLE_SUPER_ADMIN]);

        $switchBackResponse->assertRedirect();
        $this->assertEquals(User::ROLE_SUPER_ADMIN, session('active_role'));

        // Verifikasi menu kembali ke super_admin
        $adminResponse = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN,
            ])
            ->get(route('dashboard'));

        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Manajemen Sekolah');
        $adminResponse->assertSee('Log Sistem');
        $adminResponse->assertDontSee('Portal Guru');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-11: Edge Case - Role tanpa file menu (fallback)
    // ─────────────────────────────────────────────────────────────
    public function test_role_without_menu_file_falls_back_gracefully(): void
    {
        $user = User::factory()->create([
            'role' => 'unknown_role_xyz',
        ]);

        // Pastikan sistem tidak crash
        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => 'unknown_role_xyz',
            ])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        // Fallback seharusnya memuat vertical_admin.json (default)
        // Verifikasi bahwa halaman tetap merender menu (tidak error 500)
        $response->assertSee('Dashboard Utama');
    }

    // ─────────────────────────────────────────────────────────────
    //  TC-12: JSON Validity (sudah di-cover test terpisah)
    // ─────────────────────────────────────────────────────────────

    // ─────────────────────────────────────────────────────────────
    //  Test tambahan: role-based submenu filtering juga berfungsi
    // ─────────────────────────────────────────────────────────────
    public function test_role_based_items_in_submenu_are_filtered(): void
    {
        // Submenu "Data Civitas" > "Alumni" hanya untuk super_admin
        // (roles: ["super_admin"] pada item Alumni)

        // Super Admin seharusnya lihat Alumni
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $responseSA = $this->actingAs($superAdmin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN,
            ])
            ->get(route('dashboard'));
        $responseSA->assertStatus(200);
        $responseSA->assertSee('Alumni');

        // Admin Sekolah seharusnya TIDAK lihat Alumni
        $adminSekolah = User::factory()->create(['role' => User::ROLE_ADMIN_SEKOLAH]);
        $responseAS = $this->actingAs($adminSekolah)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_ADMIN_SEKOLAH,
            ])
            ->get(route('dashboard'));
        $responseAS->assertStatus(200);
        $responseAS->assertDontSee('Alumni');
    }

    public function test_wali_kelas_does_not_see_admin_menu(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_WALI_KELAS,
            ])
            ->get(route('wali-kelas.dashboard'));

        $response->assertStatus(200);

        // Menu admin harus TIDAK muncul
        $response->assertDontSee('Dashboard Utama');
        $response->assertDontSee('Data Civitas');
        $response->assertDontSee('Kurikulum');
        $response->assertDontSee('Cetak Kartu');
        $response->assertDontSee('Pengaturan');
    }

    public function test_guru_does_not_see_admin_or_wali_kelas_menus(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($user)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_GURU,
            ])
            ->get(route('guru.dashboard'));

        $response->assertStatus(200);

        // Menu wali_kelas harus TIDAK muncul
        $response->assertDontSee('Kelas Saya');
        $response->assertDontSee('Input Absensi Kelas');
        $response->assertDontSee('Tugas Piket');

        // Menu admin harus TIDAK muncul
        $response->assertDontSee('Data Civitas');
        $response->assertDontSee('Kurikulum');
    }
}

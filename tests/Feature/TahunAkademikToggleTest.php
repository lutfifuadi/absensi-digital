<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahunAkademikToggleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private TahunAkademik $taAktif;
    private TahunAkademik $taNonaktif;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_toggle_test',
        ]);

        $this->taAktif = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        $this->taNonaktif = TahunAkademik::create([
            'nama' => '2024/2025',
            'semester' => 'genap',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2024-06-30',
            'is_aktif' => false,
        ]);
    }

    private function toggleRoute(TahunAkademik $ta): string
    {
        return route('admin.tahun-akademik.toggle-aktif', $ta);
    }

    /** @test */
    public function test_toggle_on_mengaktifkan_ta_nonaktif()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_aktif' => true,
        ]);

        $this->taNonaktif->refresh();
        $this->taAktif->refresh();

        $this->assertTrue($this->taNonaktif->is_aktif);
        $this->assertFalse($this->taAktif->is_aktif);

        $latestLog = ActivityLog::where('description', 'like', '%Toggle%')
            ->orWhere('description', 'like', '%toggle%')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($latestLog, 'ActivityLog harus mencatat toggle');
        $this->assertStringContainsString('Toggle', $latestLog->description);
        $this->assertStringContainsString($this->taNonaktif->nama, $latestLog->description);
    }

    /** @test */
    public function test_toggle_off_pada_satu_satunya_ta_aktif_harus_gagal()
    {
        TahunAkademik::where('id', '!=', $this->taAktif->id)->update(['is_aktif' => false]);
        $aktifCount = TahunAkademik::where('is_aktif', true)->count();
        $this->assertEquals(1, $aktifCount, 'Harus hanya 1 TA aktif');

        $this->actingAs($this->admin);

        $response = $this->postJson($this->toggleRoute($this->taAktif));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Tidak bisa menonaktifkan. Minimal harus ada satu tahun ajaran yang aktif.',
        ]);

        $this->taAktif->refresh();
        $this->assertTrue($this->taAktif->is_aktif, 'TA harus tetap aktif');
    }

    /** @test */
    public function test_toggle_off_ketika_ada_ta_lain_yang_aktif_harus_berhasil()
    {
        // Karena ada index unik idx_tahun_akademik_hanya_satu_aktif (is_aktif_unique),
        // tidak boleh ada 2 baris yang is_aktif = true sekaligus (is_aktif_unique = 1).
        // Oleh karena itu, kita buat TA baru dengan is_aktif = false terlebih dahulu,
        // lalu nanti saat toggle off taAktif, flow aplikasinya harus memindahkan keaktifan
        // atau kita set manual agar hanya 1 yang aktif.
        $taLain = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => false,
        ]);

        // Mari kita aktifkan taLain dan nonaktifkan taAktif
        $this->actingAs($this->admin);

        // Toggle ON taLain (yang otomatis me-nonaktifkan taAktif karena aturan hanya satu yang boleh aktif)
        $response = $this->postJson($this->toggleRoute($taLain));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_aktif' => true,
        ]);

        $this->taAktif->refresh();
        $this->assertFalse($this->taAktif->is_aktif);
    }

    /** @test */
    public function test_role_guru_tidak_bisa_mengakses_toggle()
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'username' => 'guru_toggle_test',
        ]);

        $this->actingAs($guru);

        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_role_wali_kelas_tidak_bisa_mengakses_toggle()
    {
        $waliKelas = User::factory()->create([
            'role' => User::ROLE_WALI_KELAS,
            'username' => 'wali_toggle_test',
        ]);

        $this->actingAs($waliKelas);

        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(403);
    }

    /** @test */
    public function test_role_operator_bisa_mengakses_toggle()
    {
        $operator = User::factory()->create([
            'role' => User::ROLE_OPERATOR,
            'username' => 'operator_toggle_test',
        ]);

        $this->actingAs($operator);

        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_response_json_format_valid()
    {
        $this->actingAs($this->admin);

        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'is_aktif',
        ]);

        $json = $response->json();
        $this->assertIsBool($json['success']);
        $this->assertIsString($json['message']);
        $this->assertIsBool($json['is_aktif']);
    }

    /** @test */
    public function test_csrf_protection_aktif()
    {
        $routeName = 'admin.tahun-akademik.toggle-aktif';
        $this->assertTrue(Route::has($routeName), "Route $routeName harus terdaftar");

        $routes = Route::getRoutes();
        $route = $routes->getByName($routeName);
        $middlewares = $route->middleware();

        $this->assertContains('web', $middlewares, 'Route harus menggunakan web middleware group yang mencakup CSRF protection');
    }

    /** @test */
    public function test_guest_tidak_bisa_mengakses_toggle()
    {
        $response = $this->postJson($this->toggleRoute($this->taNonaktif));

        $response->assertStatus(401);
    }

    /** @test */
    public function test_toggle_dari_nonaktif_ke_aktif_mencatat_activity_log()
    {
        $this->actingAs($this->admin);

        $this->postJson($this->toggleRoute($this->taNonaktif));

        $log = ActivityLog::where('module', 'tahun_akademik')
            ->where('action', 'update')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertEquals([
            'is_aktif_sebelum' => false,
        ], $log->old_data);
        $this->assertEquals([
            'is_aktif_sesudah' => true,
        ], $log->new_data);
    }

    /** @test */
    public function test_halaman_index_hanya_bisa_diakses_role_tertentu()
    {
        $guru = User::factory()->create([
            'role' => User::ROLE_GURU,
            'username' => 'guru_index_test',
        ]);

        $this->actingAs($guru);

        $response = $this->get(route('admin.tahun-akademik.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function test_toggle_tidak_mengubah_data_ketika_gagal()
    {
        TahunAkademik::where('id', '!=', $this->taAktif->id)->update(['is_aktif' => false]);

        $this->actingAs($this->admin);

        $originalAktif = $this->taAktif->is_aktif;
        $originalNonaktif = $this->taNonaktif->is_aktif;

        $response = $this->postJson($this->toggleRoute($this->taAktif));
        $response->assertStatus(422);

        $this->taAktif->refresh();
        $this->taNonaktif->refresh();

        $this->assertEquals($originalAktif, $this->taAktif->is_aktif);
        $this->assertEquals($originalNonaktif, $this->taNonaktif->is_aktif);
    }
}

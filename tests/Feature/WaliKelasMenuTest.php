<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guru;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WaliKelasMenuTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji validitas sintaks berkas JSON menu.
     */
    public function test_vertical_admin_menu_json_is_valid_json(): void
    {
        $path = base_path('resources/menu/vertical_admin.json');
        $this->assertFileExists($path);
        
        $jsonContent = file_get_contents($path);
        $this->assertJson($jsonContent);
        
        $data = json_decode($jsonContent, true);
        $this->assertArrayHasKey('menu', $data);
    }

    /**
     * Memastikan route yang didefinisikan di menu vertical_admin.json untuk wali kelas valid.
     */
    public function test_wali_kelas_menu_routes_exist(): void
    {
        // Route-route yang dipakai di menu Wali Kelas (vertical_admin.json):
        // 1. admin.wali-kelas.index
        // 2. admin.wali-kelas.create
        // 3. admin.wali-kelas.edit
        
        $this->assertTrue(Route::has('admin.wali-kelas.index'));
        $this->assertTrue(Route::has('admin.wali-kelas.create'));
        $this->assertTrue(Route::has('admin.wali-kelas.edit'));
    }

    /**
     * Uji akses admin ke dashboard dan pastikan view admin merender menu dengan baik.
     */
    public function test_admin_can_access_dashboard_and_render_menu(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['tahun_akademik_id' => $tahun->id, 'active_role' => User::ROLE_SUPER_ADMIN])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        
        // Memastikan item menu Wali Kelas yang baru saja dimasukkan oleh Teh Ayu ter-render
        $response->assertSee('/admin/wali-kelas');
        $response->assertSee('Wali Kelas');
    }
}

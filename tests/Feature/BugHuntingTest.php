<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugHuntingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup data dasar
        TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'is_aktif' => true,
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
        ]);
    }

    /** @test */
    public function test_siswa_cannot_access_admin_dashboard()
    {
        $siswa = User::factory()->create(['role' => 'siswa']);

        $response = $this->actingAs($siswa)->get('/dashboard');
        
        $response->assertRedirect(route('siswa.dashboard'));
    }

    /** @test */
    public function test_guest_cannot_access_siswa_dashboard()
    {
        $response = $this->get('/siswa/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_innovation_api_is_accessible_without_sanctum_but_has_tenant_middleware()
    {
        // Berdasarkan api.php, innovation routes hanya pakai middleware 'tenant', bukan 'auth:sanctum'
        $response = $this->getJson('/api/v1/innovation/notification-templates'); 
        
        // Jika middleware 'tenant' tidak menangani guest, maka ini akan 200 (potensi security issue jika data sensitif)
        $response->assertStatus(200); 
    }

    /** @test */
    public function test_pelepasan_scan_requires_keyword_auth()
    {
        $response = $this->get('/pelepasan/scan/live');
        $response->assertRedirect(route('public.pelepasan.index'));
    }

    /** @test */
    public function test_pelepasan_auth_with_wrong_keyword()
    {
        $response = $this->post('/pelepasan/scan/auth', [
            'kata_kunci' => 'salah_banget'
        ]);

        $response->assertSessionHasErrors('kata_kunci');
        $this->assertFalse(session('pelepasan_public_authenticated', false));
    }

    /** @test */
    public function test_pelepasan_process_with_invalid_qr()
    {
        // Simulate auth
        session(['pelepasan_public_authenticated' => true]);

        $response = $this->postJson('/pelepasan/scan/process', [
            'qr_code' => 'KODE_PALSU_123'
        ]);

        $response->assertStatus(404)
                 ->assertJsonFragment(['success' => false, 'message' => 'Kartu siswa tidak terdaftar!']);
    }
}

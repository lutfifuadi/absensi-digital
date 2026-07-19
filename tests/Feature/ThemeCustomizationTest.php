<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ThemeCustomizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $nonAdmin;
    private array $validPayload;

    protected function setUp(): void
    {
        parent::setUp();

        // Create Admin user (role yang diizinkan: super_admin atau admin_sekolah)
        $this->admin = User::factory()->create([
            'role' => 'super_admin',
        ]);

        // Create Non-Admin user
        $this->nonAdmin = User::factory()->create([
            'role' => 'siswa',
        ]);

        // Setup valid theme customization payload
        $this->validPayload = [
            'theme_primary' => '#7367f0',
            'theme_success' => '#28c76f',
            'theme_info' => '#00cfe8',
            'theme_warning' => '#ff9f43',
            'theme_danger' => '#ea5455',
            'theme_secondary' => '#a8aaae',
            'theme_text_main' => '#cbd5e1',
            'theme_surface' => 'rgba(15, 23, 42, 0.45)',
            'theme_border' => 'rgba(255, 255, 255, 0.07)',
            'theme_hero_preset' => 'default',
        ];

        // Ensure clean cache state
        Cache::forget('das_theme_vars');
    }

    /**
     * Test 1: Admin can update theme customization.
     * Verifikasi:
     * - Data masuk ke database tabel 'pengaturan' dengan group = 'theme'
     * - Soft-colors ter-generate dengan benar (misal primary_soft)
     * - Cache 'das_theme_vars' dibersihkan (kembalian Cache::get nilainya null / request selanjutnya akan merefresh)
     */
    public function test_admin_can_update_theme_customization(): void
    {
        // Set fake cache to verify it is cleared
        Cache::put('das_theme_vars', ['old' => 'value']);

        $response = $this->actingAs($this->admin)->postJson(route('admin.pengaturan.tema.update'), $this->validPayload);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Kustomisasi warna tema UI berhasil disimpan.'
        ]);

        // Verifikasi database has original data with group 'theme'
        $this->assertDatabaseHas('pengaturan', [
            'key' => 'theme_primary',
            'value' => '#7367f0',
            'group' => 'theme',
        ]);

        $this->assertDatabaseHas('pengaturan', [
            'key' => 'theme_hero_preset',
            'value' => 'default',
            'group' => 'theme',
        ]);

        // Verifikasi soft-colors ter-generate dengan benar
        // #7367f0 = RGB (115, 103, 240) -> soft color = rgba(115, 103, 240, 0.12)
        $this->assertDatabaseHas('pengaturan', [
            'key' => 'theme_primary_soft',
            'value' => 'rgba(115, 103, 240, 0.12)',
            'group' => 'theme',
        ]);

        // Verifikasi cache 'das_theme_vars' dibersihkan
        $this->assertNull(Cache::get('das_theme_vars'));
    }

    /**
     * Test 2: Non-Admin is rejected when trying to change theme.
     */
    public function test_non_admin_cannot_update_theme_customization(): void
    {
        // Case 2a: authenticated non-admin user
        $response1 = $this->actingAs($this->nonAdmin)->postJson(route('admin.pengaturan.tema.update'), $this->validPayload);
        $response1->assertStatus(403);

        // Case 2b: unauthenticated user
        $response2 = $this->postJson(route('admin.pengaturan.tema.update'), $this->validPayload);
        // Middleware RoleMiddleware returns 403 when user is null, or gets caught/redirected by auth/sanctum/role.
        // Let's accept 403 or 401 or 302/redirect
        $response2->assertStatus(403);
    }

    /**
     * Test 3: Admin can reset theme customization.
     * Verifikasi:
     * - Data di database terhapus (key 'theme_*' terhapus)
     * - Cache dibersihkan
     */
    public function test_admin_can_reset_theme_customization(): void
    {
        // Seed some theme configs in database
        Pengaturan::create(['key' => 'theme_primary', 'value' => '#7367f0', 'group' => 'theme']);
        Pengaturan::create(['key' => 'theme_primary_soft', 'value' => 'rgba(115, 103, 240, 0.12)', 'group' => 'theme']);
        
        Cache::put('das_theme_vars', ['theme_primary' => '#7367f0']);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.pengaturan.tema.reset'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Tema UI berhasil direset ke default.'
        ]);

        // Verifikasi data terhapus di database
        $this->assertDatabaseMissing('pengaturan', [
            'key' => 'theme_primary',
        ]);
        $this->assertDatabaseMissing('pengaturan', [
            'key' => 'theme_primary_soft',
        ]);

        // Verifikasi cache 'das_theme_vars' dibersihkan
        $this->assertNull(Cache::get('das_theme_vars'));
    }

    /**
     * Test 4: Validation rejects invalid color formats.
     */
    public function test_validation_rejects_invalid_color_formats_or_presets(): void
    {
        // invalid hex format
        $invalidPayload1 = $this->validPayload;
        $invalidPayload1['theme_primary'] = 'invalid_color';

        $response1 = $this->actingAs($this->admin)->postJson(route('admin.pengaturan.tema.update'), $invalidPayload1);
        $response1->assertStatus(422);
        $response1->assertJsonValidationErrors(['theme_primary']);

        // invalid preset
        $invalidPayload2 = $this->validPayload;
        $invalidPayload2['theme_hero_preset'] = 'invalid_preset';

        $response2 = $this->actingAs($this->admin)->postJson(route('admin.pengaturan.tema.update'), $invalidPayload2);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['theme_hero_preset']);

        // invalid rgba format for surface
        $invalidPayload3 = $this->validPayload;
        $invalidPayload3['theme_surface'] = 'rgba(256, 0, 0, 2)'; // invalid rgba value or format

        $response3 = $this->actingAs($this->admin)->postJson(route('admin.pengaturan.tema.update'), $invalidPayload3);
        $response3->assertStatus(422);
        $response3->assertJsonValidationErrors(['theme_surface']);
    }
}

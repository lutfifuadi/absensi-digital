<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ClearCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_clear_cache()
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.pengaturan.clear-cache'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cache aplikasi berhasil dibersihkan!'
        ]);
    }

    public function test_non_super_admin_cannot_clear_cache()
    {
        // Coba dengan role admin_sekolah
        $adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH
        ]);

        $response = $this->actingAs($adminSekolah)
            ->post(route('admin.pengaturan.clear-cache'));

        $response->assertStatus(403);
    }
}

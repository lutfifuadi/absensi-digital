<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasImpersonateTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminSekolah;
    protected User $waliKelas;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat Super Admin
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Buat Admin Sekolah
        $this->adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
        ]);

        // Buat Wali Kelas
        $this->waliKelas = User::factory()->create([
            'role' => User::ROLE_WALI_KELAS,
        ]);
    }

    /** @test */
    public function super_admin_can_login_as_wali_kelas_via_post_and_revert_via_get()
    {
        // 1. Super Admin start impersonate wali kelas
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.impersonate.login-as', $this->waliKelas));

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('impersonator_id', $this->superAdmin->id);
        $response->assertSessionHas('active_role', User::ROLE_WALI_KELAS);

        // Pastikan user yang login berubah menjadi wali kelas
        $this->assertEquals($this->waliKelas->id, auth()->guard('web')->id());

        // 2. Revert back to Super Admin
        $revertResponse = $this->get(route('admin.impersonate.revert'));
        $revertResponse->assertRedirect(route('admin.users.index'));
        $revertResponse->assertSessionMissing('impersonator_id');
        $revertResponse->assertSessionHas('active_role', User::ROLE_SUPER_ADMIN);

        // Pastikan user yang login kembali menjadi Super Admin
        $this->assertEquals($this->superAdmin->id, auth()->guard('web')->id());
    }

    /** @test */
    public function admin_sekolah_cannot_login_as_wali_kelas()
    {
        // Karena route admin.impersonate.login-as dibatasi hanya untuk Super Admin di ImpersonateController
        $response = $this->actingAs($this->adminSekolah)
            ->post(route('admin.impersonate.login-as', $this->waliKelas));

        $response->assertStatus(403);
    }
}

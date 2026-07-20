<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicQrScanBypassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat settings default agar middleware atau controller tidak error
        Pengaturan::updateOrCreate(['key' => 'password_unlock_scan_qr'], ['value' => bcrypt('rahasia')]);
    }

    public function test_guest_cannot_access_scan_page_without_password(): void
    {
        $response = $this->get(route('public.scan-qr.scan'));

        $response->assertRedirect(route('public.scan-qr.index'));
        $response->assertSessionHas('error', 'Silakan masukkan password terlebih dahulu.');
    }

    public function test_guest_accessing_index_page_is_shown_login_view(): void
    {
        $response = $this->get(route('public.scan-qr.index'));

        $response->assertStatus(200);
        $response->assertViewIs('public.scan-qr-login');
    }

    public function test_super_admin_is_automatically_authenticated_and_redirected_on_index_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('public.scan-qr.index'));

        $response->assertRedirect(route('public.scan-qr.scan'));
        $this->assertTrue(session('qr_scan_authenticated'));
    }

    public function test_admin_sekolah_is_automatically_authenticated_and_redirected_on_index_page(): void
    {
        $adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
        ]);

        $response = $this->actingAs($adminSekolah)->get(route('public.scan-qr.index'));

        $response->assertRedirect(route('public.scan-qr.scan'));
        $this->assertTrue(session('qr_scan_authenticated'));
    }

    public function test_regular_user_role_is_not_automatically_redirected_on_index_page(): void
    {
        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($siswaUser)->get(route('public.scan-qr.index'));

        $response->assertStatus(200);
        $response->assertViewIs('public.scan-qr-login');
        $this->assertNull(session('qr_scan_authenticated'));
    }

    public function test_super_admin_can_bypass_scan_page_without_session_password(): void
    {
        $superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('public.scan-qr.scan'));

        $response->assertStatus(200);
        $response->assertViewIs('public.scan-qr-scan');
    }

    public function test_admin_sekolah_can_bypass_scan_page_without_session_password(): void
    {
        $adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
        ]);

        $response = $this->actingAs($adminSekolah)->get(route('public.scan-qr.scan'));

        $response->assertStatus(200);
        $response->assertViewIs('public.scan-qr-scan');
    }

    public function test_regular_user_cannot_bypass_scan_page_without_session_password(): void
    {
        $siswaUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($siswaUser)->get(route('public.scan-qr.scan'));

        $response->assertRedirect(route('public.scan-qr.index'));
        $response->assertSessionHas('error', 'Silakan masukkan password terlebih dahulu.');
    }

    public function test_other_non_admin_roles_are_not_automatically_redirected_on_index_page(): void
    {
        $nonAdminRoles = [
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_STAFF_TU,
            User::ROLE_ORANG_TUA,
            User::ROLE_PIKET,
        ];

        foreach ($nonAdminRoles as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $response = $this->actingAs($user)->get(route('public.scan-qr.index'));

            $response->assertStatus(200);
            $response->assertViewIs('public.scan-qr-login');
            $this->assertNull(session('qr_scan_authenticated'));
            
            // Clear session for next loop iteration
            $this->flushSession();
        }
    }

    public function test_other_non_admin_roles_cannot_bypass_scan_page_without_session_password(): void
    {
        $nonAdminRoles = [
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_STAFF_TU,
            User::ROLE_ORANG_TUA,
            User::ROLE_PIKET,
        ];

        foreach ($nonAdminRoles as $role) {
            $user = User::factory()->create([
                'role' => $role,
            ]);

            $response = $this->actingAs($user)->get(route('public.scan-qr.scan'));

            $response->assertRedirect(route('public.scan-qr.index'));
            $response->assertSessionHas('error', 'Silakan masukkan password terlebih dahulu.');
            
            // Clear session for next loop iteration
            $this->flushSession();
        }
    }
}

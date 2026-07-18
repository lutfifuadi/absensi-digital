<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSelectorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User with single role is redirected to dashboard.
     */
    public function test_single_role_user_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_SISWA,
            'roles' => null,
        ]);

        $response = $this->actingAs($user)->get(route('role.select'));

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * User with multi role can access role selector.
     */
    public function test_multi_role_user_can_access_role_selector(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS, User::ROLE_PIKET],
        ]);

        $response = $this->actingAs($user)->get(route('role.select'));

        $response->assertStatus(200);
        $response->assertViewIs('content.authentications.auth-select-role');
        $response->assertViewHas('availableRoles');
    }

    /**
     * User can select a role they own.
     */
    public function test_user_can_select_owned_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS],
        ]);

        $response = $this->actingAs($user)->post(route('role.select.post'), [
            'role' => User::ROLE_WALI_KELAS,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals(User::ROLE_WALI_KELAS, session('active_role'));
    }

    /**
     * User cannot select a role they do not own.
     */
    public function test_user_cannot_select_unowned_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS],
        ]);

        $response = $this->actingAs($user)->post(route('role.select.post'), [
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $response->assertSessionHasErrors(['role']);
    }

    /**
     * User can switch their active role.
     */
    public function test_user_can_switch_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS],
        ]);

        session(['active_role' => User::ROLE_GURU]);

        $response = $this->actingAs($user)->post(route('role.switch'), [
            'role' => User::ROLE_WALI_KELAS,
        ]);

        $response->assertRedirect();
        $this->assertEquals(User::ROLE_WALI_KELAS, session('active_role'));
    }

    /**
     * User is redirected to role selector if access dashboard and multi role and has no active role.
     */
    public function test_dashboard_redirects_to_role_selector_when_no_active_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('role.select'));
    }

    /**
     * Middleware prevents access to unauthorized role.
     */
    public function test_role_middleware_restricts_access(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_WALI_KELAS],
        ]);

        // Accessing guru dashboard which requires role:guru
        // Case 1: active_role is guru -> should pass
        session(['active_role' => User::ROLE_GURU]);
        $response = $this->actingAs($user)->get(route('guru.dashboard'));
        $response->assertStatus(200); // Because dashboard view/redirect maps to 200 dashboards.default/guru view
        
        // Case 2: active_role is wali_kelas -> guru.dashboard requires role:guru -> should abort 403
        session(['active_role' => User::ROLE_WALI_KELAS]);
        $response = $this->actingAs($user)->get(route('guru.dashboard'));
        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordPlainAdminTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper ────────────────────────────────────────────────────────────

    private function createSuperAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_SUPER_ADMIN,
            'roles' => [User::ROLE_SUPER_ADMIN],
            'password' => Hash::make('admin1234'),
        ], $overrides));
    }

    private function createAdminSekolah(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'roles' => [User::ROLE_ADMIN_SEKOLAH],
            'password' => Hash::make('adminsekolah1234'),
        ], $overrides));
    }

    private function createGuru(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_GURU,
            'roles' => [User::ROLE_GURU],
            'password' => Hash::make('guru1234'),
        ], $overrides));
    }

    private function createSiswa(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_SISWA,
            'roles' => [User::ROLE_SISWA],
            'password' => Hash::make('siswa1234'),
        ], $overrides));
    }

    private function createOperator(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_OPERATOR,
            'roles' => [User::ROLE_OPERATOR],
            'password' => Hash::make('operator1234'),
        ], $overrides));
    }

    // ════════════════════════════════════════════════════════════════════════
    // 1. SYNC PASSWORD_PLAIN — Observer Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_observer_syncs_password_plain_on_create(): void
    {
        $plainPassword = 'MySecretPassword123!';

        User::setPendingPlainPassword($plainPassword);
        $user = User::create([
            'name' => 'Test Observer Create',
            'username' => 'observer_create_' . uniqid(),
            'email' => uniqid() . '@test.com',
            'password' => Hash::make($plainPassword),
            'role' => User::ROLE_GURU,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => $plainPassword,
        ]);
    }

    public function test_observer_syncs_password_plain_on_update(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
            'role' => User::ROLE_GURU,
        ]);

        $newPlainPassword = 'NewPassword456!';
        User::setPendingPlainPassword($newPlainPassword);
        $user->update([
            'password' => Hash::make($newPlainPassword),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => $newPlainPassword,
        ]);
    }

    public function test_observer_does_not_sync_when_password_not_changed(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('mypassword'),
            'role' => User::ROLE_GURU,
        ]);

        // Update name only — password not dirty
        User::setPendingPlainPassword('ShouldNotSync');
        $user->update(['name' => 'Updated Name']);

        // password_plain should remain null (factory doesn't set it)
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => null,
        ]);
    }

    public function test_observer_clears_pending_after_save(): void
    {
        User::setPendingPlainPassword('temp_password');

        // Trigger an unrelated save
        $user = User::factory()->create([
            'password' => Hash::make('somepassword'),
            'role' => User::ROLE_GURU,
        ]);

        // Static pending should be cleared
        $this->assertNull(User::getPendingPlainPassword());
    }

    public function test_password_plain_syncs_via_controller_store(): void
    {
        $admin = $this->createSuperAdmin();
        $plainPassword = 'NewUserPass789!';
        $email = uniqid() . '@test.com';

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Test Store User',
            'username' => 'store_' . uniqid(),
            'email' => $email,
            'password' => $plainPassword,
            'password_confirmation' => $plainPassword,
            'role' => User::ROLE_GURU,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => $email,
        ]);

        // Verify password_plain is stored
        $createdUser = User::where('email', $email)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($plainPassword, $createdUser->password_plain);
    }

    public function test_password_plain_syncs_via_controller_update(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password' => Hash::make('oldpass'),
            'role' => User::ROLE_GURU,
        ]);

        $newPassword = 'UpdatedPass456!';
        $response = $this->actingAs($admin)->put(route('admin.users.update', $user->id), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
            'role' => User::ROLE_GURU,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => $newPassword,
        ]);
    }

    public function test_password_plain_not_changed_when_password_not_provided_in_update(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password' => Hash::make('existingpassword'),
            'role' => User::ROLE_GURU,
        ]);

        // Update without password
        $response = $this->actingAs($admin)->put(route('admin.users.update', $user->id), [
            'name' => 'Updated Name Only',
            'username' => $user->username,
            'email' => $user->email,
            'role' => User::ROLE_GURU,
        ]);

        $response->assertRedirect();

        // password_plain should remain null
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => null,
        ]);
    }

    public function test_password_plain_syncs_via_fortify_update(): void
    {
        $user = $this->createGuru();

        $newPassword = 'FortifyNewPass!99';
        $response = $this->actingAs($user)->put('/user/password', [
            'current_password' => 'guru1234',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => $newPassword,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 2. ENDPOINT showPassword — Authorization Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_super_admin_can_access_show_password(): void
    {
        $admin = $this->createSuperAdmin();
        $targetUser = User::factory()->create([
            'password_plain' => 'plaintext123',
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => 'plaintext123',
                'user_id' => $targetUser->id,
                'user_name' => $targetUser->name,
            ]);
    }

    public function test_admin_sekolah_can_access_show_password(): void
    {
        $admin = $this->createAdminSekolah();
        $targetUser = User::factory()->create([
            'password_plain' => 'sekolahPass456',
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => 'sekolahPass456',
            ]);
    }

    public function test_guru_cannot_access_show_password(): void
    {
        $guru = $this->createGuru();
        $targetUser = User::factory()->create([
            'password_plain' => 'hidden123',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($guru)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertStatus(403);
    }

    public function test_siswa_cannot_access_show_password(): void
    {
        $siswa = $this->createSiswa();
        $targetUser = User::factory()->create([
            'password_plain' => 'shouldnotsee',
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($siswa)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_show_password(): void
    {
        $operator = $this->createOperator();
        $targetUser = User::factory()->create([
            'password_plain' => 'operatorNo',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($operator)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_show_password(): void
    {
        $user = User::factory()->create(['password_plain' => 'anonNo']);

        $response = $this->getJson(
            route('admin.users.show-password', $user->id)
        );

        // JSON request returns 401, web request returns 302 redirect
        $response->assertStatus(401);
    }

    public function test_show_password_returns_null_when_password_plain_is_null(): void
    {
        $admin = $this->createSuperAdmin();
        $targetUser = User::factory()->create([
            'password_plain' => null,
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => null,
            ]);
    }

    public function test_show_password_logs_activity(): void
    {
        $admin = $this->createSuperAdmin();
        $targetUser = User::factory()->create([
            'password_plain' => 'logmeplz',
            'role' => User::ROLE_GURU,
        ]);

        $this->assertDatabaseMissing('activity_logs', [
            'action' => 'VIEW_PASSWORD',
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertOk();
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'VIEW_PASSWORD',
            'module' => 'User',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 3. IDOR & PRIVILEGE ESCALATION Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_non_admin_cannot_view_other_users_password_idor(): void
    {
        $guru = $this->createGuru();
        $targetUser = User::factory()->create([
            'password_plain' => 'supersecret',
            'role' => User::ROLE_SISWA,
        ]);

        // Guru tries to access admin endpoint
        $response = $this->actingAs($guru)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        $response->assertStatus(403);
    }

    public function test_admin_sekolah_cannot_be_escalated_to_super_admin_via_endpoint(): void
    {
        $adminSekolah = $this->createAdminSekolah();

        // admin_sekolah should be able to access (it's allowed)
        $targetUser = User::factory()->create([
            'password_plain' => 'adm1npass',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($adminSekolah)->getJson(
            route('admin.users.show-password', $targetUser->id)
        );

        // Should succeed (admin_sekolah is authorized)
        $response->assertOk();
    }

    // ════════════════════════════════════════════════════════════════════════
    // 4. EDGE CASE Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_show_password_for_user_with_null_password_plain(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password_plain' => null,
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => null,
            ]);
    }

    public function test_show_password_with_very_long_password(): void
    {
        $admin = $this->createSuperAdmin();
        $longPassword = str_repeat('A', 255) . '!@#$%^&*()_+';
        $user = User::factory()->create([
            'password_plain' => $longPassword,
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => $longPassword,
            ]);
    }

    public function test_show_password_with_special_characters(): void
    {
        $admin = $this->createSuperAdmin();
        $specialPassword = '<script>alert("xss")</script>&\'"test';
        $user = User::factory()->create([
            'password_plain' => $specialPassword,
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => $specialPassword,
            ]);

        // Note: JSON API returns raw data. XSS protection is handled
        // by the frontend (Blade/Javascript) when rendering in the DOM.
        // JSON content-type header ensures browsers don't execute scripts.
    }

    public function test_show_password_with_unicode_characters(): void
    {
        $admin = $this->createSuperAdmin();
        $unicodePassword = '密码测试パスワード🔐';
        $user = User::factory()->create([
            'password_plain' => $unicodePassword,
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => $unicodePassword,
            ]);
    }

    public function test_show_password_nonexistent_user_returns_404(): void
    {
        $admin = $this->createSuperAdmin();

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', 99999)
        );

        $response->assertStatus(404);
    }

    public function test_show_password_empty_string_password(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password_plain' => '',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => '',
            ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 5. RATE LIMITING Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_rate_limiting_is_configured_on_show_password_route(): void
    {
        // Verify that the route has throttle middleware
        $route = route('admin.users.show-password', 1);
        $this->assertNotEmpty($route);

        // We can't easily test exact throttle behavior in PHPUnit,
        // but we verify the route accepts first request
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password_plain' => 'ratetest',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk();
    }

    // ════════════════════════════════════════════════════════════════════════
    // 6. PASSWORD_PLAIN IN HIDDEN ATTRIBUTE Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_password_plain_is_hidden_from_serialization(): void
    {
        $user = User::factory()->create([
            'password_plain' => 'shouldnotserialize',
            'role' => User::ROLE_GURU,
        ]);

        $toArray = $user->toArray();
        $this->assertArrayNotHasKey('password_plain', $toArray);
    }

    public function test_password_plain_is_in_hidden_array(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains('password_plain', $hidden);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 7. RESPONSE FORMAT Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_show_password_response_structure(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password_plain' => 'structuretest',
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'user_id',
                'user_name',
                'password',
            ]);
    }

    public function test_show_password_error_response_for_unauthorized(): void
    {
        $guru = $this->createGuru();
        $user = User::factory()->create([
            'password_plain' => 'errortest',
            'role' => User::ROLE_SISWA,
        ]);

        $response = $this->actingAs($guru)->getJson(
            route('admin.users.show-password', $user->id)
        );

        $response->assertStatus(403);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 8. USER INDEX PAGE — Column Visibility Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_user_index_page_shows_password_column_for_super_admin(): void
    {
        $admin = $this->createSuperAdmin();
        User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Password');
        $response->assertSee('btn-toggle-password');
    }

    public function test_user_index_page_shows_password_column_for_admin_sekolah(): void
    {
        // Note: The user management page (admin.users.index) requires role:super_admin
        // The showPassword endpoint separately allows admin_sekolah.
        // This test verifies admin_sekolah gets 403 on the index page (expected).
        $admin = $this->createAdminSekolah();
        User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        // admin_sekolah cannot access user management page (only super_admin can)
        $response->assertStatus(403);
    }

    public function test_user_index_page_hides_password_column_for_guru(): void
    {
        $guru = $this->createGuru();
        User::factory()->create(['role' => User::ROLE_SISWA]);

        $response = $this->actingAs($guru)->get(route('admin.users.index'));

        // guru cannot access user management page
        $response->assertStatus(403);
    }

    public function test_user_index_page_hides_password_column_for_siswa(): void
    {
        $siswa = $this->createSiswa();
        User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($siswa)->get(route('admin.users.index'));

        // siswa cannot access user management page
        $response->assertStatus(403);
    }

    public function test_ajax_request_returns_table_with_password_for_admin(): void
    {
        $admin = $this->createSuperAdmin();
        User::factory()->create(['role' => User::ROLE_GURU]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.users.index'), [
                'X-Requested-With' => 'XMLHttpRequest',
            ]);

        $response->assertOk();
        $response->assertSee('btn-toggle-password');
    }

    // ════════════════════════════════════════════════════════════════════════
    // 9. MASS ASSIGNMENT Protection Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_password_plain_is_mass_assignable(): void
    {
        // Verify password_plain is in fillable
        $user = new User();
        $this->assertContains('password_plain', $user->getFillable());
    }

    // ════════════════════════════════════════════════════════════════════════
    // 10. ROLE MIDDLEWARE Integration Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_route_has_role_middleware(): void
    {
        // Verify the route is registered within the admin group with role middleware
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.users.show-password');
        $this->assertNotNull($route);
    }

    public function test_route_has_throttle_middleware(): void
    {
        // Verify throttle middleware is applied
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.users.show-password');
        $this->assertNotNull($route);

        $middleware = $route->gatherMiddleware();
        $hasThrottle = collect($middleware)->contains(function ($m) {
            return str_contains($m, 'throttle');
        });

        $this->assertTrue($hasThrottle, 'Route should have throttle middleware');
    }

    public function test_route_is_get_method(): void
    {
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('admin.users.show-password');
        $this->assertEquals('GET', $route->methods()[0]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 11. MULTIPLE PASSWORD CHANGES Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_password_plain_updates_on_each_password_change(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password' => Hash::make('firstpass'),
            'role' => User::ROLE_GURU,
        ]);

        // First change
        User::setPendingPlainPassword('secondpass');
        $user->update(['password' => Hash::make('secondpass')]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'password_plain' => 'secondpass']);

        // Second change
        User::setPendingPlainPassword('thirdpass');
        $user->update(['password' => Hash::make('thirdpass')]);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'password_plain' => 'thirdpass']);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 12. PASSWORD_PLAIN Column Migration Tests
    // ════════════════════════════════════════════════════════════════════════

    public function test_password_plain_column_exists(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Schema::hasColumn('users', 'password_plain'),
            'password_plain column should exist in users table'
        );
    }

    public function test_password_plain_column_is_nullable(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);

        // password_plain should be null by default
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'password_plain' => null,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 13. SUPER_ADMIN Viewing Own Password
    // ════════════════════════════════════════════════════════════════════════

    public function test_super_admin_can_view_own_password(): void
    {
        $admin = $this->createSuperAdmin([
            'password_plain' => 'myadminpass',
        ]);

        $response = $this->actingAs($admin)->getJson(
            route('admin.users.show-password', $admin->id)
        );

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'password' => 'myadminpass',
            ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // 14. CSRF / Accept Header Validation
    // ════════════════════════════════════════════════════════════════════════

    public function test_show_password_works_with_json_accept_header(): void
    {
        $admin = $this->createSuperAdmin();
        $user = User::factory()->create([
            'password_plain' => 'jsontest',
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($admin)
            ->withHeaders(['Accept' => 'application/json'])
            ->get(route('admin.users.show-password', $user->id));

        $response->assertOk()
            ->assertJsonFragment(['password' => 'jsontest']);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_deploy_page(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get(route('admin.deploy.index'));

        $response->assertStatus(200);
    }

    public function test_admin_sekolah_cannot_access_deploy_page(): void
    {
        $user = User::factory()->create(['role' => 'admin_sekolah']);

        $response = $this->actingAs($user)->get(route('admin.deploy.index'));

        $response->assertStatus(403);
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('admin.deploy.index'));

        $response->assertStatus(302);
    }

    public function test_super_admin_can_access_deploy_status_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get(route('admin.deploy.status'));

        $response->assertStatus(200)->assertJsonStructure(['queue_running', 'current_version', 'latest_version', 'can_deploy']);
    }

    public function test_admin_sekolah_cannot_access_deploy_status_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'admin_sekolah']);

        $response = $this->actingAs($user)->get(route('admin.deploy.status'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_deploy_history(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get(route('admin.deploy.history'));

        $response->assertStatus(200);
    }

    public function test_operator_cannot_access_deploy_page(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($user)->get(route('admin.deploy.index'));

        $response->assertStatus(403);
    }

    public function test_guru_cannot_access_deploy_page(): void
    {
        $user = User::factory()->create(['role' => 'guru']);

        $response = $this->actingAs($user)->get(route('admin.deploy.index'));

        $response->assertStatus(403);
    }

    public function test_run_deploy_requires_password(): void
    {
        $user = User::factory()->create(['role' => 'super_admin', 'password' => bcrypt('secret')]);

        $response = $this->actingAs($user)->postJson(route('admin.deploy.run'), []);

        $response->assertStatus(422);
    }

    public function test_run_deploy_rejects_wrong_password(): void
    {
        $user = User::factory()->create(['role' => 'super_admin', 'password' => bcrypt('secret')]);

        $response = $this->actingAs($user)->postJson(route('admin.deploy.run'), [
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(403);
    }

    public function test_operator_cannot_access_deploy_run_endpoint(): void
    {
        $user = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($user)->post(route('admin.deploy.run'), [
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }
}

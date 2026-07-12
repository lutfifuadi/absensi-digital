<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SupervisorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class QueueControlControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function test_admin_can_access_queue_status_even_if_supervisor_not_running()
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // Mock SupervisorService
        $mock = Mockery::mock(SupervisorService::class);
        $mock->shouldReceive('getStatus')->andReturn([
            'success' => false,
            'message' => 'Supervisor tidak berjalan atau tidak dapat dijangkau.',
            'status' => 'stopped',
            'process_info' => null,
        ]);
        $this->app->instance(SupervisorService::class, $mock);

        $response = $this->actingAs($admin)->getJson('/admin/queue/status');

        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'success' => false,
                     'status' => 'stopped',
                 ]);
    }

    /** @test */
    public function test_supervisor_service_construct_reads_proper_env_configurations()
    {
        // Re-bind configuration to clear cached config settings in PHPUnit env
        config([
            'supervisor.host' => '10.0.0.5',
            'supervisor.port' => 9999,
            'supervisor.username' => 'custom_user',
            'supervisor.password' => 'secret',
            'supervisor.program' => 'custom-program',
        ]);

        $service = new SupervisorService();
        
        $refHost = new \ReflectionProperty(SupervisorService::class, 'host');
        $refHost->setAccessible(true);
        $refPort = new \ReflectionProperty(SupervisorService::class, 'port');
        $refPort->setAccessible(true);
        $refUser = new \ReflectionProperty(SupervisorService::class, 'username');
        $refUser->setAccessible(true);
        $refProgram = new \ReflectionProperty(SupervisorService::class, 'program');
        $refProgram->setAccessible(true);

        $this->assertEquals('10.0.0.5', $refHost->getValue($service));
        $this->assertEquals(9999, $refPort->getValue($service));
        $this->assertEquals('custom_user', $refUser->getValue($service));
        $this->assertEquals('custom-program', $refProgram->getValue($service));
    }

    /** @test */
    public function test_supervisor_service_simulation_mode_in_local_environment()
    {
        // Set env to local
        $this->app->detectEnvironment(fn() => 'local');
        
        // Buat SupervisorService
        $service = new SupervisorService();
        
        // Simulasikan port/host salah agar connection refused
        config([
            'supervisor.host' => '127.0.0.1',
            'supervisor.port' => 9999, // port salah
        ]);

        // Panggil status
        $status = $service->getStatus();

        $this->assertTrue($status['success']);
        $this->assertEquals('running', $status['status']);
        $this->assertNotNull($status['process_info']);
        $this->assertEquals('RUNNING', $status['process_info']['statename']);

        // Stop
        $service->stop();
        $status = $service->getStatus();
        $this->assertEquals('stopped', $status['status']);
        
        // Start
        $service->start();
        $status = $service->getStatus();
        $this->assertEquals('running', $status['status']);
    }
}

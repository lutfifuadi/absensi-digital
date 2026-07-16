<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanLiveBoardCounterColorTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN
        ]);
    }

    public function test_super_admin_can_access_branding_tab_with_counter_color()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.pengaturan.index', ['tab' => 'branding']));

        $response->assertStatus(200);
        $response->assertSee('live_board_counter_color');
    }

    public function test_super_admin_can_save_counter_color_setting()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.pengaturan.update'), [
                'live_board_counter_color' => '#ff5733',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        
        $this->assertEquals('#ff5733', Pengaturan::where('key', 'live_board_counter_color')->value('value'));
    }

    public function test_counter_color_reflected_in_live_board_view()
    {
        Pengaturan::updateOrCreate(
            ['key' => 'live_board_counter_color'],
            ['value' => '#abcdef', 'group' => 'umum']
        );

        $response = $this->get(route('public.live-board'));
        $response->assertStatus(200);
        $response->assertSee('#abcdef');
    }
}

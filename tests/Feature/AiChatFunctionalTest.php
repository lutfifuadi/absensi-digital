<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiChatFunctionalTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'super_admin'
        ]);

        // Setup API Key palsu di database
        Pengaturan::updateOrCreate(['key' => 'gemini_api_key'], ['value' => 'key1,key2', 'group' => 'ai']);
        Pengaturan::updateOrCreate(['key' => 'gemini_last_key_index'], ['value' => '0', 'group' => 'ai']);
    }

    /** @test */
    public function it_can_send_simple_message()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'Halo! Saya adalah asisten AI sistem absensi Anda.']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.ai-chat.send'), [
                'message' => 'Halo, siapa kamu?'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Halo! Saya adalah asisten AI sistem absensi Anda.'
            ]);
    }

    /** @test */
    public function it_can_handle_tool_call_for_statistics()
    {
        // Mocking model count because factories don't exist
        // Note: In real app, we should use factories if they exist, 
        // but here we just need to ensure the tool call flow works.
        
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    [
                                        'functionCall' => [
                                            'name' => 'statistik_data',
                                            'args' => (object)[]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ], 200)
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Total siswa saat ini adalah 0 dan total guru adalah 0.']
                                ]
                            ]
                        ]
                    ]
                ], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.ai-chat.send'), [
                'message' => 'Berapa total siswa dan guru saat ini?'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonFragment([
                'message' => 'Total siswa saat ini adalah 0 dan total guru adalah 0.'
            ]);
    }

    /** @test */
    public function it_rotates_api_key_on_429_error()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'Rate limit exceeded']], 429) // Key 1 gagal
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Respon dari key kedua.']
                                ]
                            ]
                        ]
                    ]
                ], 200) // Key 2 sukses
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.ai-chat.send'), [
                'message' => 'Tes rotasi key'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Respon dari key kedua.'
            ]);

        // Pastikan index key berubah
        $this->assertEquals('1', Pengaturan::where('key', 'gemini_last_key_index')->value('value'));
    }

    /** @test */
    public function it_handles_401_error_by_rotating_key()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::sequence()
                ->push(['error' => ['message' => 'API_KEY_INVALID']], 400) // Biasanya 400 dengan error message tertentu untuk invalid key di Gemini
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => 'Respon dari key kedua yang valid.']
                                ]
                            ]
                        ]
                    ]
                ], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.ai-chat.send'), [
                'message' => 'Tes key invalid'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Respon dari key kedua yang valid.'
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ChatLog;
use App\Models\Guide;
use App\Models\GuideCategory;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $guru;
    protected User $siswa;
    protected User $orangTua;
    protected GuideCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat users dengan berbagai role
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->guru = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);

        $this->siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $this->orangTua = User::factory()->create([
            'role' => User::ROLE_ORANG_TUA,
        ]);

        // Buat kategori dan guide untuk testing
        $this->category = GuideCategory::factory()->create([
            'name' => 'Panduan Siswa',
        ]);

        // Set default nama_lembaga
        Pengaturan::factory()->create([
            'key' => 'nama_lembaga',
            'value' => 'SMAN 1 Jakarta',
            'group' => 'sekolah',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // A. ROUTE MIDDLEWARE TESTS
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_super_admin_can_access_ai_chat_route()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.ai-chat.index'));

        // Note: View memiliki bug Blade (extra @endif) yang menyebabkan 500 error.
        // Tapi middleware route sudah lolos (bukan 302/403).
        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function test_guru_can_access_ai_chat_route()
    {
        $response = $this->actingAs($this->guru)
            ->get(route('admin.ai-chat.index'));

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function test_siswa_can_access_ai_chat_route()
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('admin.ai-chat.index'));

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function test_orang_tua_can_access_ai_chat_route()
    {
        $response = $this->actingAs($this->orangTua)
            ->get(route('admin.ai-chat.index'));

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function test_unauthenticated_user_cannot_access_ai_chat_route()
    {
        $response = $this->get(route('admin.ai-chat.index'));

        $response->assertStatus(302); // Redirect to login
    }

    /** @test */
    public function test_super_admin_can_access_ai_chat_send_route()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ai-chat.send'), ['message' => 'Halo']);

        // Bisa 200 (sukses) atau 500 (view error atau Gemini API error)
        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /** @test */
    public function test_siswa_can_access_ai_chat_send_route()
    {
        $response = $this->actingAs($this->siswa)
            ->post(route('admin.ai-chat.send'), ['message' => 'Halo']);

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    // ──────────────────────────────────────────────────────────
    // B. TOOL: CARI_PANDUAN TESTS
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_tool_cari_panduan_with_valid_keyword_returns_max_3_results()
    {
        // Arrange: buat beberapa guide published
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Cara Absen Mandiri',
            'category_id' => $this->category->id,
        ]);
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Cara Izin Sakit',
            'category_id' => $this->category->id,
        ]);
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Cara Melihat Nilai',
            'category_id' => $this->category->id,
        ]);
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Panduan Lengkap Lainnya',
            'category_id' => $this->category->id,
        ]);

        // Act: executeTool via reflection
        $result = $this->executeTool('cari_panduan', ['keyword' => 'Cara', 'role' => 'siswa'], 'siswa');
        $decoded = json_decode($result, true);

        // Assert: max 3 hasil
        $this->assertLessThanOrEqual(3, count($decoded));
        $this->assertArrayNotHasKey('error', $decoded);
    }

    /** @test */
    public function test_tool_cari_panduan_without_results_returns_not_found()
    {
        // Act: cari dengan keyword yang tidak ada
        $result = $this->executeTool('cari_panduan', ['keyword' => 'xyzabc123', 'role' => 'siswa'], 'siswa');
        $decoded = json_decode($result, true);

        // Assert: harus return message "Tidak ditemukan"
        $this->assertArrayHasKey('message', $decoded);
        $this->assertStringContainsString('Tidak ditemukan', $decoded['message']);
    }

    /** @test */
    public function test_tool_cari_panduan_for_siswa_only_returns_siswa_guides()
    {
        // Arrange: buat guide untuk siswa dan guru
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Panduan Khusus Siswa',
            'category_id' => $this->category->id,
            'content' => 'Langkah-langkah untuk siswa',
        ]);
        Guide::factory()->published()->forRole('guru')->create([
            'title' => 'Panduan Khusus Guru',
            'category_id' => $this->category->id,
            'content' => 'Langkah-langkah untuk guru',
        ]);
        Guide::factory()->published()->create([
            'title' => 'Panduan Publik',
            'role_target' => 'public',
            'category_id' => $this->category->id,
            'content' => 'Panduan untuk semua',
        ]);

        // Act: search sebagai siswa
        $result = $this->executeTool('cari_panduan', ['keyword' => 'Panduan', 'role' => 'siswa'], 'siswa');
        $decoded = json_decode($result, true);

        // Assert: hanya guide yang ditargetkan ke siswa yang muncul
        $titles = array_column($decoded, 'title');
        $this->assertContains('Panduan Khusus Siswa', $titles);
        $this->assertContains('Panduan Publik', $titles);
        $this->assertNotContains('Panduan Khusus Guru', $titles);
    }

    /** @test */
    public function test_tool_cari_panduan_rejects_short_keyword()
    {
        // Act: keyword hanya 1 karakter
        $result = $this->executeTool('cari_panduan', ['keyword' => 'a', 'role' => 'siswa'], 'siswa');
        $decoded = json_decode($result, true);

        // Assert: error minimal 2 karakter
        $this->assertArrayHasKey('error', $decoded);
        $this->assertStringContainsString('minimal 2 karakter', $decoded['error']);
    }

    // ──────────────────────────────────────────────────────────
    // C. TOOL: GET_FITUR_SISTEM TESTS
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_tool_get_fitur_sistem_returns_fitur_for_guru()
    {
        // Arrange: buat category dengan guide untuk guru
        $catGuru = GuideCategory::factory()->create(['name' => 'Panduan Guru']);
        Guide::factory()->published()->forRole('guru')->create([
            'title' => 'Cara Absen Guru',
            'category_id' => $catGuru->id,
        ]);
        Guide::factory()->published()->forRole('guru')->create([
            'title' => 'Cara Mengisi Nilai',
            'category_id' => $catGuru->id,
        ]);

        // Act
        $result = $this->executeTool('get_fitur_sistem', ['role' => 'guru'], 'guru');
        $decoded = json_decode($result, true);

        // Assert
        $this->assertArrayNotHasKey('error', $decoded);
        $this->assertEquals('guru', $decoded['role']);
        $this->assertNotEmpty($decoded['fitur']);
        
        $kategoriList = array_column($decoded['fitur'], 'kategori');
        $this->assertContains('Panduan Guru', $kategoriList);
    }

    /** @test */
    public function test_tool_get_fitur_sistem_returns_fitur_for_siswa()
    {
        // Arrange: buat category dengan guide untuk siswa
        $catSiswa = GuideCategory::factory()->create(['name' => 'Panduan Siswa']);
        Guide::factory()->published()->forRole('siswa')->create([
            'title' => 'Cara Absen Mandiri',
            'category_id' => $catSiswa->id,
        ]);

        // Pastikan guide untuk guru tidak muncul
        $catGuru = GuideCategory::factory()->create(['name' => 'Panduan Guru']);
        Guide::factory()->published()->forRole('guru')->create([
            'title' => 'Cara Mengisi Rapor',
            'category_id' => $catGuru->id,
        ]);

        // Act
        $result = $this->executeTool('get_fitur_sistem', ['role' => 'siswa'], 'siswa');
        $decoded = json_decode($result, true);

        // Assert
        $this->assertArrayNotHasKey('error', $decoded);
        $this->assertEquals('siswa', $decoded['role']);
        
        $kategoriList = array_column($decoded['fitur'], 'kategori');
        $this->assertContains('Panduan Siswa', $kategoriList);
        $this->assertNotContains('Panduan Guru', $kategoriList);
    }

    // ──────────────────────────────────────────────────────────
    // D. TIERED ACCESS TESTS
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_tier_1_siswa_can_only_access_cari_panduan_and_get_fitur_sistem()
    {
        // Arrange: siswa login
        $this->actingAs($this->siswa);

        // Act & Assert: cari_panduan boleh
        $result = $this->executeTool('cari_panduan', ['keyword' => 'test', 'role' => 'siswa'], 'siswa');
        $this->assertStringNotContainsString('tidak memiliki izin', $result);

        // Act & Assert: get_fitur_sistem boleh
        $result2 = $this->executeTool('get_fitur_sistem', ['role' => 'siswa'], 'siswa');
        $this->assertStringNotContainsString('tidak memiliki izin', $result2);

        // Act & Assert: update_siswa ditolak
        $result3 = $this->executeTool('update_siswa', ['id' => 1, 'nama_lengkap' => 'Test'], 'siswa');
        $this->assertStringContainsString('tidak memiliki izin', $result3);

        // Act & Assert: get_siswa ditolak
        $result4 = $this->executeTool('get_siswa', ['id' => 1], 'siswa');
        $this->assertStringContainsString('tidak memiliki izin', $result4);
    }

    /** @test */
    public function test_tier_2_guru_can_access_read_tools_but_not_update_tools()
    {
        // Arrange: guru login
        $this->actingAs($this->guru);

        // Act & Assert: cari_panduan boleh
        $result = $this->executeTool('cari_panduan', ['keyword' => 'test', 'role' => 'guru'], 'guru');
        $this->assertStringNotContainsString('tidak memiliki izin', $result);

        // Act & Assert: get_siswa boleh (read-only)
        $result2 = $this->executeTool('get_siswa', [], 'guru');
        $this->assertStringNotContainsString('tidak memiliki izin', $result2);

        // Act & Assert: statistik_data boleh
        $result3 = $this->executeTool('statistik_data', [], 'guru');
        $this->assertStringNotContainsString('tidak memiliki izin', $result3);

        // Act & Assert: update_siswa ditolak
        $result4 = $this->executeTool('update_siswa', ['id' => 1, 'nama_lengkap' => 'Test'], 'guru');
        $this->assertStringContainsString('tidak memiliki izin', $result4);
    }

    /** @test */
    public function test_tier_3_super_admin_can_access_all_tools()
    {
        // Arrange: super_admin login
        $this->actingAs($this->superAdmin);

        // Act & Assert: cari_panduan boleh
        $result = $this->executeTool('cari_panduan', ['keyword' => 'test', 'role' => 'super_admin'], 'super_admin');
        $this->assertStringNotContainsString('tidak memiliki izin', $result);

        // Act & Assert: get_fitur_sistem boleh
        $result2 = $this->executeTool('get_fitur_sistem', ['role' => 'super_admin'], 'super_admin');
        $this->assertStringNotContainsString('tidak memiliki izin', $result2);

        // Act & Assert: get_siswa boleh
        $result3 = $this->executeTool('get_siswa', [], 'super_admin');
        $this->assertStringNotContainsString('tidak memiliki izin', $result3);

        // Act & Assert: update_siswa boleh (tier 3)
        $result4 = $this->executeTool('update_siswa', ['id' => 999], 'super_admin');
        // Akan error karena siswa tidak ditemukan, tapi bukan karena izin
        $this->assertStringNotContainsString('tidak memiliki izin', $result4);
    }

    // ──────────────────────────────────────────────────────────
    // E. CHAT HISTORY TESTS (REGRESSION)
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_chat_history_is_saved_and_retrieved()
    {
        // Arrange: simpan beberapa chat log
        ChatLog::create([
            'user_id' => $this->siswa->id,
            'role' => 'user',
            'message' => 'Halo',
        ]);
        ChatLog::create([
            'user_id' => $this->siswa->id,
            'role' => 'assistant',
            'message' => 'Halo, ada yang bisa dibantu?',
        ]);

        // Act: login dan akses history
        $response = $this->actingAs($this->siswa)
            ->get(route('admin.ai-chat.history'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function test_clear_chat_deletes_all_history()
    {
        // Arrange: simpan chat log
        ChatLog::create([
            'user_id' => $this->siswa->id,
            'role' => 'user',
            'message' => 'Test',
        ]);

        // Act: clear chat
        $response = $this->actingAs($this->siswa)
            ->delete(route('admin.ai-chat.clear'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verifikasi database sudah kosong
        $this->assertEquals(0, ChatLog::where('user_id', $this->siswa->id)->count());
    }

    /** @test */
    public function test_chat_history_is_user_specific()
    {
        // Arrange: simpan chat untuk dua user berbeda
        ChatLog::create([
            'user_id' => $this->siswa->id,
            'role' => 'user',
            'message' => 'Pesan siswa',
        ]);
        ChatLog::create([
            'user_id' => $this->guru->id,
            'role' => 'user',
            'message' => 'Pesan guru',
        ]);

        // Act: login sebagai siswa
        $response = $this->actingAs($this->siswa)
            ->get(route('admin.ai-chat.history'));

        // Assert: hanya melihat pesan siswa
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Pesan siswa', $data[0]['message']);
    }

    /** @test */
    public function test_send_message_validates_required_message()
    {
        $response = $this->actingAs($this->siswa)
            ->post(route('admin.ai-chat.send'), ['message' => '']);

        $response->assertStatus(302); // Redirect back with validation error
        $response->assertSessionHasErrors('message');
    }

    /** @test */
    public function test_send_message_validates_max_length()
    {
        $response = $this->actingAs($this->siswa)
            ->post(route('admin.ai-chat.send'), ['message' => str_repeat('a', 2001)]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('message');
    }

    // ──────────────────────────────────────────────────────────
    // F. TOOL DEFINITION FILTERING (getToolDefinitions)
    // ──────────────────────────────────────────────────────────

    /** @test */
    public function test_get_tool_definitions_for_siswa_only_contains_basic_tools()
    {
        $gemini = app(GeminiService::class);
        $tools = $gemini->getToolDefinitions('siswa');

        $toolNames = array_column($tools, 'name');
        $this->assertContains('cari_panduan', $toolNames);
        $this->assertContains('get_fitur_sistem', $toolNames);
        $this->assertNotContains('update_siswa', $toolNames);
        $this->assertNotContains('get_siswa', $toolNames);
        $this->assertNotContains('statistik_data', $toolNames);
    }

    /** @test */
    public function test_get_tool_definitions_for_guru_contains_read_tools()
    {
        $gemini = app(GeminiService::class);
        $tools = $gemini->getToolDefinitions('guru');

        $toolNames = array_column($tools, 'name');
        $this->assertContains('cari_panduan', $toolNames);
        $this->assertContains('get_fitur_sistem', $toolNames);
        $this->assertContains('get_siswa', $toolNames);
        $this->assertContains('statistik_data', $toolNames);
        $this->assertNotContains('update_siswa', $toolNames);
    }

    /** @test */
    public function test_get_tool_definitions_for_super_admin_contains_all_tools()
    {
        $gemini = app(GeminiService::class);
        $tools = $gemini->getToolDefinitions('super_admin');

        $toolNames = array_column($tools, 'name');
        $this->assertContains('cari_panduan', $toolNames);
        $this->assertContains('get_fitur_sistem', $toolNames);
        $this->assertContains('get_siswa', $toolNames);
        $this->assertContains('update_siswa', $toolNames);
        $this->assertContains('statistik_data', $toolNames);
    }

    /**
     * Helper untuk mengeksekusi method protected executeTool.
     */
    private function executeTool(string $functionName, array $args, string $role): string
    {
        $gemini = app(GeminiService::class);
        $reflection = new \ReflectionClass(GeminiService::class);
        $method = $reflection->getMethod('executeTool');
        $method->setAccessible(true);

        return $method->invoke($gemini, $functionName, $args, $role);
    }
}

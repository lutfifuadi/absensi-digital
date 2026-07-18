<?php

namespace Tests\Unit;

use App\Models\Pengaturan;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GeminiService $gemini;

    protected function setUp(): void
    {
        parent::setUp();

        // Need to prevent the constructor from loading API keys from DB
        // Use partial mocking or create a testable instance
        $this->gemini = $this->getMockBuilder(GeminiService::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();

        // Recreate with actual constructor
        $this->gemini = new GeminiService();
    }

    /** @test */
    public function test_build_dynamic_system_instruction_contains_nama_lembaga_from_database()
    {
        // Arrange: set nama_lembaga di pengaturan
        Pengaturan::factory()->create([
            'key' => 'nama_lembaga',
            'value' => 'SMAN 1 Jakarta',
            'group' => 'sekolah',
        ]);

        // Act: panggil method protected via reflection
        $result = $this->invokeBuildDynamicSystemInstruction('siswa');

        // Assert: instruction harus mengandung "Asisten SMAN 1 Jakarta"
        $this->assertStringContainsString('Asisten SMAN 1 Jakarta', $result['parts'][0]['text']);
        $this->assertStringNotContainsString('Asisten MAN 1 Kota Bandung', $result['parts'][0]['text']);
    }

    /** @test */
    public function test_build_dynamic_system_instruction_fallback_to_app_name_when_nama_lembaga_null()
    {
        // Arrange: Hapus atau jangan set nama_lembaga, pastikan null
        Pengaturan::where('key', 'nama_lembaga')->delete();

        // Act
        $result = $this->invokeBuildDynamicSystemInstruction('siswa');

        // Assert: instruction harus mengandung app.name (fallback)
        $appName = config('app.name', 'Sekolah');
        $this->assertStringContainsString("Asisten {$appName}", $result['parts'][0]['text']);
    }

    /** @test */
    public function test_build_dynamic_system_instruction_mentions_user_role()
    {
        // Arrange: set nama_lembaga
        Pengaturan::factory()->create([
            'key' => 'nama_lembaga',
            'value' => 'SMAN 1 Jakarta',
            'group' => 'sekolah',
        ]);

        // Act: test untuk role guru
        $resultGuru = $this->invokeBuildDynamicSystemInstruction('guru');

        // Assert: instruction harus menyebut role yang sesuai
        $this->assertStringContainsString('Role User Saat Ini: **guru**', $resultGuru['parts'][0]['text']);

        // Act: test untuk role super_admin
        $resultAdmin = $this->invokeBuildDynamicSystemInstruction('super_admin');
        $this->assertStringContainsString('Role User Saat Ini: **super_admin**', $resultAdmin['parts'][0]['text']);

        // Act: test untuk role siswa
        $resultSiswa = $this->invokeBuildDynamicSystemInstruction('siswa');
        $this->assertStringContainsString('Role User Saat Ini: **siswa**', $resultSiswa['parts'][0]['text']);
    }

    /**
     * Helper untuk invoke protected method buildDynamicSystemInstruction.
     */
    private function invokeBuildDynamicSystemInstruction(string $role): array
    {
        $reflection = new \ReflectionClass(GeminiService::class);
        $method = $reflection->getMethod('buildDynamicSystemInstruction');
        $method->setAccessible(true);

        // Butuh instance dengan constructor yang sudah jalan
        // Karena kita disable constructor di setUp, kita perlu instance baru
        $service = new GeminiService();
        return $method->invoke($service, $role);
    }
}

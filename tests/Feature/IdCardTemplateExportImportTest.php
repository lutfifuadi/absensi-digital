<?php

namespace Tests\Feature;

use App\Models\IdCardTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IdCardTemplateExportImportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'username' => 'admin_test',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
        
        Storage::fake('public');
    }

    public function test_admin_can_export_template_without_background()
    {
        $template = IdCardTemplate::create([
            'name' => 'Template Siswa Keren',
            'type' => 'siswa',
            'config' => ['canvas' => ['width' => 150, 'height' => 240, 'border_radius' => 5], 'elements' => []],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.id-card-templates.export', $template->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
        
        $content = json_decode($response->streamedContent(), true);
        $this->assertEquals('Template Siswa Keren', $content['name']);
        $this->assertEquals('siswa', $content['type']);
        $this->assertEquals(5, $content['config']['canvas']['border_radius']);
        $this->assertArrayNotHasKey('background_base64', $content);
    }

    public function test_admin_can_export_template_with_local_background()
    {
        Storage::disk('public')->put('backgrounds/bg_test.jpg', 'fake_image_content');

        $template = IdCardTemplate::create([
            'name' => 'Template Guru Modern',
            'type' => 'guru',
            'background_path' => 'backgrounds/bg_test.jpg',
            'config' => ['canvas' => ['width' => 150, 'height' => 240, 'border_radius' => 5], 'elements' => []],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.id-card-templates.export', $template->id));

        $response->assertStatus(200);
        
        $content = json_decode($response->streamedContent(), true);
        $this->assertEquals('Template Guru Modern', $content['name']);
        $this->assertEquals(base64_encode('fake_image_content'), $content['background_base64']);
        $this->assertEquals('image/jpeg', $content['background_mime']);
    }

    public function test_admin_can_import_template_without_background()
    {
        $payload = [
            'name' => 'Template Siswa Baru',
            'type' => 'siswa',
            'config' => ['canvas' => ['width' => 150, 'height' => 240, 'border_radius' => 4], 'elements' => []],
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
        file_put_contents($tempFile, json_encode($payload));

        $uploadedFile = new UploadedFile(
            $tempFile,
            'template.json',
            'application/json',
            null,
            true
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.id-card-templates.import'), [
                'template_file' => $uploadedFile,
                'is_active' => '1',
            ]);

        @unlink($tempFile);

        $response->assertRedirect(route('admin.id-card-templates.index'));
        $this->assertDatabaseHas('id_card_templates', [
            'name' => 'Template Siswa Baru',
            'type' => 'siswa',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_import_template_with_base64_background()
    {
        $imageContent = 'fake_image_bytes';
        $payload = [
            'name' => 'Template Staff Baru',
            'type' => 'staff',
            'config' => ['canvas' => ['width' => 150, 'height' => 240, 'border_radius' => 3], 'elements' => []],
            'background_base64' => base64_encode($imageContent),
            'background_mime' => 'image/png',
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'import_test');
        file_put_contents($tempFile, json_encode($payload));

        $uploadedFile = new UploadedFile(
            $tempFile,
            'template.json',
            'application/json',
            null,
            true
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.id-card-templates.import'), [
                'template_file' => $uploadedFile,
            ]);

        @unlink($tempFile);

        $response->assertRedirect(route('admin.id-card-templates.index'));
        
        $template = IdCardTemplate::where('name', 'Template Staff Baru')->first();
        $this->assertNotNull($template);
        $this->assertNotNull($template->background_path);
        $this->assertTrue(Storage::disk('public')->exists($template->background_path));
        $this->assertEquals($imageContent, Storage::disk('public')->get($template->background_path));
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\IdCardTemplate;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class IdCardTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected $mockGoogleDriveService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->mockGoogleDriveService = Mockery::mock(GoogleDriveService::class);
        $this->app->instance(GoogleDriveService::class, $this->mockGoogleDriveService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_template_has_new_fields_in_default_config(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $response = $this->actingAs($user)->get(route('admin.id-card-templates.create'));

        $response->assertStatus(200);
        $response->assertSee('gender');
        $response->assertSee('ttl');
        $response->assertSee('masa_berlaku');
    }

    public function test_edit_template_merges_missing_config_keys(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        // Old template format without gender, ttl, and masa_berlaku
        $template = IdCardTemplate::create([
            'name' => 'Old Template',
            'type' => 'siswa',
            'config' => [
                'canvas' => ['width' => 153, 'height' => 243],
                'elements' => [
                    'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                    'qr' => ['x' => 49, 'y' => 165, 'w' => 55, 'h' => 55, 'show' => true],
                    'name' => ['x' => 0, 'y' => 20, 'size' => 10, 'color' => '#000000', 'show' => true, 'align' => 'center'],
                    'nis' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                    'nisn' => ['x' => 0, 'y' => 40, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                    'nip' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                    'class' => ['x' => 0, 'y' => 152, 'size' => 8, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                ]
            ],
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('admin.id-card-templates.edit', $template->id));

        $response->assertStatus(200);
        $response->assertSee('gender');
        $response->assertSee('ttl');
        $response->assertSee('masa_berlaku');
    }

    public function test_store_template_uploads_background_to_google_drive(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $file = UploadedFile::fake()->image('bg.jpg');

        $this->mockGoogleDriveService->shouldReceive('uploadPhoto')
            ->once()
            ->with(Mockery::on(function ($uploadedFile) use ($file) {
                return $uploadedFile->getClientOriginalName() === $file->getClientOriginalName();
            }))
            ->andReturn('google_drive_background_file_id_1234567890');

        $data = [
            'name' => 'Test Template GD',
            'type' => 'siswa',
            'background' => $file,
            'config' => json_encode([
                'canvas' => ['width' => 153, 'height' => 243],
                'elements' => [
                    'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                ]
            ]),
        ];

        $response = $this->actingAs($user)->post(route('admin.id-card-templates.store'), $data);

        $response->assertRedirect(route('admin.id-card-templates.index'));
        $this->assertDatabaseHas('id_card_templates', [
            'name' => 'Test Template GD',
            'background_path' => 'google_drive_background_file_id_1234567890',
        ]);
    }

    public function test_update_template_uploads_new_background_and_deletes_old_from_google_drive(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $template = IdCardTemplate::create([
            'name' => 'Old Template GD',
            'type' => 'siswa',
            'background_path' => 'old_google_drive_background_file_id_whose_length_is_more_than_thirty_characters_123',
            'config' => [
                'canvas' => ['width' => 153, 'height' => 243],
                'elements' => [
                    'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                ]
            ],
            'is_active' => true,
        ]);

        $file = UploadedFile::fake()->image('bg_new.jpg');

        $this->mockGoogleDriveService->shouldReceive('uploadPhoto')
            ->once()
            ->with(
                Mockery::on(function ($uploadedFile) use ($file) {
                    return $uploadedFile->getClientOriginalName() === $file->getClientOriginalName();
                }),
                'old_google_drive_background_file_id_whose_length_is_more_than_thirty_characters_123'
            )
            ->andReturn('new_google_drive_background_file_id_abc');

        $data = [
            'name' => 'Updated Template GD',
            'type' => 'siswa',
            'background' => $file,
            'config' => json_encode([
                'canvas' => ['width' => 153, 'height' => 243],
                'elements' => [
                    'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                ]
            ]),
        ];

        $response = $this->actingAs($user)->put(route('admin.id-card-templates.update', $template->id), $data);

        $response->assertRedirect(route('admin.id-card-templates.index'));
        $this->assertDatabaseHas('id_card_templates', [
            'id' => $template->id,
            'name' => 'Updated Template GD',
            'background_path' => 'new_google_drive_background_file_id_abc',
        ]);
    }

    public function test_delete_template_deletes_background_from_google_drive(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $template = IdCardTemplate::create([
            'name' => 'Template to Delete GD',
            'type' => 'siswa',
            'background_path' => 'delete_google_drive_background_file_id_whose_length_is_more_than_thirty_characters_123',
            'config' => [
                'canvas' => ['width' => 153, 'height' => 243],
                'elements' => [
                    'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                ]
            ],
            'is_active' => true,
        ]);

        $this->mockGoogleDriveService->shouldReceive('deletePhoto')
            ->once()
            ->with('delete_google_drive_background_file_id_whose_length_is_more_than_thirty_characters_123')
            ->andReturn(true);

        $response = $this->actingAs($user)->delete(route('admin.id-card-templates.destroy', $template->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('id_card_templates', [
            'id' => $template->id,
        ]);
    }
}

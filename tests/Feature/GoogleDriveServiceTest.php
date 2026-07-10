<?php

namespace Tests\Feature;

use App\Services\GoogleDriveService;
use App\Models\GoogleDriveSetting;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GoogleDriveServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup default mock database configurations for the service
        GoogleDriveSetting::create([
            'google_client_id' => 'mock_client_id',
            'google_client_secret' => 'mock_client_secret',
            'google_redirect_uri' => 'mock_redirect_uri',
            'google_root_folder_id' => 'mock_folder_id',
            'google_access_token' => json_encode([
                'access_token' => 'mock_access_token',
                'created' => time(),
                'expires_in' => 3600
            ]),
            'google_refresh_token' => 'mock_refresh_token',
            'is_connected' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_photo_url()
    {
        $service = new GoogleDriveService();
        $this->assertEquals(
            'https://drive.google.com/thumbnail?id=12345&sz=w800',
            $service->getPhotoUrl('12345')
        );
    }

    public function test_get_photo_base64_cached()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('gd_photo_base64_12345', Mockery::any(), Mockery::any())
            ->andReturn('data:image/jpeg;base64,encoded_data');

        $service = new GoogleDriveService();
        $result = $service->getPhotoBase64('12345');

        $this->assertEquals('data:image/jpeg;base64,encoded_data', $result);
    }

    public function test_oauth_flow_initialization()
    {
        $service = new GoogleDriveService();
        
        /** @var \Google\Client $client */
        $client = $service->getClient();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertEquals('mock_client_id', $client->getClientId());
        $this->assertEquals('mock_client_secret', $client->getClientSecret());
    }
}

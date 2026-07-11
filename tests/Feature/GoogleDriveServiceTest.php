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

    public function test_upload_photo_create_new_file()
    {
        $mockFiles = Mockery::mock(\Google\Service\Drive\Resource\Files::class);
        $mockPermissions = Mockery::mock(\Google\Service\Drive\Resource\Permissions::class);
        $mockDrive = Mockery::mock(\Google\Service\Drive::class);

        $mockDrive->files = $mockFiles;
        $mockDrive->permissions = $mockPermissions;

        $uploadedFileMock = new \Google\Service\Drive\DriveFile([
            'id' => 'new_file_123'
        ]);

        $mockFiles->shouldReceive('create')
            ->once()
            ->with(Mockery::type(\Google\Service\Drive\DriveFile::class), Mockery::on(function ($options) {
                return isset($options['data']);
            }))
            ->andReturn($uploadedFileMock);

        $mockPermissions->shouldReceive('create')
            ->once()
            ->with('new_file_123', Mockery::type(\Google\Service\Drive\Permission::class), Mockery::any())
            ->andReturn(new \Google\Service\Drive\Permission());

        $service = new GoogleDriveService();
        
        $refDrive = new \ReflectionProperty(GoogleDriveService::class, 'driveService');
        $refDrive->setAccessible(true);
        $refDrive->setValue($service, $mockDrive);

        $refEnabled = new \ReflectionProperty(GoogleDriveService::class, 'isEnabled');
        $refEnabled->setAccessible(true);
        $refEnabled->setValue($service, true);

        // Buat file temporary lokal untuk test
        $tempFile = tempnam(sys_get_temp_dir(), 'test_img');
        file_put_contents($tempFile, 'fake jpeg content');

        $resultId = $service->uploadPhoto($tempFile);

        $this->assertEquals('new_file_123', $resultId);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_upload_photo_update_existing_file()
    {
        $mockFiles = Mockery::mock(\Google\Service\Drive\Resource\Files::class);
        $mockPermissions = Mockery::mock(\Google\Service\Drive\Resource\Permissions::class);
        $mockDrive = Mockery::mock(\Google\Service\Drive::class);

        $mockDrive->files = $mockFiles;
        $mockDrive->permissions = $mockPermissions;

        $updatedFileMock = new \Google\Service\Drive\DriveFile([
            'id' => 'existing_file_999'
        ]);

        $mockFiles->shouldReceive('update')
            ->once()
            ->with('existing_file_999', Mockery::type(\Google\Service\Drive\DriveFile::class), Mockery::on(function ($options) {
                return isset($options['data']);
            }))
            ->andReturn($updatedFileMock);

        $mockPermissions->shouldReceive('create')
            ->once()
            ->with('existing_file_999', Mockery::type(\Google\Service\Drive\Permission::class), Mockery::any())
            ->andReturn(new \Google\Service\Drive\Permission());

        // Tidak boleh memanggil create atau delete karena berhasil update in-place
        $mockFiles->shouldNotReceive('create');
        $mockFiles->shouldNotReceive('delete');

        $service = new GoogleDriveService();
        
        $refDrive = new \ReflectionProperty(GoogleDriveService::class, 'driveService');
        $refDrive->setAccessible(true);
        $refDrive->setValue($service, $mockDrive);

        $refEnabled = new \ReflectionProperty(GoogleDriveService::class, 'isEnabled');
        $refEnabled->setAccessible(true);
        $refEnabled->setValue($service, true);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_img');
        file_put_contents($tempFile, 'fake jpeg content');

        $resultId = $service->uploadPhoto($tempFile, 'existing_file_999');

        $this->assertEquals('existing_file_999', $resultId);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    public function test_upload_photo_update_fails_falls_back_to_create()
    {
        $mockFiles = Mockery::mock(\Google\Service\Drive\Resource\Files::class);
        $mockPermissions = Mockery::mock(\Google\Service\Drive\Resource\Permissions::class);
        $mockDrive = Mockery::mock(\Google\Service\Drive::class);

        $mockDrive->files = $mockFiles;
        $mockDrive->permissions = $mockPermissions;

        // Mock update melempar exception
        $mockFiles->shouldReceive('update')
            ->once()
            ->with('bad_existing_file_777', Mockery::any(), Mockery::any())
            ->andThrow(new \Exception('File not found in Google Drive'));

        // Mock create untuk fallback
        $uploadedFileMock = new \Google\Service\Drive\DriveFile([
            'id' => 'fallback_new_file_888'
        ]);

        $mockFiles->shouldReceive('create')
            ->once()
            ->with(Mockery::type(\Google\Service\Drive\DriveFile::class), Mockery::any())
            ->andReturn($uploadedFileMock);

        // Mock permissions create untuk fallback_new_file_888
        $mockPermissions->shouldReceive('create')
            ->once()
            ->with('fallback_new_file_888', Mockery::type(\Google\Service\Drive\Permission::class), Mockery::any())
            ->andReturn(new \Google\Service\Drive\Permission());

        // Mock delete file lama yang bermasalah (dipanggil saat update gagal & fallback sukses)
        $mockFiles->shouldReceive('delete')
            ->once()
            ->with('bad_existing_file_777', Mockery::any())
            ->andReturn(true);

        $service = new GoogleDriveService();
        
        $refDrive = new \ReflectionProperty(GoogleDriveService::class, 'driveService');
        $refDrive->setAccessible(true);
        $refDrive->setValue($service, $mockDrive);

        $refEnabled = new \ReflectionProperty(GoogleDriveService::class, 'isEnabled');
        $refEnabled->setAccessible(true);
        $refEnabled->setValue($service, true);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_img');
        file_put_contents($tempFile, 'fake jpeg content');

        $resultId = $service->uploadPhoto($tempFile, 'bad_existing_file_777');

        $this->assertEquals('fallback_new_file_888', $resultId);

        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}

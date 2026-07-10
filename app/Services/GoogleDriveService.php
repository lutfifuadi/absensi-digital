<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\GoogleDriveSetting;
use App\Services\GoogleDriveConfigService;

class GoogleDriveService
{
    private ?Client $client = null;
    private ?Drive $driveService = null;
    private ?string $folderId = null;
    private bool $isEnabled = true;

    public function __construct()
    {
        try {
            if (!Schema::hasTable('google_drive_settings')) {
                $this->isEnabled = false;
                return;
            }

            $config = GoogleDriveConfigService::getConfig();

            if (empty($config['client_id']) || empty($config['client_secret'])) {
                $this->isEnabled = false;
                return;
            }

            $this->folderId = $config['root_folder_id'];

            $this->client = new Client();
            $this->client->setClientId($config['client_id']);
            $this->client->setClientSecret($config['client_secret']);
            $this->client->setRedirectUri($config['redirect_uri']);
            $this->client->addScope(Drive::DRIVE);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');

            $cacert = 'D:\Project\xampp\php\extras\ssl\cacert.pem';
            if (file_exists($cacert)) {
                $guzzleClient = new \GuzzleHttp\Client(['verify' => $cacert]);
                $this->client->setHttpClient($guzzleClient);
            }

            if ($config['access_token']) {
                $token = json_decode($config['access_token'], true);
                $this->client->setAccessToken($token);

                if ($this->client->isAccessTokenExpired()) {
                    if ($config['refresh_token']) {
                        $this->client->fetchAccessTokenWithRefreshToken($config['refresh_token']);
                        $newToken = $this->client->getAccessToken();
                        
                        $setting = GoogleDriveSetting::first();
                        if ($setting) {
                            $setting->update([
                                'google_access_token' => json_encode($newToken)
                            ]);
                        }
                    } else {
                        $this->isEnabled = false;
                        Log::warning('GoogleDriveService: Access token is expired and no refresh token is available.');
                    }
                }
            } else {
                $this->isEnabled = false;
            }

            if ($this->isEnabled) {
                $this->driveService = new Drive($this->client);
            }
        } catch (\Exception $e) {
            $this->isEnabled = false;
            Log::warning('GoogleDriveService constructor failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the Google Client instance.
     */
    public function getClient(): ?Client
    {
        if (!Schema::hasTable('pengaturan')) {
            $this->isEnabled = false;
            throw new \RuntimeException("Google Drive Service is not enabled (missing pengaturan table).");
        }

        return $this->client;
    }

    /**
     * Get the Google Drive Service instance.
     */
    public function getDriveService(): ?Drive
    {
        return $this->driveService;
    }

    /**
     * Upload photo to Google Drive, setting permission to public view, and deleting old file if provided.
     *
     * @param mixed $file Illuminate\Http\UploadedFile or file path string
     * @param string|null $oldFileId Old file ID to delete
     * @return string|null New file ID or null on failure
     */
    public function uploadPhoto($file, ?string $oldFileId = null): ?string
    {
        if (!$this->isEnabled || !$this->driveService) {
            Log::warning('GoogleDriveService: uploadPhoto called but service is not enabled.');
            return null;
        }

        try {
            $driveService = $this->driveService;

            $filePath = '';
            $originalName = 'photo.jpg';
            $mimeType = 'image/jpeg';

            if ($file instanceof UploadedFile) {
                $filePath = $file->getRealPath();
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
            } elseif (is_string($file) && file_exists($file)) {
                $filePath = $file;
                $originalName = basename($file);
                $mimeType = mime_content_type($file) ?: 'image/jpeg';
            } else {
                throw new \InvalidArgumentException('Invalid file provided for upload.');
            }

            // Resize if needed
            $tempFileCreated = false;
            $uploadPath = $this->resizeImageIfNeeded($filePath, $mimeType, $tempFileCreated);

            // Google Drive File Metadata
            $fileMetadata = new \Google\Service\Drive\DriveFile([
                'name' => $originalName,
            ]);

            if ($this->folderId) {
                $fileMetadata->setParents([$this->folderId]);
            }

            // Read content
            $content = file_get_contents($uploadPath);

            // Upload
            $uploadedFile = $driveService->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => $mimeType,
                'uploadType' => 'multipart',
                'fields' => 'id',
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true,
            ]);

            $fileId = $uploadedFile->id;

            // Clean up temp file if created
            if ($tempFileCreated && file_exists($uploadPath)) {
                @unlink($uploadPath);
            }

            if (!$fileId) {
                return null;
            }

            // Set permission to public reader
            try {
                $permission = new \Google\Service\Drive\Permission([
                    'type' => 'anyone',
                    'role' => 'reader'
                ]);
                $driveService->permissions->create($fileId, $permission, [
                    'supportsAllDrives' => true,
                    'supportsTeamDrives' => true,
                ]);
            } catch (\Exception $e) {
                Log::warning('GoogleDriveService: Gagal menyetel file permission ke public: ' . $e->getMessage());
            }

            // Delete old file if provided
            if ($oldFileId) {
                $this->deletePhoto($oldFileId);
            }

            return $fileId;

        } catch (\Exception $e) {
            Log::error('GoogleDriveService uploadPhoto failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Delete/trash a file from Google Drive.
     *
     * @param string $fileId
     * @return bool True on success, false on failure
     */
    public function deletePhoto(string $fileId): bool
    {
        if (!$this->isEnabled || !$this->driveService) {
            return false;
        }

        try {
            $driveService = $this->driveService;
            $driveService->files->delete($fileId, [
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true,
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error("GoogleDriveService deletePhoto failed for file ID {$fileId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Downloads the file contents from Google Drive and returns as base64 string.
     * Caches the base64 output for 24 hours.
     *
     * @param string $fileId
     * @return string data:image/jpeg;base64,... or empty string on failure
     */
    public function getPhotoBase64(string $fileId): string
    {
        if (!$this->isEnabled || !$this->driveService) {
            return '';
        }

        try {
            return Cache::remember("gd_photo_base64_{$fileId}", now()->addHours(24), function () use ($fileId) {
                $driveService = $this->driveService;
                /** @var \Psr\Http\Message\ResponseInterface $response */
                $response = $driveService->files->get($fileId, [
                    'alt' => 'media',
                    'supportsAllDrives' => true,
                    'supportsTeamDrives' => true,
                ]);
                $content = $response->getBody()->getContents();
                return 'data:image/jpeg;base64,' . base64_encode($content);
            });
        } catch (\Exception $e) {
            Log::error("GoogleDriveService getPhotoBase64 failed for file ID {$fileId}: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Return direct preview URL.
     *
     * @param string $fileId
     * @return string
     */
    public function getPhotoUrl(string $fileId): string
    {
        return "https://drive.google.com/thumbnail?id={$fileId}&sz=w800";
    }

    /**
     * Resize image if it exceeds 800px in either dimension.
     * Returns the path to the file to be uploaded (might be a temporary file).
     * The caller is responsible for deleting the temp file if a new one was created.
     */
    private function resizeImageIfNeeded(string $filePath, string $mimeType, &$tempFileCreated): string
    {
        $tempFileCreated = false;

        if (!extension_loaded('gd') ||
            !function_exists('getimagesize') ||
            !function_exists('imagecreatefromjpeg') ||
            !function_exists('imagecreatetruecolor') ||
            !function_exists('imagecopyresampled')) {
            return $filePath;
        }

        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return $filePath;
        }

        list($width, $height, $imageType) = $imageInfo;

        if ($width <= 800 && $height <= 800) {
            return $filePath;
        }

        if ($width > $height) {
            $newWidth = 800;
            $newHeight = (int) ($height * (800 / $width));
        } else {
            $newHeight = 800;
            $newWidth = (int) ($width * (800 / $height));
        }

        $srcImage = null;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImage = @imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                if (function_exists('imagecreatefrompng')) {
                    $srcImage = @imagecreatefrompng($filePath);
                }
                break;
            case IMAGETYPE_GIF:
                if (function_exists('imagecreatefromgif')) {
                    $srcImage = @imagecreatefromgif($filePath);
                }
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = @imagecreatefromwebp($filePath);
                }
                break;
        }

        if (!$srcImage) {
            return $filePath;
        }

        $dstImage = imagecreatetruecolor($newWidth, $newHeight);
        if (!$dstImage) {
            imagedestroy($srcImage);
            return $filePath;
        }

        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        if (!@imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
            imagedestroy($srcImage);
            imagedestroy($dstImage);
            return $filePath;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'gd_drive_');
        if (!$tempPath) {
            imagedestroy($srcImage);
            imagedestroy($dstImage);
            return $filePath;
        }

        $saved = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $saved = @imagejpeg($dstImage, $tempPath, 90);
                break;
            case IMAGETYPE_PNG:
                $saved = @imagepng($dstImage, $tempPath);
                break;
            case IMAGETYPE_GIF:
                $saved = @imagegif($dstImage, $tempPath);
                break;
            case IMAGETYPE_WEBP:
                $saved = @imagewebp($dstImage, $tempPath);
                break;
            default:
                $saved = @imagejpeg($dstImage, $tempPath, 90);
                break;
        }

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if ($saved) {
            $tempFileCreated = true;
            return $tempPath;
        }

        if (file_exists($tempPath)) {
            @unlink($tempPath);
        }

        return $filePath;
    }
}
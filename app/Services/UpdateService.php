<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Pengaturan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class UpdateService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.update_server.url', env('MASTER_DB_API_URL', ''));
        $this->apiKey = config('services.update_server.key', env('MASTER_DB_API_KEY', ''));
    }

    public function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }

    public function checkForUpdates(): array
    {
        if (empty($this->apiUrl)) {
            return ['status' => false, 'message' => 'Update server URL not configured.'];
        }

        try {
            // Simulating request to central server
            // In real scenario, uncomment this:
            /*
            $response = Http::withHeaders(['X-API-KEY' => $this->apiKey])
                ->get($this->apiUrl . '/api/v1/check-update', [
                    'current_version' => $this->getCurrentVersion(),
                    'license_key' => env('LICENSE_KEY')
                ]);
            
            if ($response->failed()) {
                return ['status' => false, 'message' => 'Failed to connect to update server.'];
            }
            $data = $response->json();
            */

            // Mocking response for development
            $data = [
                'latest_version' => '1.0.1',
                'changelog' => "- Perbaikan bug pada scan QR\n- Penambahan fitur sinkronisasi update\n- Optimasi performa dashboard",
                'package_url' => 'https://example.com/updates/v1.0.1.zip',
                'release_date' => '2026-05-05'
            ];

            $isUpdateAvailable = version_compare($data['latest_version'], $this->getCurrentVersion(), '>');

            if ($isUpdateAvailable) {
                $this->saveUpdateInfo($data);
            } else {
                $this->clearUpdateInfo();
            }

            return [
                'status' => true,
                'update_available' => $isUpdateAvailable,
                'latest_version' => $data['latest_version'],
                'changelog' => $data['changelog'],
                'release_date' => $data['release_date']
            ];

        } catch (\Exception $e) {
            Log::error('Update check error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'An error occurred during update check.'];
        }
    }

    private function saveUpdateInfo(array $data): void
    {
        Pengaturan::updateOrCreate(['key' => 'update_available_version'], ['value' => $data['latest_version'], 'group' => 'update']);
        Pengaturan::updateOrCreate(['key' => 'update_changelog'], ['value' => $data['changelog'], 'group' => 'update']);
        Pengaturan::updateOrCreate(['key' => 'update_package_url'], ['value' => $data['package_url'], 'group' => 'update']);
        Pengaturan::updateOrCreate(['key' => 'update_last_check'], ['value' => now()->toDateTimeString(), 'group' => 'update']);
    }

    private function clearUpdateInfo(): void
    {
        Pengaturan::where('group', 'update')->delete();
        Pengaturan::updateOrCreate(['key' => 'update_last_check'], ['value' => now()->toDateTimeString(), 'group' => 'update']);
    }

    public function getCachedUpdateInfo(): ?array
    {
        $version = Pengaturan::where('key', 'update_available_version')->first();
        if (!$version) return null;

        return [
            'latest_version' => $version->value,
            'changelog' => Pengaturan::where('key', 'update_changelog')->value('value'),
            'package_url' => Pengaturan::where('key', 'update_package_url')->value('value'),
            'last_check' => Pengaturan::where('key', 'update_last_check')->value('value'),
        ];
    }

    public function runUpdate(): array
    {
        $info = $this->getCachedUpdateInfo();
        if (!$info) {
            return ['status' => false, 'message' => 'No update info found. Please check for updates first.'];
        }

        try {
            // In a real scenario:
            // 1. Download package
            // 2. Extract to temporary folder
            // 3. Backup current files
            // 4. Copy new files
            // 5. Run migrations
            // 6. Clear cache

            Log::info('Starting update to version ' . $info['latest_version']);

            // Simulate progress
            // For now, we just simulate a successful update by updating the .env version
            $this->updateEnvVersion($info['latest_version']);
            
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            $this->clearUpdateInfo();

            return ['status' => true, 'message' => 'Update successful.'];

        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }

    private function updateEnvVersion(string $newVersion): void
    {
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            $content = preg_replace('/APP_VERSION=.*/', 'APP_VERSION=' . $newVersion, $content);
            File::put($envPath, $content);
        }
    }
}

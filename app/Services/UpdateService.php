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
    public function getCurrentVersion(): string
    {
        $dbVersion = Pengaturan::where('key', 'app_version')->value('value');
        if ($dbVersion) return $dbVersion;
        
        return config('app.version', env('APP_VERSION', '1.0.0'));
    }

    public function checkForUpdates(): array
    {
        $owner = Pengaturan::where('key', 'github_repo_owner')->value('value');
        $repo = Pengaturan::where('key', 'github_repo_name')->value('value');
        $token = Pengaturan::where('key', 'github_access_token')->value('value');

        if (empty($owner) || empty($repo)) {
            return [
                'status' => false, 
                'message' => 'Konfigurasi repositori GitHub belum lengkap (Owner/Repo kosong).'
            ];
        }

        try {
            $url = "https://api.github.com/repos/{$owner}/{$repo}/releases/latest";
            
            $headers = [
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ];

            $request = Http::withoutVerifying()->withHeaders($headers);

            if (!empty($token)) {
                $request->withToken($token);
            }

            $response = $request->timeout(60)->get($url);

            if ($response->status() === 404) {
                return [
                    'status' => false, 
                    'message' => 'Repositori tidak ditemukan atau Token tidak valid untuk akses privat.'
                ];
            }

            if ($response->failed()) {
                return [
                    'status' => false, 
                    'message' => 'Gagal terhubung ke GitHub: ' . ($response->json()['message'] ?? $response->body())
                ];
            }

            $data = $response->json();
            
            // Bersihkan tag version (misal v1.0.1 -> 1.0.1)
            $latestVersion = ltrim($data['tag_name'], 'v');

            $isUpdateAvailable = version_compare($latestVersion, $this->getCurrentVersion(), '>');

            $updateData = [
                'latest_version' => $latestVersion,
                'changelog' => $data['body'] ?? 'Tidak ada catatan perubahan.',
                'package_url' => $data['zipball_url'], // GitHub menyediakan zipball_url untuk source
                'release_date' => isset($data['published_at']) ? date('Y-m-d H:i', strtotime($data['published_at'])) : now()->toDateTimeString()
            ];

            if ($isUpdateAvailable) {
                $this->saveUpdateInfo($updateData);
            } else {
                $this->clearUpdateInfo();
            }

            return array_merge([
                'status' => true,
                'update_available' => $isUpdateAvailable,
            ], $updateData);

        } catch (\Exception $e) {
            Log::error('Update check error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Terjadi kesalahan saat memeriksa update: ' . $e->getMessage()];
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
        // Hanya hapus data hasil update, jangan hapus KONFIGURASI repositori
        Pengaturan::whereIn('key', ['update_available_version', 'update_changelog', 'update_package_url'])->delete();
        
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
        // Set time limit before ANY operation
        if (function_exists('set_time_limit')) {
            @set_time_limit(600); // 10 minutes max for the whole operation
        }
        ini_set('max_execution_time', 600);

        $info = $this->getCachedUpdateInfo();
        if (!$info) {
            return ['status' => false, 'message' => 'Informasi update tidak ditemukan. Silakan periksa update terlebih dahulu.'];
        }

        $token = Pengaturan::where('key', 'github_access_token')->value('value');

        try {
            Log::info('Memulai pengunduhan update: ' . $info['package_url']);

            // 1. Download Paket
            $request = Http::withoutVerifying()->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ]);
            
            if (!empty($token)) {
                $request->withToken($token);
            }

            $response = $request->timeout(300)->get($info['package_url']);
            
            if ($response->failed()) {
                throw new \Exception('Gagal mengunduh paket dari GitHub (Status: ' . $response->status() . ').');
            }

            $tempPath = storage_path('app/updates');
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }

            $zipFile = $tempPath . '/update.zip';
            File::put($zipFile, $response->body());

            // 2. Ekstrak Paket
            $zip = new \ZipArchive();
            if ($zip->open($zipFile) === TRUE) {
                $extractPath = $tempPath . '/extracted';
                if (File::exists($extractPath)) {
                    File::deleteDirectory($extractPath);
                }
                File::makeDirectory($extractPath);
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('Gagal membuka file paket update (ZIP).');
            }

            // 3. Pindahkan File (Overwriting)
            $folders = File::directories($extractPath);
            if (count($folders) > 0) {
                $sourcePath = $folders[0]; // Folder root di dalam zip GitHub (owner-repo-hash)
                
                $this->recursiveCopy($sourcePath, base_path(), [
                    base_path('.env'),
                    base_path('storage'),
                    base_path('public/storage'),
                    base_path('database/database.sqlite'), // Jika menggunakan sqlite di root
                ]);
            } else {
                throw new \Exception('Struktur paket update tidak valid (folder root tidak ditemukan).');
            }

            // 4. Cleanup & Finalisasi
            File::delete($zipFile);
            File::deleteDirectory($extractPath);

            $this->updateEnvVersion($info['latest_version']);
            
            // UPDATE DATABASE VERSION JUGA
            Pengaturan::updateOrCreate(
                ['key' => 'app_version'],
                ['value' => $info['latest_version'], 'group' => 'update']
            );
            
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            $this->clearUpdateInfo();

            Log::info('Update ke versi ' . $info['latest_version'] . ' berhasil diselesaikan.');

            return ['status' => true, 'message' => 'Sistem berhasil diperbarui ke versi ' . $info['latest_version']];

        } catch (\Exception $e) {
            Log::error('Update error: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()];
        }
    }

    /**
     * Menyalin folder secara rekursif dengan pengecualian path tertentu.
     */
    private function recursiveCopy($src, $dst, $excluded = [])
    {
        $dir = opendir($src);
        if (!File::exists($dst)) {
            File::makeDirectory($dst, 0755, true);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;

                // Cek apakah file/folder ini dikecualikan
                foreach ($excluded as $ex) {
                    if (realpath($dstFile) === realpath($ex) || str_starts_with(realpath($dstFile), realpath($ex) . DIRECTORY_SEPARATOR)) {
                        continue 2;
                    }
                }

                if (is_dir($srcFile)) {
                    $this->recursiveCopy($srcFile, $dstFile, $excluded);
                } else {
                    copy($srcFile, $dstFile);
                }
            }
        }
        closedir($dir);
    }

    private function updateEnvVersion(string $newVersion): void
    {
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            if (str_contains($content, 'APP_VERSION=')) {
                $content = preg_replace('/APP_VERSION=.*/', 'APP_VERSION=' . $newVersion, $content);
            } else {
                $content .= "\nAPP_VERSION=" . $newVersion;
            }
            File::put($envPath, $content);
        }
    }
}

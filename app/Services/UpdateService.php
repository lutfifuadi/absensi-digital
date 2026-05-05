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

    public function getChangelogHistory(): array
    {
        $history = Pengaturan::where('key', 'update_changelog_history')->first();
        if (!$history) return [];

        return json_decode($history->value, true) ?? [];
    }

    public function saveCurrentChangelogToHistory(): void
    {
        $currentVersion = $this->getCurrentVersion();
        // Ambil changelog yang baru saja diinstall (yang tersimpan di update_changelog)
        $currentChangelog = Pengaturan::where('key', 'update_changelog')->value('value');

        if ($currentChangelog) {
            $history = $this->getChangelogHistory();
            
            // Cek apakah versi ini sudah ada di history
            $exists = false;
            foreach ($history as $item) {
                if ($item['version'] === $currentVersion) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $history[] = [
                    'version' => $currentVersion,
                    'changelog' => $currentChangelog,
                    'date' => now()->toDateTimeString(),
                ];

                // Simpan max 10 history saja agar DB tidak bengkak
                if (count($history) > 10) {
                    array_shift($history);
                }

                Pengaturan::updateOrCreate(
                    ['key' => 'update_changelog_history'],
                    ['value' => json_encode($history), 'group' => 'update']
                );
            }
        }
    }

    public function getUpdateDataForModal(): ?array
    {
        $currentInfo = $this->getCachedUpdateInfo();
        if (!$currentInfo) return null;

        return [
            'latest_version' => $currentInfo['latest_version'],
            'current_version' => $this->getCurrentVersion(),
            'changelog' => $currentInfo['changelog'],
            'changelog_history' => $this->getChangelogHistory(),
            'release_date' => $currentInfo['last_check'],
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
            Log::warning('Agen Aulia: Percobaan update tanpa data cache.');
            return ['status' => false, 'message' => 'Informasi update tidak ditemukan. Silakan periksa update terlebih dahulu.'];
        }

        Log::info('Agen Aulia: Memulai proses update sistem ke versi ' . $info['latest_version']);

        $token = Pengaturan::where('key', 'github_access_token')->value('value');

        try {
            Log::info('Langkah 1: Mengunduh paket dari ' . $info['package_url']);

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
                throw new \Exception('Gagal mengunduh paket dari GitHub (Status: ' . $response->status() . '). Pastikan koneksi internet dan token valid.');
            }

            $tempPath = storage_path('app/updates');
            if (!File::exists($tempPath)) {
                File::makeDirectory($tempPath, 0755, true);
            }

            $zipFile = $tempPath . '/update.zip';
            File::put($zipFile, $response->body());

            Log::info('Langkah 2: Mengekstrak paket update.');

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
                throw new \Exception('Gagal membuka file paket update (ZIP). File mungkin korup.');
            }

            // 3. Pindahkan File (Overwriting)
            $folders = File::directories($extractPath);
            if (count($folders) > 0) {
                $sourcePath = $folders[0]; 
                
                Log::info('Langkah 3: Menyalin file update ke root direktori.');
                
                $this->recursiveCopy($sourcePath, base_path(), [
                    base_path('.env'),
                    base_path('storage'),
                    base_path('public/storage'),
                    base_path('database/database.sqlite'),
                ]);
            } else {
                throw new \Exception('Struktur paket update tidak valid (folder root tidak ditemukan di dalam ZIP).');
            }

            // 4. Cleanup & Finalisasi
            Log::info('Langkah 4: Finalisasi dan pembersihan.');
            File::delete($zipFile);
            File::deleteDirectory($extractPath);

            $this->updateEnvVersion($info['latest_version']);
            
            Pengaturan::updateOrCreate(
                ['key' => 'app_version'],
                ['value' => $info['latest_version'], 'group' => 'update']
            );

            // Simpan ke history
            $this->saveCurrentChangelogToHistory();
            
            Log::info('Langkah 5: Menjalankan migrasi dan pembersihan cache.');
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');

            $this->clearUpdateInfo();

            Log::info('Agen Aulia: Update ke versi ' . $info['latest_version'] . ' berhasil diselesaikan.');

            return ['status' => true, 'message' => 'Sistem berhasil diperbarui ke versi ' . $info['latest_version']];

        } catch (\Exception $e) {
            Log::error('Agen Aulia: Terjadi kesalahan saat update: ' . $e->getMessage());
            return ['status' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()];
        }
    }

    /**
     * Menyalin folder secara rekursif dengan pengecualian path tertentu.
     */
    private function recursiveCopy($src, $dst, $excluded = [])
    {
        if (!File::exists($src)) return;

        $dir = opendir($src);
        if (!File::exists($dst)) {
            File::makeDirectory($dst, 0755, true);
        }

        // Normalize excluded paths for comparison
        $normalizedExcluded = array_map(function($path) {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        }, $excluded);

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcFile = $src . '/' . $file;
                $dstFile = $dst . '/' . $file;
                
                $normalizedDstFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dstFile);

                // Cek apakah file/folder ini dikecualikan
                $isExcluded = false;
                foreach ($normalizedExcluded as $ex) {
                    if ($normalizedDstFile === $ex || str_starts_with($normalizedDstFile, $ex . DIRECTORY_SEPARATOR)) {
                        $isExcluded = true;
                        break;
                    }
                }

                if ($isExcluded) continue;

                if (is_dir($srcFile)) {
                    $this->recursiveCopy($srcFile, $dstFile, $excluded);
                } else {
                    File::copy($srcFile, $dstFile);
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

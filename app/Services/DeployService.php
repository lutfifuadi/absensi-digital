<?php

namespace App\Services;

use App\Models\DeployLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DeployService
{
    protected array $env;

    public function __construct()
    {
        $this->env = [
            'PATH' => getenv('PATH') ?: '/usr/local/bin:/usr/bin:/bin',
            'HOME' => getenv('HOME') ?: '/root',
        ];
    }

    public function checkEnvironment(): array
    {
        $tools = ['git', 'composer', 'mysqldump'];
        $result = [];

        foreach ($tools as $tool) {
            $process = new Process([$tool, '--version'], null, $this->env);
            $process->run();
            $result[$tool] = [
                'available' => $process->isSuccessful(),
                'version' => $process->isSuccessful() ? trim($process->getOutput()) : null,
            ];
        }

        return $result;
    }

    public function checkQueueHeartbeat(): bool
    {
        return Cache::has('queue_heartbeat');
    }

    public function checkVersion(): array
    {
        $local = new Process(['git', 'log', '--oneline', '-1'], base_path(), $this->env);
        $local->run();

        $remote = new Process(['git', 'log', 'origin/main', '--oneline', '-1'], base_path(), $this->env);
        $remote->run();

        $behind = new Process(['git', 'rev-list', '--count', 'HEAD..origin/main'], base_path(), $this->env);
        $behind->run();

        return [
            'local' => $local->isSuccessful() ? trim($local->getOutput()) : 'N/A',
            'remote' => $remote->isSuccessful() ? trim($remote->getOutput()) : 'N/A',
            'behind' => $behind->isSuccessful() ? (int) trim($behind->getOutput()) : 0,
        ];
    }

    public function backupDatabase(): string
    {
        $dbName = config('database.connections.mariadb.database') ?: config('database.connections.mysql.database');
        $username = config('database.connections.mariadb.username') ?: config('database.connections.mysql.username');
        $password = config('database.connections.mariadb.password') ?: config('database.connections.mysql.password');
        $host = config('database.connections.mariadb.host') ?: config('database.connections.mysql.host');

        $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
        $backupPath = 'backups/' . $filename;

        Storage::makeDirectory('backups');

        $fullPath = Storage::path($backupPath);

        $command = "mysqldump --user={$username} --password={$password} --host={$host} {$dbName} > {$fullPath}";
        $process = Process::fromShellCommandline($command, null, $this->env);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $fullPath;
    }

    public function runDeploy(User $user, DeployLog $deployLog, array &$progress): void
    {
        $steps = [
            'backup' => ['label' => 'Backup database...', 'progress' => 10],
            'fetch' => ['label' => 'git fetch origin...', 'progress' => 30],
            'reset' => ['label' => 'git reset --hard origin/main...', 'progress' => 40],
            'composer' => ['label' => 'composer install...', 'progress' => 60],
            'migrate' => ['label' => 'php artisan migrate --force...', 'progress' => 80],
            'optimize' => ['label' => 'php artisan optimize...', 'progress' => 90],
            'restart-queue' => ['label' => 'php artisan queue:restart...', 'progress' => 100],
        ];

        $logOutput = '';

        try {
            // Step 1: Backup
            $this->updateProgress($progress, $steps['backup']['label'], $steps['backup']['progress'], $logOutput);
            $backupPath = $this->backupDatabase();
            $deployLog->update(['backup_path' => $backupPath]);
            $logOutput .= "[OK] Backup database: {$backupPath}\n";

            // Step 2: git fetch
            $this->updateProgress($progress, $steps['fetch']['label'], $steps['fetch']['progress'], $logOutput);
            $this->runProcess(['git', 'fetch', 'origin'], 120);
            $logOutput .= "[OK] git fetch origin\n";

            // Step 3: git reset
            $this->updateProgress($progress, $steps['reset']['label'], $steps['reset']['progress'], $logOutput);
            $this->runProcess(['git', 'reset', '--hard', 'origin/main'], 120);
            $logOutput .= "[OK] git reset --hard origin/main\n";

            $commitInfo = $this->getCurrentCommitInfo();
            $deployLog->update([
                'version' => $commitInfo['version'],
                'commit_hash' => $commitInfo['hash'],
                'commit_message' => $commitInfo['message'],
            ]);

            // Step 4: composer install (Hanya jika composer.json atau composer.lock berubah)
            $composerChanged = false;
            try {
                $diffProcess = new Process(['git', 'diff', '--name-only', 'HEAD@{1}', 'HEAD'], base_path(), $this->env);
                $diffProcess->run();
                if ($diffProcess->isSuccessful()) {
                    $files = explode("\n", trim($diffProcess->getOutput()));
                    foreach ($files as $f) {
                        if (str_contains($f, 'composer.json') || str_contains($f, 'composer.lock')) {
                            $composerChanged = true;
                            break;
                        }
                    }
                } else {
                    $composerChanged = true;
                }
            } catch (\Exception $e) {
                $composerChanged = true;
            }

            if ($composerChanged) {
                $this->updateProgress($progress, $steps['composer']['label'], $steps['composer']['progress'], $logOutput);
                $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader'], 300);
                $logOutput .= "[OK] composer install\n";
            } else {
                $logOutput .= "[SKIP] composer install (tidak ada perubahan dependensi PHP)\n";
            }

            // Step 5: migrate
            $this->updateProgress($progress, $steps['migrate']['label'], $steps['migrate']['progress'], $logOutput);
            $this->runProcess(['php', 'artisan', 'migrate', '--force'], 120);
            $logOutput .= "[OK] php artisan migrate --force\n";

            // Step 6: optimize
            $this->updateProgress($progress, $steps['optimize']['label'], $steps['optimize']['progress'], $logOutput);
            $this->runProcess(['php', 'artisan', 'optimize'], 120);
            $logOutput .= "[OK] php artisan optimize\n";

            // Update database version setting to match new code version (running in separate process to load new files)
            try {
                $versionProcess = new Process(['php', '-r', 'echo (include "config/app.php")["version"] ?? "";'], base_path(), $this->env);
                $versionProcess->run();
                if ($versionProcess->isSuccessful() && !empty(trim($versionProcess->getOutput()))) {
                    $newVersion = trim($versionProcess->getOutput());
                    \App\Models\Pengaturan::updateOrCreate(
                        ['key' => 'app_version'],
                        ['value' => $newVersion]
                    );
                    $logOutput .= "[OK] Database version updated to: {$newVersion}\n";
                }
            } catch (\Exception $e) {
                $logOutput .= "[WARNING] Failed to update database version: " . $e->getMessage() . "\n";
            }

            // Step 7: queue:restart
            $this->updateProgress($progress, $steps['restart-queue']['label'], $steps['restart-queue']['progress'], $logOutput);
            $this->runProcess(['php', 'artisan', 'queue:restart'], 30);
            $logOutput .= "[OK] php artisan queue:restart\n";

            $deployLog->update([
                'status' => 'success',
                'log_output' => $logOutput,
                'finished_at' => now(),
                'duration_seconds' => $deployLog->started_at->diffInSeconds(now()),
            ]);

            Cache::forget('deploy_running');
            $progress['status'] = 'success';

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $logOutput .= "[FAILED] Terjadi kesalahan saat deploy.\n";
            $deployLog->update([
                'status' => 'failed',
                'log_output' => $logOutput,
                'finished_at' => now(),
                'duration_seconds' => $deployLog->started_at->diffInSeconds(now()),
            ]);

            Log::error('Deploy failed', [
                'deploy_log_id' => $deployLog->id,
                'user_id' => $user->id,
                'error' => $errorMessage,
            ]);

            Cache::forget('deploy_running');
            $progress['status'] = 'failed';
            $progress['error'] = 'Terjadi kesalahan saat deploy. Silakan cek log untuk detail.';

            throw $e;
        }
    }

    public function rollback(int $deployLogId): void
    {
        $deployLog = DeployLog::findOrFail($deployLogId);

        if ($deployLog->status !== 'failed') {
            throw new \Exception('Rollback hanya dapat dilakukan pada deploy dengan status failed.');
        }

        if (empty($deployLog->backup_path) || !file_exists($deployLog->backup_path)) {
            throw new \Exception('File backup tidak ditemukan.');
        }

        $dbName = config('database.connections.mariadb.database') ?: config('database.connections.mysql.database');
        $username = config('database.connections.mariadb.username') ?: config('database.connections.mysql.username');
        $password = config('database.connections.mariadb.password') ?: config('database.connections.mysql.password');
        $host = config('database.connections.mariadb.host') ?: config('database.connections.mysql.host');

        $command = "mysql --user={$username} --password={$password} --host={$host} {$dbName} < {$deployLog->backup_path}";
        $process = Process::fromShellCommandline($command, null, $this->env);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Git checkout ke commit sebelum deploy
        $prevCommit = new Process(['git', 'log', '--oneline', '-2', '--skip=1'], base_path(), $this->env);
        $prevCommit->run();
        if ($prevCommit->isSuccessful()) {
            $parts = explode(' ', trim($prevCommit->getOutput()));
            $hash = $parts[0] ?? 'HEAD~1';
            $this->runProcess(['git', 'checkout', $hash], 60);
        }

        $deployLog->update([
            'status' => 'rolled_back',
            'finished_at' => now(),
        ]);
    }

    protected function runProcess(array $command, int $timeout = 120): string
    {
        $process = new Process($command, base_path(), $this->env);
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    protected function getCurrentCommitInfo(): array
    {
        $hash = new Process(['git', 'log', '--oneline', '-1'], base_path(), $this->env);
        $hash->run();

        $message = new Process(['git', 'log', '-1', '--format=%s'], base_path(), $this->env);
        $message->run();

        $tag = new Process(['git', 'describe', '--tags', '--exact-match', '--always'], base_path(), $this->env);
        $tag->run();

        $hashOutput = $hash->isSuccessful() ? trim($hash->getOutput()) : 'N/A';
        $parts = explode(' ', $hashOutput, 2);

        return [
            'hash' => $parts[0] ?? 'N/A',
            'message' => $message->isSuccessful() ? trim($message->getOutput()) : 'N/A',
            'version' => $tag->isSuccessful() ? trim($tag->getOutput()) : $parts[0] ?? 'N/A',
        ];
    }

    protected function updateProgress(array &$progress, string $step, int $percentage, string &$logOutput): void
    {
        $progress['step'] = $step;
        $progress['percentage'] = $percentage;
        $progress['log'][] = $step;
        Cache::put('deploy_progress', $progress, 600);
    }
}

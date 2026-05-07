<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyLicense extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'license:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the application license against the remote server';

    private const LICENSE_API_URL = 'https://saas-presensi.lutfifuadi.my.id/api/license/verify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // We will run the check 6 times with 10 seconds interval to achieve "every 10 seconds"
        // as the Laravel scheduler minimum is 1 minute.
        for ($i = 0; $i < 6; $i++) {
            $status = $this->performVerification();
            
            // If license is invalid and cleared (status 1), stop immediately
            // Status 1 means the server rejected the license.
            if ($status === 1) {
                break;
            }

            if ($i < 5) {
                $this->info("Waiting 10 seconds for next check...");
                sleep(10);
            }
        }

        return 0;
    }

    /**
     * The actual verification logic.
     */
    private function performVerification()
    {
        $licenseKey = env('LICENSE_KEY');
        $domain     = env('REGISTERED_DOMAIN');

        if (empty($licenseKey)) {
            $this->info('No license key found. Skipping verification.');
            return 0;
        }

        // Development bypass
        if ($licenseKey === 'DEV-MASTER-KEY') {
            $this->info('Master key detected. Verification bypassed.');
            return 0;
        }

        $this->info("Verifying license: {$licenseKey} for domain: {$domain}");

        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->timeout(30)
                ->post(self::LICENSE_API_URL, [
                    'license_key' => $licenseKey,
                    'domain'      => $domain,
                ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['success'])) {
                $errorMsg = 'License validation failed.';
                if (isset($result['message'])) {
                    $errorMsg .= ' Reason: ' . $result['message'];
                }

                $this->error($errorMsg);
                Log::warning('License verification failed: ' . $errorMsg);

                // Clear license from .env to trigger reactivation
                $this->clearLicense();
                
                return 1;
            }

            $this->info('License verified successfully.');
            // Log success removed to prevent log bloating since it runs every minute
            
        } catch (\Exception $e) {
            $this->error('Failed to contact verification server: ' . $e->getMessage());
            Log::error('License verification error: ' . $e->getMessage());
            // We don't clear the license if the server is just unreachable
            return 1;
        }

        return 0;
    }

    /**
     * Remove LICENSE_KEY and REGISTERED_DOMAIN from .env file.
     */
    private function clearLicense(): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        $keys = ['LICENSE_KEY', 'REGISTERED_DOMAIN'];

        foreach ($keys as $key) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}=", $content);
            }
        }

        file_put_contents($envPath, $content);

        // Clear config cache
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            $this->info('Configuration cache cleared.');
        } catch (\Exception $e) {
            $this->error('Failed to clear config cache: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LicenseController extends Controller
{
    private const LICENSE_API_URL = 'https://saas-presensi.lutfifuadi.my.id/api/license/verify';

    /**
     * Show the license warning / activation page.
     */
    public function showWarning()
    {
        // If license is already set and active in DB, redirect to home
        $licenseKey = config('license.key');
        $dbStatus = \App\Models\Pengaturan::where('key', 'license_status')->value('value');

        if (!empty($licenseKey) && $dbStatus === 'active') {
            return redirect('/');
        }

        return view('license-warning');
    }

    /**
     * Process the license activation form submission.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'license_key'       => 'required|string|min:5',
            'registered_domain' => 'required|string|min:3',
        ]);

        $license = trim($request->license_key);
        $domain  = trim($request->registered_domain);

        // Development bypass
        if ($license === 'DEV-MASTER-KEY') {
            $this->writeEnv($license, $domain);
            return redirect('/')->with('success', 'Lisensi berhasil diaktifkan.');
        }

        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->timeout(30)
                ->post(self::LICENSE_API_URL, [
                    'license_key' => $license,
                    'domain'      => $domain,
                ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['success'])) {
                $errorMsg = 'Lisensi tidak valid untuk domain: ' . $domain;

                if (isset($result['message'])) {
                    $errorMsg .= ' — ' . $result['message'];
                }

                return back()->withInput()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menghubungi server verifikasi: ' . $e->getMessage());
        }

        $this->writeEnv($license, $domain);

        return redirect('/')->with('success', 'Lisensi berhasil diaktifkan! Selamat datang.');
    }

    /**
     * Write LICENSE_KEY and REGISTERED_DOMAIN to .env file.
     */
    private function writeEnv(string $licenseKey, string $domain): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        $keys = [
            'LICENSE_KEY'       => $licenseKey,
            'REGISTERED_DOMAIN' => $domain,
        ];

        foreach ($keys as $key => $value) {
            $escapedValue = preg_match('/\s/', $value) ? '"' . $value . '"' : $value;

            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$escapedValue}",
                    $content
                );
            } else {
                $content .= "\n{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, $content);

        // Also update database status as the primary truth
        try {
            \App\Models\Pengaturan::updateOrCreate(
                ['key' => 'license_status'],
                ['value' => 'active', 'group' => 'license']
            );
        } catch (\Exception $e) {
            // Silently fail database update
        }

        // Clear config cache so new values are picked up immediately
        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Non-fatal – continue
        }
    }
}

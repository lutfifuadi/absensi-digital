<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class LicenseController extends Controller
{
    private const LICENSE_API_URL = 'https://saas-presensi.lutfifuadi.my.id/api/license/verify';

    /**
     * Show the license dashboard for admin.
     */
    public function index()
    {
        $licenseKey = config('license.key');
        $domain     = config('license.domain');
        $status     = \App\Models\Pengaturan::where('key', 'license_status')->value('value') ?? 'inactive';
        $schoolName = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? '-';
        
        return view('admin.license.index', compact('licenseKey', 'domain', 'status', 'schoolName'));
    }

    /**
     * Show the license warning / activation page.
     */
    public function showWarning()
    {
        // If license is already set and active in DB, redirect to home
        $licenseKey = config('license.key');
        $dbStatus   = \App\Models\Pengaturan::where('key', 'license_status')->value('value');

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
            'school_name'       => 'required|string|min:3',
        ]);

        // Sanitize inputs to prevent .env injection (strip newlines and unsafe chars)
        $license = preg_replace('/[^A-Za-z0-9\-_]/', '', trim($request->license_key));
        $domain  = preg_replace('/[\r\n\0]/', '', trim($request->registered_domain));
        $school  = strip_tags(trim($request->school_name));

        // Development bypass
        if ($license === 'DEV-MASTER-KEY') {
            $this->writeEnv($license, $domain);
            return redirect('/')->with('success', 'Lisensi berhasil diaktifkan.');
        }

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post(self::LICENSE_API_URL, [
                    'license_key' => $license,
                    'domain'      => $domain,
                    'school_name' => $school,
                ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['success'])) {
                $errorMsg = 'Lisensi tidak valid untuk domain: ' . htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');

                if (isset($result['message'])) {
                    $errorMsg .= ' — ' . strip_tags($result['message']);
                }

                return back()->withInput()->with('error', $errorMsg);
            }

            // Write keys to .env
            $this->writeEnv($license, $domain);

            // Set DB status to active
            \App\Models\Pengaturan::updateOrCreate(
                ['key' => 'license_status'],
                ['value' => 'active', 'group' => 'license']
            );

            return redirect('/')->with('success', 'Lisensi berhasil diaktifkan.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menghubungi server lisensi. Coba lagi nanti.');
        }
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

        // Update or append LICENSE_KEY
        if (str_contains($content, 'LICENSE_KEY=')) {
            $content = preg_replace('/^LICENSE_KEY=.*/m', 'LICENSE_KEY=' . $licenseKey, $content);
        } else {
            $content .= "\nLICENSE_KEY=" . $licenseKey;
        }

        // Update or append REGISTERED_DOMAIN
        if (str_contains($content, 'REGISTERED_DOMAIN=')) {
            $content = preg_replace('/^REGISTERED_DOMAIN=.*/m', 'REGISTERED_DOMAIN=' . $domain, $content);
        } else {
            $content .= "\nREGISTERED_DOMAIN=" . $domain;
        }

        file_put_contents($envPath, $content);

        // Clear config cache so new values take effect immediately
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}

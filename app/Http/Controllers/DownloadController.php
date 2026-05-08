<?php

namespace App\Http\Controllers;

use App\Models\PembelianLisensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    /**
     * Serve the app download ZIP via a signed token URL.
     * Route: GET /download/app/{token}  (signed)
     */
    public function downloadApp(Request $request, string $token)
    {
        // Validate signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Link download tidak valid atau sudah kadaluarsa.');
        }

        $pembelian = PembelianLisensi::where('download_token', $token)->first();

        if (!$pembelian) {
            abort(404, 'Token download tidak ditemukan.');
        }

        if (!$pembelian->isValid()) {
            abort(403, 'Lisensi tidak aktif. Hubungi penyedia layanan.');
        }

        Log::info('App download accessed', [
            'pembelian_id' => $pembelian->id,
            'email'        => $pembelian->email_klien,
            'ip'           => $request->ip(),
        ]);

        // Ambil URL download terbaru dari GitHub Release
        $downloadUrl = $this->getLatestReleaseUrl();

        if ($downloadUrl) {
            return redirect($downloadUrl);
        }

        // Fallback: redirect ke halaman panduan manual
        return redirect()->route('download.manual')
            ->with('warning', 'Release otomatis tidak tersedia. Silakan ikuti panduan manual di bawah.');
    }

    /**
     * Halaman panduan download manual.
     */
    public function manualDownload()
    {
        return view('download.manual');
    }

    /**
     * Ambil URL download ZIP dari GitHub Release terbaru.
     */
    private function getLatestReleaseUrl(): ?string
    {
        $owner = env('GITHUB_REPO_OWNER', 'lutfifuadi');
        $repo  = env('GITHUB_REPO_NAME', 'absensi-digital');
        $token = config('services.github.token');

        try {
            $headers = ['Accept' => 'application/vnd.github+json'];
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get("https://api.github.com/repos/{$owner}/{$repo}/releases/latest");

            if (!$response->successful()) {
                Log::warning('GitHub release fetch failed', ['status' => $response->status()]);
                return null;
            }

            $assets = $response->json('assets', []);
            foreach ($assets as $asset) {
                if (str_ends_with($asset['name'], '.zip')) {
                    return $asset['browser_download_url'];
                }
            }

            // Fallback: tarball
            return $response->json('zipball_url');
        } catch (\Exception $e) {
            Log::error('Exception fetching GitHub release', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

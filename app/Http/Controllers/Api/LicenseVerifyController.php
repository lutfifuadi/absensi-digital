<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PembelianLisensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicenseVerifyController extends Controller
{
    /**
     * Verify a license key submitted by the installer.
     *
     * POST /api/license/verify
     * Body: license_key, domain, school_name (optional)
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'license_key' => 'required|string|max:100|regex:/^[A-Za-z0-9\-_]+$/',
            'domain'      => 'required|string|max:255',
            'school_name' => 'nullable|string|max:255',
        ]);

        $licenseKey = trim($request->input('license_key'));
        $domain     = trim(strtolower($request->input('domain')));

        // Strip scheme prefix for comparison
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = rtrim($domain, '/');

        Log::info('License verify request', [
            'license_key' => $licenseKey,
            'domain'      => $domain,
            'ip'          => $request->ip(),
        ]);

        $pembelian = PembelianLisensi::where('license_key', $licenseKey)->first();

        if (!$pembelian) {
            Log::warning('License not found', ['license_key' => $licenseKey]);
            return response()->json([
                'success' => false,
                'message' => 'License key tidak ditemukan.',
            ], 404);
        }

        // Check payment status
        if ($pembelian->payment_status !== 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran belum dikonfirmasi untuk lisensi ini.',
            ], 403);
        }

        // Check license status
        if ($pembelian->status === 'revoked') {
            return response()->json([
                'success' => false,
                'message' => 'Lisensi ini telah dicabut. Hubungi penyedia layanan.',
            ], 403);
        }

        if ($pembelian->status === 'expired' || ($pembelian->expires_at && $pembelian->expires_at->isPast())) {
            // Auto-update status to expired
            $pembelian->update(['status' => 'expired']);
            return response()->json([
                'success' => false,
                'message' => 'Lisensi sudah kadaluarsa. Silakan perpanjang.',
            ], 403);
        }

        // Validate domain if one is registered
        if (!empty($pembelian->domain)) {
            $registeredDomain = preg_replace('#^https?://#', '', trim(strtolower($pembelian->domain)));
            $registeredDomain = rtrim($registeredDomain, '/');

            if ($registeredDomain !== $domain) {
                Log::warning('License domain mismatch', [
                    'license_key'       => $licenseKey,
                    'provided_domain'   => $domain,
                    'registered_domain' => $registeredDomain,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => "License key tidak valid untuk domain: {$domain}",
                ], 422);
            }
        } else {
            // Domain belum terdaftar — daftarkan sekarang (pertama kali aktivasi)
            $pembelian->update(['domain' => $domain]);
        }

        // If school_name provided in request, update catatan/nama_klien if empty
        if ($request->filled('school_name') && empty($pembelian->catatan)) {
            $pembelian->update(['catatan' => 'Nama Sekolah: ' . strip_tags($request->school_name)]);
        }

        Log::info('License verified successfully', [
            'license_key' => $licenseKey,
            'domain'      => $domain,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Lisensi valid.',
            'school_name' => $pembelian->nama_klien,
            'expires_at'  => $pembelian->expires_at?->toDateString(),
        ]);
    }
}

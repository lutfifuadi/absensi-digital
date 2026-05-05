<?php

namespace App\Http\Middleware;

use App\Models\Pengaturan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidatePmbmApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $incomingKey = trim((string) $request->header('X-API-KEY', ''));

        if ($incomingKey === '') {
            return response()->json([
                'success' => false,
                'message' => 'X-API-KEY header tidak ditemukan.',
            ], 401);
        }

        $storedKey = trim((string) (Pengaturan::where('key', 'pmbm_incoming_api_key')->value('value')
            ?: env('PMBM_INCOMING_API_KEY', '')));

        if (empty($storedKey)) {
            Log::warning('PMBM Webhook: pmbm_incoming_api_key belum dikonfigurasi di pengaturan atau environment.');
            return response()->json([
                'success' => false,
                'message' => 'Endpoint belum dikonfigurasi. Hubungi administrator.',
            ], 503);
        }

        if (!hash_equals($storedKey, $incomingKey)) {
            Log::warning('PMBM Webhook: API Key tidak valid.', [
                'ip' => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'X-API-KEY tidak valid.',
            ], 403);
        }

        return $next($request);
    }
}

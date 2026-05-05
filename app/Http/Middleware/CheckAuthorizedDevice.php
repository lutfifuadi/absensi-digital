<?php

namespace App\Http\Middleware;

use App\Models\AuthorizedDevice;
use App\Models\Pengaturan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthorizedDevice
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if device locking is enabled in settings
        $isLockEnabled = Pengaturan::where('key', 'lock_device_pc')->value('value') === 'Ya';
        
        if (!$isLockEnabled) {
            return $next($request);
        }

        $deviceUuid = $request->cookie('device_uuid');

        if (!$deviceUuid) {
            // Tidak ada cookie device_uuid → perangkat tidak dikenal
            // Daftarkan sebagai pending dengan UUID baru, lalu redirect ke halaman unauthorized
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Perangkat tidak dikenal. Silakan muat ulang halaman.'], 403);
            }

            // Generate UUID baru untuk device ini agar bisa di-track admin
            $newUuid = \Illuminate\Support\Str::uuid()->toString();

            AuthorizedDevice::create([
                'device_uuid'   => $newUuid,
                'device_name'   => 'Perangkat baru (' . $request->ip() . ')',
                'user_agent'    => $request->header('User-Agent'),
                'ip_address'    => $request->ip(),
                'is_authorized' => false,
            ]);

            return redirect()->route('public.device-unauthorized')
                ->cookie('device_uuid', $newUuid, 60 * 24 * 365, '/', null, false, false);
        }

        $device = AuthorizedDevice::where('device_uuid', $deviceUuid)->first();

        // If device is not found, we create a pending one
        if (!$device) {
            AuthorizedDevice::create([
                'device_uuid' => $deviceUuid,
                'device_name' => 'Perangkat baru (' . $request->ip() . ')',
                'user_agent' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
                'is_authorized' => false,
            ]);
            
            return redirect()->route('public.device-unauthorized');
        }

        if (!$device->is_authorized) {
            // Update last info
            $device->update([
                'ip_address' => $request->ip(),
                'last_active_at' => now(),
            ]);

            return redirect()->route('public.device-unauthorized');
        }

        // Device is authorized, update activity
        $device->update(['last_active_at' => now()]);

        return $next($request);
    }
}

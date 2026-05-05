<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SyncService;

class StaffTuSyncController extends BaseSyncController
{
    protected $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Handle incoming sync request for Staff TU.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|string',
            'email' => 'nullable|email',
            'nama_lengkap' => 'required|string',
            'password' => 'nullable|string',
            'status' => 'nullable|string|in:aktif,cuti,pensiun,keluar',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $payload = $request->all();
        $payload['username'] = $request->input('nip');
        $payload['email'] = $payload['email'] ?? strtolower($request->input('nip')) . '@' . $domainEmail;

        try {
            $staff = $this->syncService->syncStaff($payload);
            return $this->sendResponse($staff, 'Data Staff TU berhasil disinkronisasi.', 200);
        } catch (\Exception $e) {
            return $this->sendError('Sync Failed.', ['error' => $e->getMessage()], 500);
        }
    }
}

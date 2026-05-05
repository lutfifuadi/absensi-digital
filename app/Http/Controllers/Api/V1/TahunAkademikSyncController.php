<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SyncService;

class TahunAkademikSyncController extends BaseSyncController
{
    protected $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Handle incoming sync request for Tahun Akademik.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'semester' => 'required|string|in:Ganjil,Genap',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $ta = $this->syncService->syncTahunAkademik($request->all());
            return $this->sendResponse($ta, 'Data Tahun Akademik berhasil disinkronisasi.', 200);
        } catch (\Exception $e) {
            return $this->sendError('Sync Failed.', ['error' => $e->getMessage()], 500);
        }
    }
}

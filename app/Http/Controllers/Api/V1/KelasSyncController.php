<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SyncService;

class KelasSyncController extends BaseSyncController
{
    protected $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Handle incoming sync request for Kelas.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string',
            'tingkat' => 'nullable|string',
            'jurusan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $kelas = $this->syncService->syncKelas($request->all());
            return $this->sendResponse($kelas, 'Data Kelas berhasil disinkronisasi.', 200);
        } catch (\Exception $e) {
            return $this->sendError('Sync Failed.', ['error' => $e->getMessage()], 500);
        }
    }
}

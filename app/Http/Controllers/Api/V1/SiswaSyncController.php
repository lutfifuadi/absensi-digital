<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\SyncService;

class SiswaSyncController extends BaseSyncController
{
    protected $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Handle incoming sync request for Siswa.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nisn' => 'required|string',
            'email' => 'required|email',
            'nama_lengkap' => 'required|string',
            'password' => 'nullable|string', // Aplikasi eksternal bisa mengirim password baru
            'status' => 'nullable|string|in:aktif,lulus,pindah,keluar',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $siswa = $this->syncService->syncSiswa($request->all());
            return $this->sendResponse($siswa, 'Data Siswa berhasil disinkronisasi.', 200);
        } catch (\Exception $e) {
            return $this->sendError('Sync Failed.', ['error' => $e->getMessage()], 500);
        }
    }
}

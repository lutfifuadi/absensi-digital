<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\MonitoringKehadiranGuru;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMonitoringApiController extends Controller
{
    /**
     * Rekap monitoring dengan filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = MonitoringKehadiranGuru::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.guru', 'guruPengganti', 'pencatat']);

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_dari);
        } else {
            $query->whereDate('tanggal', '>=', today());
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
        } else {
            $query->whereDate('tanggal', '<=', today());
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwalPelajaran', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        if ($request->filled('guru_id')) {
            $query->whereHas('jadwalPelajaran', function($q) use ($request) {
                $q->where('guru_id', $request->guru_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->query('per_page', 25);
        $monitorings = $query->latest('tanggal')->latest('id')->paginate($perPage);

        return response()->json($monitorings);
    }
    
    /**
     * Tampilkan detail monitoring
     */
    public function show(MonitoringKehadiranGuru $monitoring): JsonResponse
    {
        $monitoring->load(['jadwalPelajaran.kelas', 'jadwalPelajaran.guru', 'guruPengganti', 'pencatat']);
        return response()->json(['data' => $monitoring]);
    }

    /**
     * Hapus entri monitoring
     */
    public function destroy(MonitoringKehadiranGuru $monitoring, Request $request): JsonResponse
    {
        // Log penghapusan sebelum dihapus
        activity()
            ->performedOn($monitoring)
            ->causedBy($request->user())
            ->log('menghapus data monitoring guru');
            
        $monitoring->delete();

        return response()->json([
            'message' => 'Data monitoring berhasil dihapus.'
        ]);
    }
}

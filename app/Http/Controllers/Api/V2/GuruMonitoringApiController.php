<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\MonitoringKehadiranGuru;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuruMonitoringApiController extends Controller
{
    /**
     * Rekap kehadiran mengajar guru yang sedang login
     */
    public function me(Request $request): JsonResponse
    {
        $guruId = $request->user()->guru_id;
        
        if (!$guruId) {
            return response()->json([
                'message' => 'User tidak ditautkan dengan data Guru.',
            ], 403);
        }

        $bulan = $request->query('bulan', today()->month);
        $tahun = $request->query('tahun', today()->year);

        $monitorings = MonitoringKehadiranGuru::with(['jadwalPelajaran.kelas', 'guruPengganti'])
            ->whereHas('jadwalPelajaran', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->latest('tanggal')
            ->get();

        return response()->json(['data' => $monitorings]);
    }
}

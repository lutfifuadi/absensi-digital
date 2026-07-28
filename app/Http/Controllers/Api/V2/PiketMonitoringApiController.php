<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V2\Monitoring\StorePiketMonitoringRequest;
use App\Http\Requests\Api\V2\Monitoring\UpdatePiketMonitoringRequest;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\MonitoringKehadiranGuru;
use App\Services\Api\V2\Monitoring\MonitoringService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Facades\LogBatch;
use Spatie\Activitylog\Models\Activity;

class PiketMonitoringApiController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Ambil daftar jam pelajaran hari ini beserta status monitoring.
     */
    public function today(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all');
        
        $data = $this->monitoringService->getTodayMonitoringList($filter);
        
        return response()->json(['data' => $data]);
    }

    /**
     * Simpan data monitoring kehadiran guru.
     */
    public function store(StorePiketMonitoringRequest $request): JsonResponse
    {
        $today = Carbon::today();
        
        // Cek existing
        $existing = MonitoringKehadiranGuru::where('jadwal_pelajaran_id', $request->jadwal_pelajaran_id)
            ->whereDate('tanggal', $today)
            ->first();
            
        if ($existing) {
            return response()->json([
                'message' => 'Monitoring untuk kelas ini hari ini sudah pernah dicatat. Gunakan endpoint edit.',
                'existing_id' => $existing->id
            ], 409);
        }
        
        // Simpan
        $monitoring = new MonitoringKehadiranGuru($request->validated());
        $monitoring->tanggal = $today;
        $monitoring->dicatat_oleh = $request->user()->id;
        $monitoring->save();
        
        // Activity log
        activity()
            ->performedOn($monitoring)
            ->causedBy($request->user())
            ->log('mencatat kehadiran guru piket');

        return response()->json([
            'message' => 'Monitoring berhasil disimpan.',
            'data' => $monitoring
        ], 201);
    }

    /**
     * Update data monitoring kehadiran guru.
     */
    public function update(UpdatePiketMonitoringRequest $request, MonitoringKehadiranGuru $monitoring): JsonResponse
    {
        Gate::authorize('update', clone $monitoring);
        
        $monitoring->update($request->validated());
        
        // Activity log
        activity()
            ->performedOn($monitoring)
            ->causedBy($request->user())
            ->log('mengubah kehadiran guru piket');

        return response()->json([
            'message' => 'Monitoring berhasil diupdate.',
            'data' => $monitoring
        ]);
    }
    
    /**
     * Pencarian autocomplete guru.
     */
    public function guruSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);
        
        $gurus = Guru::where('nama', 'LIKE', "%{$request->q}%")
            ->select('id', 'nama')
            ->limit(10)
            ->get();
            
        return response()->json(['data' => $gurus]);
    }
}

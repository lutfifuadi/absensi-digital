<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\Request;

class PiketMonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $filter = $request->input('filter', 'all');

        $data = $this->monitoringService->getSchedulesWithMonitoring($tanggal, $filter);

        return view('piket.monitoring.index', compact('data', 'filter', 'tanggal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jadwal_pelajaran_id' => 'required|exists:jadwal_pelajaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,tidak_hadir,terlambat',
            'keterangan' => 'required_if:status,tidak_hadir|nullable|in:sakit,izin,dinas_luar,alfa',
            'lama_terlambat' => 'required_if:status,terlambat|nullable|integer|min:1|max:120',
            'keterangan_lain' => 'nullable|string|max:500',
            'guru_pengganti_id' => 'nullable|exists:guru,id',
            'guru_pengganti_nama' => 'nullable|string|max:191',
        ], [
            'keterangan.required_if' => 'Keterangan wajib diisi untuk status Tidak Hadir.',
            'lama_terlambat.required_if' => 'Lama keterlambatan wajib diisi untuk status Terlambat.',
        ]);

        try {
            $this->monitoringService->storeMonitoring($request->all(), auth()->id());

            return redirect()->back()->with('success', 'Monitoring kehadiran guru berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:hadir,tidak_hadir,terlambat',
            'keterangan' => 'required_if:status,tidak_hadir|nullable|in:sakit,izin,dinas_luar,alfa',
            'lama_terlambat' => 'required_if:status,terlambat|nullable|integer|min:1|max:120',
            'keterangan_lain' => 'nullable|string|max:500',
            'guru_pengganti_id' => 'nullable|exists:guru,id',
            'guru_pengganti_nama' => 'nullable|string|max:191',
        ], [
            'keterangan.required_if' => 'Keterangan wajib diisi untuk status Tidak Hadir.',
            'lama_terlambat.required_if' => 'Lama keterlambatan wajib diisi untuk status Terlambat.',
        ]);

        try {
            $this->monitoringService->updateMonitoring($id, $request->all(), auth()->user());

            return redirect()->back()->with('success', 'Data monitoring berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function searchGuru(Request $request)
    {
        $q = $request->input('q', '');
        $gurus = $this->monitoringService->searchGuru($q);

        return response()->json(['data' => $gurus]);
    }
}

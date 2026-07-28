<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MonitoringKehadiranGuru;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class AdminMonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request)
    {
        $filters = [
            'tanggal_dari' => $request->input('tanggal_dari', date('Y-m-d')),
            'tanggal_sampai' => $request->input('tanggal_sampai', date('Y-m-d')),
            'kelas_id' => $request->input('kelas_id'),
            'guru_id' => $request->input('guru_id'),
            'status' => $request->input('status'),
        ];

        $rekap = $this->monitoringService->getAdminRekap($filters);
        $kelases = Kelas::orderBy('nama_kelas')->get();
        $gurus = Guru::orderBy('nama')->get();

        return view('admin.monitoring.index', compact('rekap', 'filters', 'kelases', 'gurus'));
    }

    public function destroy($id)
    {
        $mon = MonitoringKehadiranGuru::findOrFail($id);
        $mon->delete();

        return redirect()->back()->with('success', 'Data monitoring berhasil dihapus.');
    }
}

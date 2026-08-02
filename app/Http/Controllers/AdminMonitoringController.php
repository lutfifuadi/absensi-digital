<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MonitoringKehadiranGuru;
use App\Services\KehadiranGuruService;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class AdminMonitoringController extends Controller
{
    protected MonitoringService $monitoringService;
    protected KehadiranGuruService $kehadiranGuruService;

    public function __construct(
        MonitoringService $monitoringService,
        KehadiranGuruService $kehadiranGuruService
    ) {
        $this->monitoringService = $monitoringService;
        $this->kehadiranGuruService = $kehadiranGuruService;
    }

    public function index(Request $request)
    {
        $filters = [
            'tanggal_dari' => $request->input('tanggal_dari', date('Y-m-d')),
            'tanggal_sampai' => $request->input('tanggal_sampai', date('Y-m-d')),
            'kelas_id' => $request->input('kelas_id'),
            'guru_id' => $request->input('guru_id'),
            'status' => $request->input('status'),
            'tipe_kepegawaian' => $request->input('tipe_kepegawaian'),
        ];

        $rekap = $this->monitoringService->getAdminRekap($filters);

        // ── PRD-007 (F-3): ringkasan slot per guru dalam rentang tanggal ──
        $rekapSlot = $this->kehadiranGuruService->getRekapSlotPerGuru($filters);

        $kelases = Kelas::orderBy('nama_kelas')->get();
        $gurus = Guru::orderBy('nama')->get();
        $tipeOptions = [
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
        ];

        return view('admin.monitoring.index', compact(
            'rekap',
            'rekapSlot',
            'filters',
            'kelases',
            'gurus',
            'tipeOptions'
        ));
    }

    public function destroy($id)
    {
        $mon = MonitoringKehadiranGuru::findOrFail($id);
        $mon->delete();

        return redirect()->back()->with('success', 'Data monitoring berhasil dihapus.');
    }
}

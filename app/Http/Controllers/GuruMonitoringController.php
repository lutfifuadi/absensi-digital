<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\Request;

class GuruMonitoringController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->guru) {
            return redirect()->back()->with('error', 'Profil guru Anda tidak ditemukan.');
        }

        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $rekap = $this->monitoringService->getGuruSelfRekap($user->guru->id, $bulan, $tahun);

        return view('guru.monitoring.index', compact('rekap', 'bulan', 'tahun'));
    }
}

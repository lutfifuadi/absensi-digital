<?php

namespace App\Http\Controllers;

use App\Services\KehadiranGuruService;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class GuruMonitoringController extends Controller
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
        $user = auth()->user();
        if (!$user->guru) {
            return redirect()->back()->with('error', 'Profil guru Anda tidak ditemukan.');
        }

        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $guru = $user->guru;
        $tipeKepegawaian = $guru->tipe_kepegawaian ?? 'full_time';
        $isPartTime = $this->kehadiranGuruService->isPartTime($guru->id);

        // ── PRD-007 (F-3): part time → rekap berbasis slot bulanan;
        //    full time → tetap seperti sekarang (record monitoring).
        if ($isPartTime) {
            $rekapSlot = $this->kehadiranGuruService->getSlotPartTimeBulanan($guru->id, $bulan, $tahun);

            return view('guru.monitoring.index', compact(
                'rekapSlot',
                'bulan',
                'tahun',
                'tipeKepegawaian',
                'isPartTime'
            ));
        }

        $rekap = $this->monitoringService->getGuruSelfRekap($guru->id, $bulan, $tahun);

        return view('guru.monitoring.index', compact(
            'rekap',
            'bulan',
            'tahun',
            'tipeKepegawaian',
            'isPartTime'
        ));
    }
}

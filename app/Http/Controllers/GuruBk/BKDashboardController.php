<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Services\BKDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class BKDashboardController extends Controller
{
    protected BKDashboardService $dashboardService;

    public function __construct(BKDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the Guru BK Dashboard.
     */
    public function index(Request $request)
    {
        try {
            $taId = $request->get('tahun_akademik_id');
            $dashboardData = $this->dashboardService->getDashboardData($taId);
            return view('guru-bk.dashboard', $dashboardData);
        } catch (Throwable $e) {
            Log::error('Error loading BK Dashboard: ' . $e->getMessage(), ['exception' => $e]);
            $fallbackData = [
                'tahunAkademik' => null,
                'topViolators' => collect(),
                'rekapKategori' => collect(),
                'spAktif' => collect(),
                'chartMonths' => [],
                'chartData' => [],
                'summary' => [
                    'totalPelanggaranBulanIni' => 0,
                    'totalSiswaBermasalah' => 0,
                    'totalSpDiterbitkan' => 0,
                ],
            ];
            return view('guru-bk.dashboard', $fallbackData);
        }
    }
}

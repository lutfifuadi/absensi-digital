<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Services\BKDashboardService;
use Illuminate\Http\Request;

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
        $taId = $request->get('tahun_akademik_id');
        $dashboardData = $this->dashboardService->getDashboardData($taId);

        return view('guru-bk.dashboard', $dashboardData);
    }
}

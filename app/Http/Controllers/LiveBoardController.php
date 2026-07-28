<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\Request;

class LiveBoardController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index(Request $request)
    {
        $jamFilter = $request->input('jam_filter', 'all');
        $liveData = $this->monitoringService->getLiveBoardData($jamFilter);

        return view('waka.live-board', compact('liveData', 'jamFilter'));
    }

    public function data(Request $request)
    {
        $jamFilter = $request->input('jam_filter', 'all');
        $liveData = $this->monitoringService->getLiveBoardData($jamFilter);

        return response()->json(['data' => $liveData]);
    }
}

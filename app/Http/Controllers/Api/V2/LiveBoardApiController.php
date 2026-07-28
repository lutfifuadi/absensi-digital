<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\Api\V2\Monitoring\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveBoardApiController extends Controller
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Ambil data live board - semua kelas, semua jam, hari ini
     */
    public function index(Request $request): JsonResponse
    {
        $jamFilter = $request->query('jam_filter', 'all');
        
        $data = $this->monitoringService->getLiveBoardData($jamFilter);
        
        return response()->json(['data' => $data]);
    }
}

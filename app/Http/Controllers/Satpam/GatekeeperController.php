<?php

namespace App\Http\Controllers\Satpam;

use App\Http\Controllers\Controller;
use App\Models\IzinPulangCepat;
use App\Services\IzinPulangCepatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatekeeperController extends Controller
{
    protected $izinService;

    public function __construct(IzinPulangCepatService $izinService)
    {
        $this->izinService = $izinService;
    }

    /**
     * Layar scanner gerbang satpam.
     */
    public function index()
    {
        return view('satpam.gatekeeper.index');
    }

    /**
     * Menerima input scanner (kode_izin / QR string / NIS / NIP) via AJAX.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string',
        ]);

        $result = $this->izinService->verifyPermission($request->input('query'));

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'is_valid' => $result['is_valid'],
            'status_label' => $result['status_label'],
            'status_message' => $result['status_message'],
            'data' => $result['data']
        ]);
    }

    /**
     * Confirm checkout tombol "BUKA GERBANG & CONFIRM KELUAR".
     */
    public function confirmCheckout(Request $request, IzinPulangCepat $izin)
    {
        $result = $this->izinService->processCheckout($izin);

        if (!$result['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }
            return back()->with('error', $result['message']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);
        }

        return back()->with('success', $result['message']);
    }
}

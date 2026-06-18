<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\Siswa;
use App\Services\EkskulAbsensiService;
use Illuminate\Http\Request;

class EkskulAbsensiScanController extends Controller
{
    public function __construct(
        private EkskulAbsensiService $absensiService
    ) {}

    /**
     * Proses scan QR absensi ekskul oleh siswa.
     *
     * POST /api/ekskul/absensi/scan/{token}
     *
     * Body: { nis: string }
     */
    public function scan(Request $request, string $token)
    {
        // 1. Verifikasi token
        $payload = $this->absensiService->verifyQRToken($token);

        if (!$payload) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid atau sudah kadaluarsa.',
            ], 422);
        }

        // 2. Validasi NIS
        $request->validate([
            'nis' => ['required', 'string', 'max:50'],
        ], [
            'nis.required' => 'NIS/NISN wajib diisi.',
        ]);

        // 3. Cari siswa berdasarkan NIS
        $siswa = Siswa::where('nis', $request->nis)->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa dengan NIS tersebut tidak ditemukan.',
            ], 404);
        }

        // 4. Validasi ekskul
        $ekskul = Ekskul::find($payload['ekskul_id']);

        if (!$ekskul || !$ekskul->status) {
            return response()->json([
                'success' => false,
                'message' => 'Ekskul tidak ditemukan atau sudah tidak aktif.',
            ], 404);
        }

        // 5. Catat absensi
        $result = $this->absensiService->recordScanAbsensi(
            $payload['ekskul_id'],
            $payload['tanggal'],
            $siswa->id
        );

        if (!$result['success']) {
            $statusCode = ($result['already'] ?? false) ? 409 : 422;
            return response()->json($result, $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil! Kamu tercatat hadir di ' . $ekskul->nama . '.',
            'data'    => [
                'ekskul' => $ekskul->nama,
                'siswa'  => [
                    'nama'  => $siswa->nama_lengkap,
                    'nis4'  => substr($siswa->nis, -4),
                    'kelas' => $siswa->kelas?->nama ?? '-',
                ],
                'status' => 'hadir',
                'jam'    => $result['jam'] ?? now()->format('H:i'),
                'tanggal' => $payload['tanggal'],
            ],
        ]);
    }
}

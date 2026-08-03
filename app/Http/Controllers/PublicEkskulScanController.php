<?php

namespace App\Http\Controllers;

use App\Models\Ekskul;
use App\Models\EkskulAbsensi;
use App\Models\EkskulAnggota;
use App\Models\Guru;
use App\Models\Siswa;
use App\Services\EkskulAbsensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class PublicEkskulScanController extends Controller
{
    public function __construct(
        private EkskulAbsensiService $absensiService
    ) {}

    /**
     * Tampilkan halaman autentikasi Passcode PIN untuk Pembina.
     */
    public function index(Request $request)
    {
        if (session('ekskul_pembina_authenticated') === true) {
            return redirect()->route('public.ekskul.scan.scanner');
        }

        return view('public.ekskul.login');
    }

    /**
     * Proses verifikasi Passcode PIN Pembina.
     */
    public function auth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Utamakan PIN khusus ekskul, jika belum diatur fallback ke password_unlock_scan_qr
        $storedValue = setting('password_unlock_scan_ekskul');
        if (empty($storedValue)) {
            $storedValue = setting('password_unlock_scan_qr');
        }

        $passwordToCheck = !empty($storedValue) ? $storedValue : 'kegiatan2026';

        $isValid = false;

        if (empty($storedValue)) {
            if ($request->password === 'kegiatan2026') {
                $isValid = true;
            }
        } else {
            if (strlen($passwordToCheck) === 60 && str_starts_with($passwordToCheck, '$2y$')) {
                if (Hash::check($request->password, $passwordToCheck)) {
                    $isValid = true;
                }
            } else {
                if ($request->password === $passwordToCheck) {
                    $isValid = true;
                }
            }
        }

        if ($isValid) {
            session(['ekskul_pembina_authenticated' => true]);
            return redirect()->route('public.ekskul.scan.scanner');
        }

        return back()->withErrors(['password' => 'Passcode PIN salah. Silakan coba lagi.'])->withInput();
    }

    /**
     * Tampilkan halaman antarmuka scanner kamera Pembina.
     */
    public function scanner(Request $request)
    {
        if (session('ekskul_pembina_authenticated') !== true) {
            return redirect()->route('public.ekskul.scan.index');
        }

        $ekskuls = Ekskul::where(function($q) {
            $q->where('status', 'aktif')
              ->orWhere('status', 1)
              ->orWhere('status', true);
        })->orderBy('nama')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('public.ekskul.scanner', compact('ekskuls', 'gurus'));
    }

    /**
     * Proses scanning absensi siswa oleh Pembina (AJAX JSON).
     */
    public function process(Request $request)
    {
        if (session('ekskul_pembina_authenticated') !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi autentikasi telah berakhir. Silakan masukkan PIN kembali.'
            ], 401);
        }

        $request->validate([
            'ekskul_id'  => 'required|exists:ekskul,id',
            'qr_code'    => 'required|string',
            'pembina_id' => 'nullable|exists:guru,id',
        ], [
            'ekskul_id.required' => 'Silakan pilih ekskul terlebih dahulu.',
            'qr_code.required'   => 'Kode QR / NIS/NISN siswa wajib diisi.',
        ]);

        $code = trim($request->qr_code);

        // Cari siswa berdasarkan qr_code, nis, atau nisn
        $siswa = Siswa::with('kelas:id,nama,tingkat')
            ->where(function ($q) use ($code) {
                $q->where('qr_code', $code)
                  ->orWhere('nisn', $code)
                  ->orWhere('nis', $code);
            })
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => "Kartu/Siswa dengan NIS/QR '{$code}' tidak ditemukan!"
            ], 404);
        }

        $ekskul = Ekskul::find($request->ekskul_id);
        if (!$ekskul || !$ekskul->status) {
            return response()->json([
                'success' => false,
                'message' => 'Ekskul tidak ditemukan atau sudah tidak aktif.'
            ], 422);
        }

        $tanggal = now()->toDateString();
        $pembinaId = $request->pembina_id ? (int) $request->pembina_id : null;

        // Gunakan service untuk mencatat absensi
        $result = $this->absensiService->recordScanAbsensi(
            (int) $ekskul->id,
            $tanggal,
            $siswa->id,
            $pembinaId
        );

        if (!$result['success']) {
            $statusCode = ($result['already'] ?? false) ? 409 : 422;
            return response()->json([
                'success' => false,
                'already' => $result['already'] ?? false,
                'message' => $result['message'],
                'data'    => [
                    'siswa'  => [
                        'nama'  => $siswa->nama_lengkap,
                        'nis'   => $siswa->nis,
                        'kelas' => $siswa->kelas?->nama ?? '-',
                        'foto'  => $siswa->foto ? asset('storage/' . $siswa->foto) : null,
                    ],
                    'ekskul' => $ekskul->nama,
                    'status' => $result['status'] ?? 'tercatat',
                ]
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => "{$siswa->nama_lengkap} berhasil dicatat HADIR di {$ekskul->nama}.",
            'data'    => [
                'siswa' => [
                    'id'    => $siswa->id,
                    'nama'  => $siswa->nama_lengkap,
                    'nis'   => $siswa->nis,
                    'kelas' => $siswa->kelas?->nama ?? '-',
                    'foto'  => $siswa->foto ? asset('storage/' . $siswa->foto) : null,
                ],
                'ekskul'  => $ekskul->nama,
                'status'  => 'hadir',
                'jam'     => $result['jam'] ?? now()->format('H:i'),
                'tanggal' => $tanggal,
            ]
        ]);
    }

    /**
     * Logout dari sesi scan pembina.
     */
    public function logout()
    {
        session()->forget('ekskul_pembina_authenticated');
        return redirect()->route('public.ekskul.scan.index');
    }
}

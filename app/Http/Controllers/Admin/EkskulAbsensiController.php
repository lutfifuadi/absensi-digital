<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EkskulAbsensiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\EkskulAbsensiRequest;
use App\Models\Guru;
use App\Services\EkskulAbsensiService;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class EkskulAbsensiController extends Controller
{
    public function __construct(
        private EkskulAbsensiService $absensiService
    ) {}

    /**
     * Halaman pilih tanggal untuk absensi ekskul.
     */
    public function index($ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        return view('admin.ekskul.absensi-index', compact('ekskul'));
    }

    /**
     * Form absensi per tanggal — tampilkan daftar anggota dengan status absensi.
     */
    public function show(Request $request, $ekskulId, $tanggal = null)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);
        $tanggal = $tanggal ?? now()->toDateString();

        // Ambil semua anggota aktif
        $anggota = EkskulAnggota::with('siswa:id,nama_lengkap,nis,kelas_id')
            ->where('ekskul_id', $ekskulId)
            ->where('status', 'aktif')
            ->get();

        // Ambil absensi yang sudah ada di tanggal tsb
        $absensiHariIni = $this->absensiService->getAbsensiPerTanggal($ekskulId, $tanggal)
            ->keyBy('siswa_id');

        return view('admin.ekskul.absensi-form', compact(
            'ekskul', 'tanggal', 'anggota', 'absensiHariIni'
        ));
    }

    /**
     * Simpan data absensi untuk satu tanggal.
     */
    public function store(EkskulAbsensiRequest $request, $ekskulId, $tanggal)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        try {
            $pembinaId = $request->pembina_id;

            if (!$pembinaId && auth()->user()->isRole(\App\Models\User::ROLE_GURU)) {
                $guru = Guru::where('user_id', auth()->id())->first();
                $pembinaId = $guru ? $guru->id : null;
            }

            // Fallback untuk non-guru (admin/operator) — gunakan user_id
            if (!$pembinaId) {
                $pembinaId = auth()->id();
            }

            $saved = $this->absensiService->simpanAbsensi(
                $ekskulId,
                $tanggal,
                $request->validated(),
                $pembinaId
            );

            return redirect()
                ->route('admin.ekskul.absensi.show', [$ekskulId, $tanggal])
                ->with('success', "Absensi berhasil disimpan ({$saved} data).");
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan absensi ekskul', [
                'ekskul_id' => $ekskulId,
                'tanggal'   => $tanggal,
                'error'     => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan absensi. Silakan coba lagi.');
        }
    }

    /**
     * Halaman rekap absensi bulanan.
     */
    public function rekap(Request $request, $ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        $bulan = $request->input('bulan', now()->month);
        $tahun = $request->input('tahun', now()->year);

        $rekap = $this->absensiService->getRekap($ekskulId, (int) $bulan, (int) $tahun);
        $rekapPerSiswa = $this->absensiService->getRekapPerSiswa($ekskulId, (int) $bulan, (int) $tahun);

        return view('admin.ekskul.absensi-rekap', array_merge(
            compact('ekskul', 'bulan', 'tahun', 'rekapPerSiswa'),
            $rekap
        ));
    }

    /**
     * Export rekap absensi ke Excel (.xlsx).
     */
    public function exportExcel(Request $request, $ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);
        $bulan  = (int) $request->input('bulan', now()->month);
        $tahun  = (int) $request->input('tahun', now()->year);

        $filename = sprintf(
            'rekap-absensi-%s-%04d-%02d.xlsx',
            \Illuminate\Support\Str::slug($ekskul->nama),
            $tahun,
            $bulan
        );

        return Excel::download(
            new EkskulAbsensiExport($ekskulId, $ekskul->nama, $bulan, $tahun),
            $filename
        );
    }

    /**
     * Export rekap absensi ke PDF.
     */
    public function exportPdf(Request $request, $ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);
        $bulan  = (int) $request->input('bulan', now()->month);
        $tahun  = (int) $request->input('tahun', now()->year);

        $rekapPerSiswa = $this->absensiService->getRekapPerSiswa($ekskulId, $bulan, $tahun);
        $rekap = $this->absensiService->getRekap($ekskulId, $bulan, $tahun);

        $namaBulan = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');

        $pdf = Pdf::loadView('admin.ekskul.absensi-rekap-pdf', compact(
            'ekskul', 'bulan', 'tahun', 'namaBulan', 'rekapPerSiswa', 'rekap'
        ))->setPaper('a4', 'landscape');

        $filename = sprintf(
            'rekap-absensi-%s-%04d-%02d.pdf',
            \Illuminate\Support\Str::slug($ekskul->nama),
            $tahun,
            $bulan
        );

        return $pdf->download($filename);
    }

    /**
     * Generate QR Code token untuk absensi hari ini.
     */
    public function generateQR($ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        try {
            $tanggal = request()->input('tanggal', now()->toDateString());
            $qrData = $this->absensiService->generateQRCode($ekskulId, $tanggal);

            return response()->json([
                'success' => true,
                'data'    => $qrData,
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal generate QR ekskul', [
                'ekskul_id' => $ekskulId,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate QR. Silakan coba lagi.',
            ], 500);
        }
    }
}

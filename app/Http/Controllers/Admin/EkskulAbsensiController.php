<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EkskulAbsensiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\EkskulAbsensiRequest;
use App\Models\Guru;
use App\Services\EkskulAbsensiService;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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

            // Generate QR code image secara lokal menggunakan endroid/qr-code
            // Library ini sudah terinstall di composer.json
            $qrImage = null;
            try {
                $qrCode = new QrCode(
                    data: $qrData['url'],
                    size: 200,
                );
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrImage = 'data:image/png;base64,' . base64_encode($result->getString());
            } catch (\Exception $e) {
                Log::warning('Gagal generate QR image via endroid, fallback ke eksternal', [
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success'  => true,
                'data'     => $qrData,
                'qr_image' => $qrImage,
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

    /**
     * Cari siswa berdasarkan NIS (untuk admin scan).
     *
     * POST /admin/ekskul/{ekskul}/absensi/lookup-siswa
     * Body: { nis: string }
     */
    public function lookupSiswa(Request $request, $ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        $request->validate([
            'nis' => ['required', 'string', 'max:50'],
        ], [
            'nis.required' => 'NIS/NISN wajib diisi.',
        ]);

        $siswa = Siswa::with('kelas:id,nama')
            ->where('nis', $request->nis)
            ->first();

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa dengan NIS tersebut tidak ditemukan.',
            ], 404);
        }

        // Cek apakah siswa sudah terdaftar sebagai anggota aktif
        $anggota = EkskulAnggota::where('ekskul_id', $ekskulId)
            ->where('siswa_id', $siswa->id)
            ->where('status', 'aktif')
            ->exists();

        return response()->json([
            'success' => true,
            'data'    => [
                'siswa' => [
                    'id'           => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nis'          => $siswa->nis,
                    'kelas'        => $siswa->kelas?->nama ?? '-',
                ],
                'is_anggota' => $anggota,
                'ekskul'     => $ekskul->nama,
            ],
        ]);
    }

    /**
     * Catat absensi siswa oleh admin via scan QR atau manual NIS.
     *
     * POST /admin/ekskul/{ekskul}/absensi/admin-scan
     * Body: { siswa_id: int, tanggal?: string }
     */
    public function adminScan(Request $request, $ekskulId)
    {
        $ekskul = Ekskul::findOrFail($ekskulId);
        $this->authorize('absensiAccess', $ekskul);

        $request->validate([
            'siswa_id' => ['required', 'integer', 'exists:siswa,id'],
        ]);

        $siswa = Siswa::with('kelas:id,nama')->find($request->siswa_id);

        // Tentukan pembina
        $pembinaId = null;
        if (auth()->user()->isRole(\App\Models\User::ROLE_GURU)) {
            $guru = Guru::where('user_id', auth()->id())->first();
            $pembinaId = $guru ? $guru->id : null;
        }

        $tanggal = $request->input('tanggal', now()->toDateString());

        // Catat absensi
        $result = $this->absensiService->recordScanAbsensi(
            (int) $ekskulId,
            $tanggal,
            $siswa->id,
            $pembinaId
        );

        if (!$result['success']) {
            $statusCode = ($result['already'] ?? false) ? 409 : 422;
            return response()->json($result, $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => $siswa->nama_lengkap . ' tercatat hadir di ' . $ekskul->nama . '.',
            'data'    => [
                'ekskul'  => $ekskul->nama,
                'siswa'   => [
                    'nama'  => $siswa->nama_lengkap,
                    'nis'   => $siswa->nis,
                    'kelas' => $siswa->kelas?->nama ?? '-',
                ],
                'status'  => 'hadir',
                'jam'     => $result['jam'] ?? now()->format('H:i'),
                'tanggal' => $tanggal,
            ],
        ]);
    }
}

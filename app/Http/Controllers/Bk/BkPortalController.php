<?php

namespace App\Http\Controllers\Bk;

use App\Http\Controllers\Controller;
use App\Models\BkKasus;
use App\Models\BkLogKonseling;
use App\Models\Guru;
use App\Models\PelanggaranPemutihanLog;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Services\BkKomdisService;
use App\Services\PoinPelanggaranService;
use Illuminate\Http\Request;
use Exception;

class BkPortalController extends Controller
{
    protected BkKomdisService $bkKomdisService;
    protected PoinPelanggaranService $poinService;

    public function __construct(BkKomdisService $bkKomdisService, PoinPelanggaranService $poinService)
    {
        $this->bkKomdisService = $bkKomdisService;
        $this->poinService = $poinService;
    }

    /**
     * Helper untuk mendapatkan record Guru BK pengakses saat ini
     */
    protected function getGuruBk()
    {
        $user = auth()->user();
        if ($user->guru) {
            return $user->guru;
        }

        return Guru::where('user_id', $user->id)->first();
    }

    /**
     * Dashboard Portal Guru BK
     */
    public function dashboard()
    {
        $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
        $guruBk = $this->getGuruBk();

        $totalKasus = BkKasus::count();
        $kasusTerbuka = BkKasus::where('status', 'terbuka')->count();
        $kasusProses = BkKasus::where('status', 'dalam_proses')->count();
        $kasusSelesai = BkKasus::where('status', 'selesai')->count();

        $totalKonselingBulanIni = BkLogKonseling::whereMonth('tanggal_konseling', now()->month)
            ->whereYear('tanggal_konseling', now()->year)
            ->count();

        $kasusTerbaru = BkKasus::with(['siswa.kelas', 'konselor'])
            ->latest('tanggal_lapor')
            ->take(5)
            ->get();

        $logKonselingTerbaru = BkLogKonseling::with(['siswa.kelas', 'konselor'])
            ->latest('tanggal_konseling')
            ->take(5)
            ->get();

        return view('bk.dashboard', compact(
            'totalKasus',
            'kasusTerbuka',
            'kasusProses',
            'kasusSelesai',
            'totalKonselingBulanIni',
            'kasusTerbaru',
            'logKonselingTerbaru'
        ));
    }

    /**
     * Manajemen Kasus BK di Portal BK Guru
     */
    public function kasus(Request $request)
    {
        $query = BkKasus::with(['siswa.kelas', 'konselor', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $kasusList = $query->latest('tanggal_lapor')->paginate(15)->withQueryString();
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $guruBk = $this->getGuruBk();

        return view('bk.kasus', compact('kasusList', 'siswas', 'guruBk'));
    }

    /**
     * Simpan Kasus Baru dari Portal BK
     */
    public function storeKasus(Request $request)
    {
        $guruBk = $this->getGuruBk();

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'judul_kasus' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:pribadi,sosial,belajar,karir,disiplin',
            'tingkat_keparahan' => 'required|in:ringan,sedang,berat,sangat_berat',
            'tanggal_lapor' => 'required|date',
        ]);

        try {
            $validated['guru_bk_id'] = $guruBk?->id;
            $validated['status'] = 'terbuka';
            $validated['created_by'] = auth()->id();

            BkKasus::create($validated);

            return redirect()->route('bk.kasus')
                ->with('success', 'Kasus BK baru berhasil didaftarkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mendaftarkan kasus: ' . $e->getMessage());
        }
    }

    /**
     * Log / Jurnal Konseling di Portal BK Guru
     */
    public function konseling(Request $request)
    {
        $guruBk = $this->getGuruBk();
        $query = BkLogKonseling::with(['kasus', 'siswa.kelas', 'konselor']);

        if ($guruBk) {
            $query->where('guru_bk_id', $guruBk->id);
        }

        if ($request->filled('jenis_konseling')) {
            $query->where('jenis_konseling', $request->input('jenis_konseling'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('tanggal_konseling')->paginate(15)->withQueryString();
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $kasuses = BkKasus::whereIn('status', ['terbuka', 'dalam_proses'])->get();

        return view('bk.log-konseling', compact('logs', 'siswas', 'kasuses', 'guruBk'));
    }

    /**
     * Simpan Jurnal Konseling dari Portal BK
     */
    public function storeKonseling(Request $request)
    {
        $guruBk = $this->getGuruBk();

        $validated = $request->validate([
            'bk_kasus_id' => 'nullable|exists:bk_kasus,id',
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_konseling' => 'required|date',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'jenis_konseling' => 'required|in:individu,kelompok,karir,kunjungan_rumah',
            'ringkasan_masalah' => 'required|string',
            'hasil_konseling' => 'nullable|string',
            'rencana_tindak_lanjut' => 'nullable|string',
            'status_tindak_lanjut' => 'required|in:belum,proses,selesai',
        ]);

        try {
            $validated['guru_bk_id'] = $guruBk?->id;
            $this->bkKomdisService->tambahLogKonseling($validated);

            return redirect()->route('bk.log-konseling')
                ->with('success', 'Log konseling berhasil disimpan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan log konseling: ' . $e->getMessage());
        }
    }

    /**
     * Pemutihan Poin Pelanggaran di Portal BK Guru
     */
    public function pemutihan(Request $request)
    {
        $query = PelanggaranPemutihanLog::with(['siswa.kelas', 'diprosesOleh']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('tanggal_pemutihan')->paginate(15)->withQueryString();
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();

        return view('bk.pemutihan', compact('logs', 'siswas'));
    }

    /**
     * Eksekusi Pemutihan Poin dari Portal BK
     */
    public function storePemutihan(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_pemutihan' => 'required|date',
            'poin_yang_diputihkan' => 'required|integer|min:1',
            'alasan_pemutihan' => 'required|string',
            'arsipkan_pelanggaran' => 'nullable|boolean',
        ]);

        try {
            $this->bkKomdisService->eksekusiPemutihan($validated);

            return redirect()->route('bk.pemutihan')
                ->with('success', 'Pemutihan poin siswa berhasil diproses.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pemutihan: ' . $e->getMessage());
        }
    }
}

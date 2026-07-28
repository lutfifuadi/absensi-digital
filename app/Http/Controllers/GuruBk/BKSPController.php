<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSPBKRequest;
use App\Models\Kelas;
use App\Models\PelanggaranSp;
use App\Models\Siswa;
use App\Services\BKSPService;
use App\Services\PoinPelanggaranService;
use Illuminate\Http\Request;
use Exception;

class BKSPController extends Controller
{
    protected BKSPService $spService;
    protected PoinPelanggaranService $poinService;

    public function __construct(BKSPService $spService, PoinPelanggaranService $poinService)
    {
        $this->spService = $spService;
        $this->poinService = $poinService;
    }

    /**
     * Display list of formal SP issuance.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['siswa_id', 'level_sp', 'kelas_id', 'search']);
        $spList = $this->spService->getSpList($filters);
        $kelases = Kelas::orderBy('nama')->get();

        return view('guru-bk.sp.index', compact('spList', 'kelases', 'filters'));
    }

    /**
     * Show form for issuing SP.
     */
    public function create(Request $request)
    {
        $siswas = Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $selectedSiswaId = $request->get('siswa_id');
        $selectedSiswa = null;
        $riwayatPelanggaran = collect();
        $totalPoin = 0;

        if ($selectedSiswaId) {
            $selectedSiswa = Siswa::with('kelas')->find($selectedSiswaId);
            if ($selectedSiswa) {
                $riwayatPelanggaran = $selectedSiswa->pelanggaran()->with('jenisPelanggaran.kategori')->latest('tanggal_kejadian')->get();
                $totalPoin = $riwayatPelanggaran->sum('poin_saat_itu');
            }
        }

        return view('guru-bk.sp.create', compact('siswas', 'selectedSiswa', 'selectedSiswaId', 'riwayatPelanggaran', 'totalPoin'));
    }

    /**
     * Store issued SP.
     */
    public function store(StoreSPBKRequest $request)
    {
        try {
            $sp = $this->spService->issueSp($request->validated());

            return redirect()->route('bk.sp.index')
                ->with('success', "Surat Peringatan {$sp->level_sp} berhasil diterbitkan!");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menerbitkan SP: ' . $e->getMessage());
        }
    }
}

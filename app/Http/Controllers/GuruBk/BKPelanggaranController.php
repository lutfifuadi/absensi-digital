<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePelanggaranBKRequest;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Services\BKPelanggaranService;
use Illuminate\Http\Request;
use Exception;

class BKPelanggaranController extends Controller
{
    protected BKPelanggaranService $pelanggaranService;

    public function __construct(BKPelanggaranService $pelanggaranService)
    {
        $this->pelanggaranService = $pelanggaranService;
    }

    /**
     * Display violation records across classes.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['siswa_id', 'kelas_id', 'kategori_id', 'tanggal_mulai', 'tanggal_selesai', 'search']);
        $pelanggaran = $this->pelanggaranService->getPelanggaranList($filters);
        
        $kelases = Kelas::orderBy('nama')->get();
        $kategories = KategoriPelanggaran::orderBy('nama')->get();

        return view('guru-bk.pelanggaran.index', compact('pelanggaran', 'kelases', 'kategories', 'filters'));
    }

    /**
     * Show form for logging violations across classes.
     */
    public function create(Request $request)
    {
        $siswas = Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $jenisList = JenisPelanggaran::with('kategori')->orderBy('nama')->get();
        $kategories = KategoriPelanggaran::orderBy('nama')->get();
        $selectedSiswaId = $request->get('siswa_id');

        return view('guru-bk.pelanggaran.create', compact('siswas', 'jenisList', 'kategories', 'selectedSiswaId'));
    }

    /**
     * Store violation record.
     */
    public function store(StorePelanggaranBKRequest $request)
    {
        try {
            $buktiFoto = $request->file('bukti_foto');
            $pelanggaran = $this->pelanggaranService->storePelanggaran($request->validated(), $buktiFoto);

            return redirect()->route('bk.pelanggaran.index')
                ->with('success', 'Pelanggaran siswa berhasil dicatat!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mencatat pelanggaran: ' . $e->getMessage());
        }
    }

    /**
     * Display student violation history detail.
     */
    public function show($id)
    {
        $pelanggaran = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'pencatat', 'fotos', 'tahunAkademik'])->findOrFail($id);

        return view('guru-bk.pelanggaran.show', compact('pelanggaran'));
    }
}

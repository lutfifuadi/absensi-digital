<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkKasus;
use App\Models\Guru;
use App\Models\Siswa;
use App\Services\BkKomdisService;
use Illuminate\Http\Request;
use Exception;

class BkKasusController extends Controller
{
    protected BkKomdisService $bkKomdisService;

    public function __construct(BkKomdisService $bkKomdisService)
    {
        $this->bkKomdisService = $bkKomdisService;
    }

    public function index(Request $request)
    {
        $query = BkKasus::with(['siswa.kelas', 'konselor', 'creator']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        if ($request->filled('tingkat_keparahan')) {
            $query->where('tingkat_keparahan', $request->input('tingkat_keparahan'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $kasusList = $query->latest('tanggal_lapor')->paginate(15)->withQueryString();
        
        $stats = [
            'total'    => BkKasus::count(),
            'proses'   => BkKasus::where('status', 'dalam_proses')->count(),
            'eskalasi' => BkKasus::where('status', 'eskalasi_komdis')->count(),
            'selesai'  => BkKasus::where('status', 'selesai')->count(),
        ];

        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('admin.bk-kasus.index', compact('kasusList', 'stats', 'siswas', 'gurus'));
    }

    public function store(Request $request)
    {
        $siswaInput = $request->input('siswa_id');
        $siswaIds = is_array($siswaInput) ? array_filter($siswaInput) : ($siswaInput ? [$siswaInput] : []);

        $request->merge(['siswa_ids' => $siswaIds]);

        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'guru_bk_id' => 'nullable|exists:guru,id',
            'judul_kasus' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:pribadi,sosial,belajar,karir,disiplin,akademik',
            'tingkat_keparahan' => 'required|in:rendah,sedang,tinggi',
            'tanggal_lapor' => 'required|date',
        ]);

        try {
            foreach ($validated['siswa_ids'] as $sId) {
                $kasusData = [
                    'siswa_id' => $sId,
                    'guru_bk_id' => $validated['guru_bk_id'] ?? null,
                    'judul_kasus' => $validated['judul_kasus'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'kategori' => $validated['kategori'],
                    'tingkat_keparahan' => $validated['tingkat_keparahan'],
                    'tanggal_lapor' => $validated['tanggal_lapor'],
                ];
                $this->bkKomdisService->tambahKasusBk($kasusData);
            }

            return redirect()->route('admin.bk-kasus.index')
                ->with('success', count($validated['siswa_ids']) . ' Kasus BK berhasil dicatat.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mencatat kasus: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $kasus = BkKasus::with(['siswa.kelas', 'konselor', 'creator', 'logKonseling.konselor', 'sidang.pimpinanSidang'])
            ->findOrFail($id);

        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('admin.bk-kasus.show', compact('kasus', 'gurus'));
    }

    public function update(Request $request, $id)
    {
        $kasus = BkKasus::findOrFail($id);

        $validated = $request->validate([
            'guru_bk_id' => 'nullable|exists:guru,id',
            'judul_kasus' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:pribadi,sosial,belajar,karir,disiplin',
            'tingkat_keparahan' => 'required|in:ringan,sedang,berat,sangat_berat',
            'status' => 'required|in:terbuka,dalam_proses,selesai,dirujuk',
            'tanggal_lapor' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_lapor',
        ]);

        try {
            $kasus->update($validated);

            return redirect()->route('admin.bk-kasus.show', $kasus->id)
                ->with('success', 'Kasus BK berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui kasus BK: ' . $e->getMessage());
        }
    }

    public function eskalasiKomdis(Request $request, $id)
    {
        $validated = $request->validate([
            'tanggal_sidang' => 'required|date',
            'waktu_sidang' => 'nullable',
            'lokasi_sidang' => 'nullable|string|max:255',
            'agenda' => 'required|string|max:255',
            'deskripsi_pelanggaran' => 'required|string',
            'pimpinan_sidang_id' => 'nullable|exists:guru,id',
        ]);

        try {
            $sidang = $this->bkKomdisService->eskalasiKasusKeKomdis($id, $validated);

            return redirect()->route('admin.komdis-sidang.show', $sidang->id)
                ->with('success', 'Kasus BK berhasil dieskalasi ke Sidang Komdis.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal mengeskalasi kasus: ' . $e->getMessage());
        }
    }
}

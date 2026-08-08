<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkKasus;
use App\Models\BkLogKonseling;
use App\Models\Guru;
use App\Models\Siswa;
use App\Services\BkKomdisService;
use Illuminate\Http\Request;
use Exception;

class BkLogKonselingController extends Controller
{
    protected BkKomdisService $bkKomdisService;

    public function __construct(BkKomdisService $bkKomdisService)
    {
        $this->bkKomdisService = $bkKomdisService;
    }

    public function index(Request $request)
    {
        $query = BkLogKonseling::with(['kasus', 'siswa.kelas', 'konselor', 'creator']);

        if ($request->filled('jenis_konseling')) {
            $query->where('jenis_konseling', $request->input('jenis_konseling'));
        }

        if ($request->filled('status_tindak_lanjut')) {
            $query->where('status_tindak_lanjut', $request->input('status_tindak_lanjut'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('tanggal_konseling')->paginate(15)->withQueryString();
        
        $stats = [
            'total'     => BkLogKonseling::count(),
            'individu'  => BkLogKonseling::where('jenis_konseling', 'individu')->count(),
            'kelompok'  => BkLogKonseling::where('jenis_konseling', 'kelompok')->count(),
            'bulan_ini' => BkLogKonseling::whereMonth('tanggal_konseling', now()->month)->count(),
        ];

        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        $kasuses = BkKasus::whereIn('status', ['terbuka', 'dalam_proses'])->get();

        return view('admin.bk-log-konseling.index', compact('logs', 'stats', 'siswas', 'gurus', 'kasuses'));
    }

    public function store(Request $request)
    {
        $siswaInput = $request->input('siswa_id');
        $siswaIds = is_array($siswaInput) ? array_filter($siswaInput) : ($siswaInput ? [$siswaInput] : []);

        $request->merge(['siswa_ids' => $siswaIds]);

        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'guru_bk_id' => 'required|exists:guru,id',
            'tanggal_konseling' => 'required|date',
            'jenis_konseling' => 'required|in:individu,kelompok,karir,kunjungan_rumah',
            'ringkasan_masalah' => 'required|string',
            'hasil_konseling' => 'nullable|string',
            'rencana_tindak_lanjut' => 'nullable|string',
            'status_tindak_lanjut' => 'nullable|in:belum,proses,selesai',
        ]);

        try {
            foreach ($validated['siswa_ids'] as $sId) {
                $logData = [
                    'siswa_id' => $sId,
                    'guru_bk_id' => $validated['guru_bk_id'],
                    'tanggal_konseling' => $validated['tanggal_konseling'],
                    'jenis_konseling' => $validated['jenis_konseling'],
                    'ringkasan_masalah' => $validated['ringkasan_masalah'],
                    'hasil_konseling' => $validated['hasil_konseling'] ?? null,
                    'rencana_tindak_lanjut' => $validated['rencana_tindak_lanjut'] ?? null,
                    'status_tindak_lanjut' => $validated['status_tindak_lanjut'] ?? 'belum',
                ];
                $this->bkKomdisService->tambahLogKonseling($logData);
            }

            return redirect()->route('admin.bk-log-konseling.index')
                ->with('success', count($validated['siswa_ids']) . ' Jurnal konseling berhasil ditambahkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan jurnal konseling: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $log = BkLogKonseling::findOrFail($id);
            $log->delete();

            return redirect()->route('admin.bk-log-konseling.index')
                ->with('success', 'Jurnal konseling berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus jurnal konseling: ' . $e->getMessage());
        }
    }
}

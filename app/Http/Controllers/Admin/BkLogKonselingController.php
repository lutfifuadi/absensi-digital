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
            'privat'    => BkLogKonseling::where('is_privat', true)->count(),
            'publik'    => BkLogKonseling::where('is_privat', false)->count(),
            'bulan_ini' => BkLogKonseling::whereMonth('tanggal_konseling', now()->month)->count(),
        ];

        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        $kasuses = BkKasus::whereIn('status', ['terbuka', 'dalam_proses'])->get();

        return view('admin.bk-log-konseling.index', compact('logs', 'stats', 'siswas', 'gurus', 'kasuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bk_kasus_id' => 'nullable|exists:bk_kasus,id',
            'siswa_id' => 'required|exists:siswa,id',
            'guru_bk_id' => 'required|exists:guru,id',
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
            $this->bkKomdisService->tambahLogKonseling($validated);

            return redirect()->route('admin.bk-log-konseling.index')
                ->with('success', 'Jurnal konseling berhasil ditambahkan.');
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

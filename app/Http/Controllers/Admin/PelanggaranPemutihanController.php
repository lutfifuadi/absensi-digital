<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PelanggaranPemutihanLog;
use App\Models\Siswa;
use App\Services\BkKomdisService;
use Illuminate\Http\Request;
use Exception;

class PelanggaranPemutihanController extends Controller
{
    protected BkKomdisService $bkKomdisService;

    public function __construct(BkKomdisService $bkKomdisService)
    {
        $this->bkKomdisService = $bkKomdisService;
    }

    public function index(Request $request)
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

        return view('admin.bk-pemutihan.index', compact('logs', 'siswas'));
    }

    public function store(Request $request)
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

            return redirect()->route('admin.bk-pemutihan.index')
                ->with('success', 'Eksekusi pemutihan poin berhasil diselesaikan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pemutihan: ' . $e->getMessage());
        }
    }
}

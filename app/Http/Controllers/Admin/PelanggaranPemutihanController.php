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
        
        $stats = [
            'total_poin'  => PelanggaranPemutihanLog::sum('poin_yang_diputihkan'),
            'siswa_count' => PelanggaranPemutihanLog::distinct('siswa_id')->count('siswa_id'),
            'total_log'   => PelanggaranPemutihanLog::count(),
        ];

        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();

        return view('admin.bk-pemutihan.index', compact('logs', 'stats', 'siswas'));
    }

    public function store(Request $request)
    {
        $siswaInput = $request->input('siswa_id');
        $siswaIds = is_array($siswaInput) ? array_filter($siswaInput) : ($siswaInput ? [$siswaInput] : []);

        $request->merge(['siswa_ids' => $siswaIds]);

        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'tanggal_pemutihan' => 'required|date',
            'poin_yang_diputihkan' => 'required|integer|min:1',
            'alasan_pemutihan' => 'required|string',
            'arsipkan_pelanggaran' => 'nullable|boolean',
        ]);

        try {
            foreach ($validated['siswa_ids'] as $sId) {
                $pemutihanData = [
                    'siswa_id' => $sId,
                    'tanggal_pemutihan' => $validated['tanggal_pemutihan'],
                    'poin_yang_diputihkan' => $validated['poin_yang_diputihkan'],
                    'alasan_pemutihan' => $validated['alasan_pemutihan'],
                    'arsipkan_pelanggaran' => $validated['arsipkan_pelanggaran'] ?? false,
                ];
                $this->bkKomdisService->eksekusiPemutihan($pemutihanData);
            }

            return redirect()->route('admin.bk-pemutihan.index')
                ->with('success', 'Eksekusi pemutihan poin berhasil diselesaikan untuk ' . count($validated['siswa_ids']) . ' siswa.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses pemutihan: ' . $e->getMessage());
        }
    }
}

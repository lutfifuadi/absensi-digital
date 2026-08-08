<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\KomdisSanksi;
use App\Models\KomdisSidang;
use App\Models\Siswa;
use App\Services\BkKomdisService;
use Illuminate\Http\Request;
use Exception;

class KomdisSanksiController extends Controller
{
    protected BkKomdisService $bkKomdisService;

    public function __construct(BkKomdisService $bkKomdisService)
    {
        $this->bkKomdisService = $bkKomdisService;
    }

    public function index(Request $request)
    {
        $query = KomdisSanksi::with(['siswa.kelas', 'sidang', 'diberikanOleh']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $sanksis = $query->latest('tanggal_mulai')->paginate(15)->withQueryString();

        $stats = [
            'sanksi_aktif' => KomdisSanksi::where('status', 'aktif')->count(),
            'sp1_aktif'    => KomdisSanksi::where('status', 'aktif')->where('nama_sanksi', 'like', '%SP 1%')->count(),
            'sp23_aktif'   => KomdisSanksi::where('status', 'aktif')->where(function($q) {
                $q->where('nama_sanksi', 'like', '%SP 2%')->orWhere('nama_sanksi', 'like', '%SP 3%');
            })->count(),
            'sanksi_selesai' => KomdisSanksi::where('status', 'selesai')->count(),
        ];

        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        $sidangs = KomdisSidang::where('status', 'selesai')->get();

        return view('admin.komdis-sanksi.index', compact('sanksis', 'stats', 'siswas', 'gurus', 'sidangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'komdis_sidang_id' => 'nullable|exists:komdis_sidang,id',
            'nama_sanksi' => 'required|string|max:255',
            'deskripsi_sanksi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,selesai,dibatalkan',
            'diberikan_oleh' => 'nullable|exists:guru,id',
        ]);

        try {
            $this->bkKomdisService->tetapkanSanksi($validated);

            return redirect()->route('admin.komdis-sanksi.index')
                ->with('success', 'Sanksi Komdis berhasil ditetapkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menetapkan sanksi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $sanksi = KomdisSanksi::findOrFail($id);

        $validated = $request->validate([
            'nama_sanksi' => 'required|string|max:255',
            'deskripsi_sanksi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,selesai,dibatalkan',
            'diberikan_oleh' => 'nullable|exists:guru,id',
        ]);

        try {
            $sanksi->update($validated);

            return redirect()->route('admin.komdis-sanksi.index')
                ->with('success', 'Sanksi Komdis berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui sanksi: ' . $e->getMessage());
        }
    }
}

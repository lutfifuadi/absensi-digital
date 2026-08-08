<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkKasus;
use App\Models\Guru;
use App\Models\KomdisSidang;
use App\Models\Siswa;
use App\Services\BkKomdisService;
use Illuminate\Http\Request;
use Exception;

class KomdisSidangController extends Controller
{
    protected BkKomdisService $bkKomdisService;

    public function __construct(BkKomdisService $bkKomdisService)
    {
        $this->bkKomdisService = $bkKomdisService;
    }

    public function index(Request $request)
    {
        $query = KomdisSidang::with(['siswa.kelas', 'pimpinanSidang', 'creator']);

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

        $sidangs = $query->latest('tanggal_sidang')->paginate(15)->withQueryString();
        $siswas = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $gurus = Guru::orderBy('nama_lengkap')->get();
        $kasuses = BkKasus::whereIn('status', ['terbuka', 'dalam_proses'])->get();

        return view('admin.komdis-sidang.index', compact('sidangs', 'siswas', 'gurus', 'kasuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'bk_kasus_id' => 'nullable|exists:bk_kasus,id',
            'tanggal_sidang' => 'required|date',
            'waktu_sidang' => 'nullable',
            'lokasi_sidang' => 'nullable|string|max:255',
            'agenda' => 'required|string|max:255',
            'deskripsi_pelanggaran' => 'required|string',
            'keputusan_sidang' => 'nullable|string',
            'status' => 'required|in:terjadwal,berjalan,ditunda,selesai',
            'pimpinan_sidang_id' => 'nullable|exists:guru,id',
        ]);

        try {
            $this->bkKomdisService->tambahSidangKomdis($validated);

            return redirect()->route('admin.komdis-sidang.index')
                ->with('success', 'Sidang Komdis berhasil dijadwalkan.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal menjadwalkan sidang: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sidang = KomdisSidang::with(['siswa.kelas', 'pimpinanSidang', 'creator', 'sanksi.diberikanOleh', 'kasusBk'])
            ->findOrFail($id);

        $gurus = Guru::orderBy('nama_lengkap')->get();

        return view('admin.komdis-sidang.show', compact('sidang', 'gurus'));
    }

    public function update(Request $request, $id)
    {
        $sidang = KomdisSidang::findOrFail($id);

        $validated = $request->validate([
            'tanggal_sidang' => 'required|date',
            'waktu_sidang' => 'nullable',
            'lokasi_sidang' => 'nullable|string|max:255',
            'agenda' => 'required|string|max:255',
            'deskripsi_pelanggaran' => 'required|string',
            'keputusan_sidang' => 'nullable|string',
            'status' => 'required|in:terjadwal,berjalan,ditunda,selesai',
            'pimpinan_sidang_id' => 'nullable|exists:guru,id',
        ]);

        try {
            $sidang->update($validated);

            return redirect()->route('admin.komdis-sidang.show', $sidang->id)
                ->with('success', 'Sidang Komdis berhasil diperbarui.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memperbarui sidang: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $sidang = KomdisSidang::findOrFail($id);
            $sidang->delete();

            return redirect()->route('admin.komdis-sidang.index')
                ->with('success', 'Sidang Komdis berhasil dihapus.');
        } catch (Exception $e) {
            return back()->with('error', 'Gagal menghapus sidang: ' . $e->getMessage());
        }
    }
}

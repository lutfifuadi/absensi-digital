<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\EkskulAnggotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EkskulAnggotaController extends Controller
{
    public function __construct(
        private EkskulAnggotaService $anggotaService
    ) {}

    /**
     * Daftar anggota per ekskul.
     */
    public function index($ekskulId)
    {
        $anggota = $this->anggotaService->getAnggota($ekskulId);
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();

        return view('admin.ekskul.anggota-index', compact('anggota', 'ekskulId', 'siswaOptions'));
    }

    /**
     * Tambah anggota baru ke ekskul.
     */
    public function store(Request $request, $ekskulId)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
        ]);

        try {
            $anggota = $this->anggotaService->addAnggota($ekskulId, $request->siswa_id);

            return back()->with('success', "Siswa berhasil ditambahkan ke ekskul.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Gagal menambahkan anggota ekskul', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Gagal menambahkan anggota. Silakan coba lagi.');
        }
    }

    /**
     * Hapus anggota dari ekskul.
     */
    public function destroy($ekskulId, $id)
    {
        try {
            $this->anggotaService->removeAnggota($id);

            return back()->with('success', 'Anggota berhasil dikeluarkan dari ekskul.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus anggota ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus anggota. Silakan coba lagi.');
        }
    }

    /**
     * Update status anggota (aktif/cuti/keluar).
     */
    public function updateStatus(Request $request, $ekskulId, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,cuti,keluar',
        ]);

        try {
            $anggota = $this->anggotaService->updateStatus($id, $request->status);

            $statusText = [
                'aktif'  => 'diaktifkan kembali',
                'cuti'   => 'ditandai cuti',
                'keluar' => 'dikeluarkan',
            ][$request->status] ?? 'diperbarui';

            return back()->with('success', "Status anggota berhasil {$statusText}.");
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui status anggota ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal memperbarui status. Silakan coba lagi.');
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EkskulRequest;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Services\EkskulService;
use Illuminate\Support\Facades\Log;

class EkskulController extends Controller
{
    public function __construct(
        private EkskulService $ekskulService
    ) {}

    /**
     * Daftar ekskul.
     */
    public function index()
    {
        $filters = request()->only(['kategori', 'status', 'search']);
        $ekskuls = $this->ekskulService->getAll($filters);

        return view('admin.ekskul.index', compact('ekskuls'));
    }

    /**
     * Form tambah ekskul.
     */
    public function create()
    {
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.ekskul.create', compact('guruOptions'));
    }

    /**
     * Simpan ekskul baru.
     */
    public function store(EkskulRequest $request)
    {
        try {
            $ekskul = $this->ekskulService->create($request->validated());

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('Gagal membuat ekskul', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Form edit ekskul.
     */
    public function edit($id)
    {
        $ekskul = $this->ekskulService->getById($id);
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.ekskul.edit', compact('ekskul', 'guruOptions'));
    }

    /**
     * Update data ekskul.
     */
    public function update(EkskulRequest $request, $id)
    {
        try {
            $ekskul = $this->ekskulService->update($id, $request->validated());

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui ekskul', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Hapus ekskul (soft delete).
     */
    public function destroy($id)
    {
        try {
            $this->ekskulService->delete($id);

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', 'Ekskul berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Toggle status aktif/nonaktif ekskul.
     */
    public function toggleStatus($id)
    {
        try {
            $ekskul = $this->ekskulService->toggleStatus($id);
            $statusText = $ekskul->status ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil {$statusText}.");
        } catch (\Exception $e) {
            Log::error('Gagal mengubah status ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal mengubah status. Silakan coba lagi.');
        }
    }
}

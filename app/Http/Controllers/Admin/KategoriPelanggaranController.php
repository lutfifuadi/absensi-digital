<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
use App\Exports\KategoriPelanggaranExport;
use App\Exports\KategoriPelanggaranTemplateExport;
use App\Imports\KategoriPelanggaranImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class KategoriPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriPelanggaran::query()->withCount('jenisPelanggaran');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
        }

        if ($request->filled('is_aktif')) {
            $query->where('is_aktif', $request->input('is_aktif'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $allowedPerPage = [10, 25, 50, 100, 15];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $categories = $query->orderBy('urutan')->orderBy('nama')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.pelanggaran-kategori.table', compact('categories'))->render();
        }

        return view('admin.pelanggaran-kategori.index', compact('categories'));
    }

    public function store(StoreKategoriRequest $request)
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');
        $data['urutan'] = $data['urutan'] ?? 0;
        
        $category = KategoriPelanggaran::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori pelanggaran berhasil ditambahkan.',
                'data' => $category,
            ]);
        }

        return redirect()->route('admin.pelanggaran-kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function update(UpdateKategoriRequest $request, $id)
    {
        $category = KategoriPelanggaran::findOrFail($id);
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');
        $data['urutan'] = $data['urutan'] ?? 0;
        
        $category->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kategori pelanggaran berhasil diperbarui.',
                'data' => $category,
            ]);
        }

        return redirect()->route('admin.pelanggaran-kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil diperbarui.');
    }

    public function destroy($id, Request $request)
    {
        $category = KategoriPelanggaran::findOrFail($id);
        $nama = $category->nama;
        
        // Cek apakah kategori memiliki jenis pelanggaran
        if ($category->jenisPelanggaran()->count() > 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Kategori {$nama} tidak dapat dihapus karena memiliki relasi dengan jenis pelanggaran.",
                ], 422);
            }
            return redirect()->route('admin.pelanggaran-kategori.index')
                ->with('error', 'Kategori tidak dapat dihapus karena memiliki relasi.');
        }

        $category->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kategori pelanggaran {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.pelanggaran-kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $isAktif = $request->input('is_aktif');
        $fileName = 'kategori_pelanggaran_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new KategoriPelanggaranExport($search, $isAktif), $fileName);
    }

    public function downloadTemplate()
    {
        $fileName = 'template_import_kategori_pelanggaran.xlsx';
        return Excel::download(new KategoriPelanggaranTemplateExport(), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'import_file.required' => 'File Excel wajib dipilih.',
            'import_file.mimes' => 'File harus berformat xlsx, xls, atau csv.',
            'import_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $import = new KategoriPelanggaranImport();
            Excel::import($import, $request->file('import_file'));
            $res = $import->getImportResult();

            $msg = "Berhasil mengimpor {$res['imported']} kategori baru";
            if ($res['updated'] > 0) {
                $msg .= " dan memperbarui {$res['updated']} kategori existing.";
            } else {
                $msg .= ".";
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data' => $res
                ]);
            }

            return redirect()->route('admin.pelanggaran-kategori.index')->with('success', $msg);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Import Kategori Pelanggaran Gagal: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor file: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('admin.pelanggaran-kategori.index')->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }
}

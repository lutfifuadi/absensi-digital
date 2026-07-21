<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Http\Requests\StoreKategoriRequest;
use App\Http\Requests\UpdateKategoriRequest;
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
        
        KategoriPelanggaran::create($data);

        return redirect()->route('admin.pelanggaran-kategori.index')
            ->with('success', 'Kategori pelanggaran berhasil ditambahkan.');
    }

    public function update(UpdateKategoriRequest $request, $id)
    {
        $category = KategoriPelanggaran::findOrFail($id);
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');
        
        $category->update($data);

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
}

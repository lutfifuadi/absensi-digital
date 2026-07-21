<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use App\Http\Requests\StoreJenisPelanggaranRequest;
use App\Http\Requests\UpdateJenisPelanggaranRequest;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisPelanggaran::query()->with('kategori')->withCount('pelanggaranSiswa');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        if ($request->filled('is_aktif')) {
            $query->where('is_aktif', $request->input('is_aktif'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $allowedPerPage = [10, 25, 50, 100, 15];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $jenisPelanggarans = $query->orderBy('kategori_id')->orderBy('nama')->paginate($perPage)->withQueryString();
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();

        if ($request->ajax()) {
            return view('admin.pelanggaran-jenis.table', compact('jenisPelanggarans'))->render();
        }

        return view('admin.pelanggaran-jenis.index', compact('jenisPelanggarans', 'categories'));
    }

    public function create()
    {
        $jenisPelanggaran = new JenisPelanggaran();
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();
        return view('admin.pelanggaran-jenis.create', compact('jenisPelanggaran', 'categories'));
    }

    public function store(StoreJenisPelanggaranRequest $request)
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');

        JenisPelanggaran::create($data);

        return redirect()->route('admin.pelanggaran-jenis.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();
        return view('admin.pelanggaran-jenis.edit', compact('jenisPelanggaran', 'categories'));
    }

    public function update(UpdateJenisPelanggaranRequest $request, $id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');

        $jenisPelanggaran->update($data);

        return redirect()->route('admin.pelanggaran-jenis.index')
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy($id, Request $request)
    {
        $jenis = JenisPelanggaran::findOrFail($id);
        $nama = $jenis->nama;

        // Cek relasi: jika sudah dipakai di pelanggaran_siswa, tidak boleh hard delete, tapi soft delete / nonaktifkan saja
        // Namun, jenis pelanggaran menggunakan SoftDeletes trait.
        // Jika pelanggaranSiswa sudah mencatat jenis ini, soft delete diperbolehkan, tapi hard delete dihalangi.
        // Mari kita cek jumlah record pelanggaranSiswa.
        $terpakai = $jenis->pelanggaranSiswa()->count() > 0;

        if ($terpakai) {
            // Karena menggunakan SoftDeletes, soft delete diperbolehkan.
            // Sesuai PRD: "jenis pelanggaran yang sudah terpakai tidak boleh di-hard delete (hanya bisa dinonaktifkan / soft delete)"
            // Kita lakukan soft delete menggunakan method delete() biasa karena model menggunakan SoftDeletes.
            $jenis->delete();
            $message = "Jenis pelanggaran '{$nama}' berhasil dinonaktifkan/diarsipkan (soft delete) karena sudah pernah digunakan.";
        } else {
            // Jika tidak terpakai, bisa kita forceDelete atau delete biasa. Kita lakukan delete() biasa (akan masuk soft delete).
            $jenis->delete();
            $message = "Jenis pelanggaran '{$nama}' berhasil dihapus.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'terpakai' => $terpakai
            ]);
        }

        return redirect()->route('admin.pelanggaran-jenis.index')->with('success', $message);
    }
}

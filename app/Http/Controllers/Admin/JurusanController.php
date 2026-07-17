<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class JurusanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        $jurusan = Jurusan::when($search, function ($query, $search) {
                $query->where('kode', 'like', "%{$search}%")
                      ->orWhere('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.jurusan.table', compact('jurusan'))->render();
        }

        return view('admin.jurusan.index', compact('jurusan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:jurusan,kode',
            'nama' => 'required|string|max:100',
        ]);

        $data['kode'] = strtoupper($data['kode']);

        $jurusan = Jurusan::create($data);

        ActivityLog::record(
            'create',
            'jurusan',
            "Membuat jurusan baru: {$jurusan->nama} ({$jurusan->kode})",
            [],
            $jurusan->toArray()
        );

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:jurusan,kode,' . $jurusan->id,
            'nama' => 'required|string|max:100',
        ]);

        $data['kode'] = strtoupper($data['kode']);
        
        $oldData = $jurusan->toArray();
        $jurusan->update($data);

        ActivityLog::record(
            'update',
            'jurusan',
            "Mengubah data jurusan: {$jurusan->nama} ({$jurusan->kode})",
            $oldData,
            $jurusan->toArray()
        );

        return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jurusan $jurusan)
    {
        try {
            // Cek apakah masih digunakan di kelas
            if ($jurusan->kelas()->exists()) {
                return redirect()->route('admin.jurusan.index')
                    ->with('error', 'Jurusan tidak dapat dihapus karena masih digunakan oleh kelas.');
            }

            $oldData = $jurusan->toArray();
            $jurusan->delete();

            ActivityLog::record(
                'delete',
                'jurusan',
                "Menghapus jurusan: {$oldData['nama']} ({$oldData['kode']})",
                $oldData,
                []
            );

            return redirect()->route('admin.jurusan.index')->with('success', 'Jurusan berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('admin.jurusan.index')
                ->with('error', 'Gagal menghapus jurusan: Jurusan sedang digunakan oleh data lain.');
        }
    }
}

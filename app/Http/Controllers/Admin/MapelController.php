<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Http\Requests\StoreMapelRequest;
use App\Http\Requests\UpdateMapelRequest;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $query = Mapel::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode_mapel', 'like', "%{$search}%")
                  ->orWhere('nama_mapel', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelompok')) {
            $query->where('kelompok', $request->input('kelompok'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $allowedPerPage = [10, 25, 50, 100, 15];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $mapels = $query->orderBy('nama_mapel')->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.mapel.table', compact('mapels'))->render();
        }

        return view('admin.mapel.index', compact('mapels'));
    }

    public function create()
    {
        $mapel = new Mapel();
        return view('admin.mapel.form', compact('mapel'));
    }

    public function store(StoreMapelRequest $request)
    {
        Mapel::create($request->validated());

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        return view('admin.mapel.form', compact('mapel'));
    }

    public function update(UpdateMapelRequest $request, Mapel $mapel)
    {
        $mapel->update($request->validated());

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel, Request $request)
    {
        $nama = $mapel->nama_mapel;
        $mapel->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Mata pelajaran {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}

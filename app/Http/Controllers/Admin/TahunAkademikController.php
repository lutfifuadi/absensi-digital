<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class TahunAkademikController extends Controller
{
    public function index()
    {
        $tahunAkademik = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.tahun-akademik.index', compact('tahunAkademik'));
    }

    public function create()
    {
        return view('admin.tahun-akademik.form', ['tahunAkademik' => new TahunAkademik()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
        ]);

        $data['is_aktif'] = $request->has('is_aktif');

        if ($data['is_aktif']) {
            TahunAkademik::where('is_aktif', true)->update(['is_aktif' => false]);
        }

        TahunAkademik::create($data);

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(TahunAkademik $tahunAkademik)
    {
        return view('admin.tahun-akademik.form', compact('tahunAkademik'));
    }

    public function update(Request $request, TahunAkademik $tahunAkademik)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'is_aktif' => 'nullable|boolean',
        ]);

        $data['is_aktif'] = $request->has('is_aktif');

        if ($data['is_aktif']) {
            TahunAkademik::where('is_aktif', true)->where('id', '!=', $tahunAkademik->id)->update(['is_aktif' => false]);
        }

        $tahunAkademik->update($data);

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAkademik $tahunAkademik)
    {
        $tahunAkademik->delete();

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function setSession(Request $request)
    {
        $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
        ]);

        session([
            'tahun_akademik_id' => $request->tahun_akademik_id,
            'tahun_ajaran_id' => $request->tahun_akademik_id
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah sesi Tahun Ajaran aktif.');
    }
}

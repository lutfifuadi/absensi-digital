<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KegiatanController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::with('tahunAkademik')->latest()->paginate(10);
        return view('admin.kegiatan.index', compact('kegiatans'));
    }

    public function create()
    {
        $tahunAkademiks = TahunAkademik::all();
        return view('admin.kegiatan.create', compact('tahunAkademiks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
        ]);

        $data['qr_code_kegiatan'] = 'KGT-' . strtoupper(Str::random(10));
        $data['tahun_akademik_id'] = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->first()?->id;

        Kegiatan::create($data);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dibuat.');
    }

    public function show(Kegiatan $kegiatan)
    {
        return redirect()->route('admin.kegiatan.index');
    }

    public function edit(Kegiatan $kegiatan)
    {
        $tahunAkademiks = TahunAkademik::all();
        return view('admin.kegiatan.edit', compact('kegiatan', 'tahunAkademiks'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'required|date',
            'waktu_mulai' => 'required',
            'waktu_selesai' => 'required',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
        ]);

        $kegiatan->update($data);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}

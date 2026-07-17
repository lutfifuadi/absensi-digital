<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TahunAkademik;
use App\Models\Kelas;
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
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();
        return view('admin.kegiatan.create', compact('tahunAkademiks', 'kelas', 'tingkat', 'jurusanList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'is_wajib' => 'nullable|boolean',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');
        $data['qr_code_kegiatan'] = 'KGT-' . strtoupper(Str::random(10));
        $data['tahun_akademik_id'] = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->first()?->id;

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

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
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();
        return view('admin.kegiatan.edit', compact('kegiatan', 'tahunAkademiks', 'kelas', 'tingkat', 'jurusanList'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'is_wajib' => 'nullable|boolean',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

        $kegiatan->update($data);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}

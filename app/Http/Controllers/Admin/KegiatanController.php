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

    public function searchSiswa(Request $request)
    {
        $search = trim($request->query('q', ''));
        if (strlen($search) < 1) {
            return response()->json(['results' => []]);
        }

        $siswa = \App\Models\Siswa::with('kelas:id,nama')
            ->where('status', 'aktif')
            ->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('nama_lengkap')
            ->limit(30)
            ->get()
            ->map(function ($s) {
                $kelasNama = $s->kelas->nama ?? '-';
                $nis = $s->nis ?? '-';
                return [
                    'id'   => $s->id,
                    'text' => "{$s->nama_lengkap} — ({$kelasNama} / NIS: {$nis})",
                ];
            });

        return response()->json(['results' => $siswa]);
    }

    public function create()
    {
        $tahunAkademiks = TahunAkademik::all();
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();
        $siswaList = collect();
        return view('admin.kegiatan.create', compact('tahunAkademiks', 'kelas', 'tingkat', 'jurusanList', 'siswaList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_pelaksanaan',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'target_gender' => 'nullable|in:L,P',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'integer|exists:siswa,id',
            'is_wajib' => 'nullable|boolean',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');
        $data['qr_code_kegiatan'] = 'KGT-' . strtoupper(Str::random(10));
        $data['tahun_akademik_id'] = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->first()?->id;

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

        // Ambil tanggal_selesai dari request. Jika kosong, set ke null.
        if (empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = null;
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
        
        $selectedSiswaIds = is_array($kegiatan->target_siswa) ? $kegiatan->target_siswa : [];
        $siswaList = !empty($selectedSiswaIds) 
            ? \App\Models\Siswa::with('kelas')->whereIn('id', $selectedSiswaIds)->get()
            : collect();

        return view('admin.kegiatan.edit', compact('kegiatan', 'tahunAkademiks', 'kelas', 'tingkat', 'jurusanList', 'siswaList'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_pelaksanaan',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'target_gender' => 'nullable|in:L,P',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'integer|exists:siswa,id',
            'is_wajib' => 'nullable|boolean',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

        // Ambil tanggal_selesai dari request. Jika kosong, set ke null.
        if (empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = null;
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

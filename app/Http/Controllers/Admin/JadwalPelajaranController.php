<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\Request;

class JadwalPelajaranController extends Controller
{
    private array $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request)
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions = Mapel::orderBy('nama_mapel')->get();
        $hariOptions = $this->hariOptions;

        $query = JadwalPelajaran::with(['kelas', 'guru'])
            ->whereHas('kelas', function($q) use ($tahunAjaranId) {
                $q->where('tahun_akademik_id', $tahunAjaranId);
            })
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")
            ->orderBy('jam_mulai');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $jadwal = $query->paginate(50)->withQueryString();

        return view('admin.jadwal.index', compact('jadwal', 'kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function create()
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');
        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions = Mapel::orderBy('nama_mapel')->get();
        $hariOptions = $this->hariOptions;

        return view('admin.jadwal.form', compact('kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|exists:mapels,nama_mapel',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalPelajaran::create($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelajaran berhasil disimpan.');
    }

    public function edit(JadwalPelajaran $jadwal)
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');
        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions = Mapel::orderBy('nama_mapel')->get();
        $hariOptions = $this->hariOptions;

        return view('admin.jadwal.form', compact('jadwal', 'kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|exists:mapels,nama_mapel',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
        ]);

        $jadwal->update($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwal)
    {
        $jadwal->delete();

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}

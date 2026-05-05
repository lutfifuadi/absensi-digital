<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;

class JadwalPelajaranController extends Controller
{
    private array $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request)
    {
        $kelasOptions = Kelas::orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $hariOptions = $this->hariOptions;

        $query = JadwalPelajaran::with(['kelas', 'guru'])->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')")->orderBy('jam_mulai');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $jadwal = $query->paginate(50)->withQueryString();

        return view('admin.jadwal.index', compact('jadwal', 'kelasOptions', 'guruOptions', 'hariOptions'));
    }

    public function create()
    {
        $kelasOptions = Kelas::orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $hariOptions = $this->hariOptions;

        return view('admin.jadwal.form', compact('kelasOptions', 'guruOptions', 'hariOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|string|max:100',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalPelajaran::create($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelajaran berhasil disimpan.');
    }

    public function edit(JadwalPelajaran $jadwal)
    {
        $kelasOptions = Kelas::orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();
        $hariOptions = $this->hariOptions;

        return view('admin.jadwal.form', compact('jadwal', 'kelasOptions', 'guruOptions', 'hariOptions'));
    }

    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|string|max:100',
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

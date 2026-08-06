<?php

namespace App\Http\Controllers\Admin;

use App\Exports\JadwalPelajaranTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\JadwalPelajaranImport;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class JadwalPelajaranController extends Controller
{
    private array $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

    public function index(Request $request)
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions  = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions = Mapel::orderBy('nama_mapel')->get();
        $hariOptions  = $this->hariOptions;

        $query = JadwalPelajaran::with(['kelas', 'guru'])
            ->whereHas('kelas', function ($q) use ($tahunAjaranId) {
                $q->where('tahun_akademik_id', $tahunAjaranId);
            })
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad')")
            ->orderBy('jam_mulai');

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $jadwal = $query->paginate(50)->withQueryString();

        return view('admin.jadwal.index', compact('jadwal', 'kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function create()
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kelasOptions  = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions   = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions  = Mapel::orderBy('nama_mapel')->get();
        $hariOptions   = $this->hariOptions;

        return view('admin.jadwal.form', compact('kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|exists:mapels,nama_mapel',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad',
            'jam_mulai'      => 'required|date_format:H:i',
            'jam_selesai'    => 'required|date_format:H:i|after:jam_mulai',
        ]);

        JadwalPelajaran::create($data);

        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal pelajaran berhasil disimpan.');
    }

    public function edit(JadwalPelajaran $jadwal)
    {
        $tahunAjaranId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kelasOptions  = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $guruOptions   = Guru::orderBy('nama_lengkap')->get();
        $mapelOptions  = Mapel::orderBy('nama_mapel')->get();
        $hariOptions   = $this->hariOptions;

        return view('admin.jadwal.form', compact('jadwal', 'kelasOptions', 'guruOptions', 'mapelOptions', 'hariOptions'));
    }

    public function update(Request $request, JadwalPelajaran $jadwal)
    {
        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'guru_id'        => 'nullable|exists:guru,id',
            'mata_pelajaran' => 'required|exists:mapels,nama_mapel',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Ahad',
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

    /**
     * Download Template Excel Dinamis dengan Dropdown Data Validation.
     */
    public function downloadTemplate()
    {
        $filename = 'Template_Jadwal_Pelajaran_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new JadwalPelajaranTemplateExport(), $filename);
    }

    /**
     * Import Jadwal Pelajaran Massal dari File Excel.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file_excel.required' => 'File Excel wajib diunggah.',
            'file_excel.mimes'    => 'Format file harus berupa Excel (.xlsx, .xls) atau CSV.',
            'file_excel.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $import = new JadwalPelajaranImport();
            Excel::import($import, $request->file('file_excel'));

            $msg = "Berhasil mengimpor {$import->importedCount} baris jadwal pelajaran.";
            if (count($import->errors) > 0) {
                $errorDetails = implode('<br>', array_slice($import->errors, 0, 5));
                if (count($import->errors) > 5) {
                    $errorDetails .= '<br>...dan ' . (count($import->errors) - 5) . ' peringatan lainnya.';
                }
                return redirect()->route('admin.jadwal.index')
                    ->with('success', $msg)
                    ->with('warning', 'Beberapa baris dilewati:<br>' . $errorDetails);
            }

            return redirect()->route('admin.jadwal.index')->with('success', $msg);
        } catch (Throwable $e) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Gagal mengimpor file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Duplikasi / Salin seluruh jadwal pelajaran dari 1 kelas ke kelas lain.
     */
    public function duplicate(Request $request)
    {
        $request->validate([
            'kelas_asal_id'   => 'required|exists:kelas,id',
            'kelas_tujuan_id' => 'required|exists:kelas,id|different:kelas_asal_id',
        ], [
            'kelas_asal_id.required'   => 'Kelas asal wajib dipilih.',
            'kelas_tujuan_id.required' => 'Kelas tujuan wajib dipilih.',
            'kelas_tujuan_id.different' => 'Kelas tujuan harus berbeda dengan kelas asal.',
        ]);

        try {
            $kelasAsal = Kelas::findOrFail($request->kelas_asal_id);
            $kelasTujuan = Kelas::findOrFail($request->kelas_tujuan_id);

            $jadwalsAsal = JadwalPelajaran::where('kelas_id', $kelasAsal->id)->get();

            if ($jadwalsAsal->isEmpty()) {
                return redirect()->route('admin.jadwal.index')
                    ->with('error', "Kelas {$kelasAsal->nama} belum memiliki data jadwal pelajaran untuk disalin.");
            }

            $countCopied = 0;
            foreach ($jadwalsAsal as $j) {
                JadwalPelajaran::updateOrCreate(
                    [
                        'kelas_id'  => $kelasTujuan->id,
                        'hari'      => $j->hari,
                        'jam_mulai' => $j->jam_mulai,
                    ],
                    [
                        'guru_id'        => $j->guru_id,
                        'mata_pelajaran' => $j->mata_pelajaran,
                        'jam_selesai'    => $j->jam_selesai,
                    ]
                );
                $countCopied++;
            }

            return redirect()->route('admin.jadwal.index')
                ->with('success', "Berhasil menyalin {$countCopied} jadwal dari kelas {$kelasAsal->nama} ke kelas {$kelasTujuan->nama}.");
        } catch (Throwable $e) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Gagal menyalin jadwal: ' . $e->getMessage());
        }
    }
}

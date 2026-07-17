<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Services\SiswaService;

class KelasController extends Controller
{
    public function __construct(protected SiswaService $siswaService) {}

    public function index(Request $request)
    {
        $search = $request->query('search');
        $tingkat = $request->query('tingkat');
        $perPage = (int) $request->query('per_page', 10);

        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $kelas = Kelas::with(['waliKelas.user', 'tahunAkademik', 'jurusan'])
            ->withCount('siswa')
            ->where('tahun_akademik_id', $tahunAjaranId)
            ->when($tingkat, function ($query, $tingkat) {
                return $query->where('tingkat', $tingkat);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('tingkat', 'like', "%{$search}%")
                      ->orWhereHas('jurusan', function ($qj) use ($search) {
                          $qj->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.kelas.table', compact('kelas'))->render();
        }

        $guruOptions = Guru::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();
        $tahunAkademikList = TahunAkademik::orderBy('nama', 'desc')->orderBy('semester', 'desc')->get();
        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();
        $jurusanOptions = Jurusan::orderBy('nama')->get();

        return view('admin.kelas.index', compact('kelas', 'guruOptions', 'tahunAkademikOptions', 'tahunAkademikList', 'tingkat', 'tingkatOptions', 'jurusanOptions'));
    }

    public function create()
    {
        $guruOptions = Guru::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();
        $jurusanOptions = Jurusan::orderBy('nama')->get();

        return view('admin.kelas.form', [
            'kelas' => new Kelas(),
            'guruOptions' => $guruOptions,
            'tahunAkademikOptions' => $tahunAkademikOptions,
            'jurusanOptions' => $jurusanOptions,
        ]);
    }

    public function store(Request $request)
    {
        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'tingkat' => ['required', Rule::in($tingkatOptions)],
            'jurusan_id' => 'required|exists:jurusan,id',
            'wali_kelas_id' => ['nullable', 'integer', Rule::exists('guru', 'id')],
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'is_aktif_absensi' => 'nullable|boolean',
            'kustomisasi_jam' => 'nullable|boolean',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
        ]);

        if (! empty($data['wali_kelas_id']) && ! Guru::where('id', $data['wali_kelas_id'])
            ->where('status', 'aktif')
            ->exists()) {
            return redirect()->back()->withInput()->withErrors(['wali_kelas_id' => 'Wali kelas harus dipilih dari guru yang aktif.']);
        }

        $data['is_aktif_absensi'] = $request->has('is_aktif_absensi');
        $data['kustomisasi_jam'] = $request->has('kustomisasi_jam');

        $kelas = Kelas::create($data);

        session(['tahun_ajaran_id' => $kelas->tahun_akademik_id, 'tahun_akademik_id' => $kelas->tahun_akademik_id]);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $guruOptions = Guru::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();
        $jurusanOptions = Jurusan::orderBy('nama')->get();

        return view('admin.kelas.form', compact('kelas', 'guruOptions', 'tahunAkademikOptions', 'jurusanOptions'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'tingkat' => ['required', Rule::in($tingkatOptions)],
            'jurusan_id' => 'required|exists:jurusan,id',
            'wali_kelas_id' => ['nullable', 'integer', Rule::exists('guru', 'id')],
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'is_aktif_absensi' => 'nullable|boolean',
            'kustomisasi_jam' => 'nullable|boolean',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
        ]);

        if (! empty($data['wali_kelas_id']) && ! Guru::where('id', $data['wali_kelas_id'])
            ->where('status', 'aktif')
            ->exists()) {
            return redirect()->back()->withInput()->withErrors(['wali_kelas_id' => 'Wali kelas harus dipilih dari guru yang aktif.']);
        }

        $data['is_aktif_absensi'] = $request->has('is_aktif_absensi');
        $data['kustomisasi_jam'] = $request->has('kustomisasi_jam');

        $kelas->update($data);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\KelasImport(), $request->file('import_file'));

            return redirect()->route('admin.kelas.index')->with('success', 'Data kelas berhasil diimpor.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $exception) {
            $failures = $exception->failures();
            $messages = collect($failures)->map(function ($failure) {
                return "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            })->implode(' | ');

            return redirect()->back()->with('error', 'Import gagal: ' . $messages);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Import gagal: ' . $th->getMessage());
        }
    }

    public function show(Request $request, Kelas $kelas)
    {
        $search  = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        $query = $kelas->siswa()->orderBy('nama_lengkap');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $perPageVal = $perPage === 'all' ? max(1, $query->count()) : max(1, (int) $perPage);
        $siswa      = $query->paginate($perPageVal)->withQueryString();

        if ($request->ajax()) {
            return view('admin.kelas.siswa-table', compact('kelas', 'siswa'));
        }

        $totalSiswaCount = $kelas->siswa()->count();

        $siswaAvailable = Siswa::where('tahun_akademik_id', $kelas->tahun_akademik_id)
            ->where(function ($q) use ($kelas) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', '!=', $kelas->id);
            })
            ->orderBy('nama_lengkap')
            ->get();

        $kelasOptions = Kelas::where('tahun_akademik_id', $kelas->tahun_akademik_id)
            ->where('id', '!=', $kelas->id)
            ->with('jurusan')
            ->orderBy('nama')
            ->get();

        return view('admin.kelas.show', compact('kelas', 'siswa', 'siswaAvailable', 'totalSiswaCount', 'kelasOptions'));
    }

    public function addSiswa(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'siswa_ids'   => 'required|array|min:1|max:500',
            'siswa_ids.*' => 'required|integer|exists:siswa,id',
        ]);

        Siswa::whereIn('id', $data['siswa_ids'])
            ->where('tahun_akademik_id', $kelas->tahun_akademik_id)
            ->where(function ($q) use ($kelas) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', '!=', $kelas->id);
            })
            ->update(['kelas_id' => $kelas->id]);

        return redirect()->route('admin.kelas.show', $kelas)
            ->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function removeSiswa(Kelas $kelas, Siswa $siswa)
    {
        if ($siswa->kelas_id !== $kelas->id) {
            return redirect()->route('admin.kelas.show', $kelas)
                ->with('error', 'Siswa tidak ditemukan di kelas ini.');
        }

        $siswa->update(['kelas_id' => null]);

        return redirect()->route('admin.kelas.show', $kelas)
            ->with('success', 'Siswa berhasil dilepas dari kelas.');
    }

    /**
     * Preview copy kelas dari TA sumber ke TA tujuan
     */
    public function previewCopy(Request $request)
    {
        $request->validate([
            'source_ta_id' => 'required|exists:tahun_akademik,id',
            'target_ta_id' => 'required|exists:tahun_akademik,id|different:source_ta_id',
        ]);

        $taSumber = TahunAkademik::findOrFail($request->source_ta_id);
        $taTujuan = TahunAkademik::findOrFail($request->target_ta_id);

        $kelasSumber = Kelas::where('tahun_akademik_id', $taSumber->id)
            ->orderBy('tingkat')->orderBy('nama')->get();

        $kelasExisting = Kelas::where('tahun_akademik_id', $taTujuan->id)
            ->get()->keyBy('nama');

        $baru = [];
        $skip = [];

        foreach ($kelasSumber as $kelas) {
            if ($kelasExisting->has($kelas->nama)) {
                $skip[] = $kelas;
            } else {
                $baru[] = $kelas;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ta_sumber' => $taSumber->nama . ' ' . ucfirst($taSumber->semester),
                'ta_tujuan' => $taTujuan->nama . ' ' . ucfirst($taTujuan->semester),
                'total_sumber' => $kelasSumber->count(),
                'total_skip' => count($skip),
                'total_baru' => count($baru),
                'kelas_baru' => collect($baru)->map(fn($k) => ['nama' => $k->nama, 'tingkat' => $k->tingkat, 'jurusan_id' => $k->jurusan_id]),
                'kelas_skip' => collect($skip)->map(fn($k) => ['nama' => $k->nama, 'tingkat' => $k->tingkat, 'jurusan_id' => $k->jurusan_id]),
            ]
        ]);
    }

    /**
     * Execute copy kelas dari TA sumber ke TA tujuan
     */
    public function executeCopy(Request $request)
    {
        $request->validate([
            'source_ta_id' => 'required|exists:tahun_akademik,id',
            'target_ta_id' => 'required|exists:tahun_akademik,id|different:source_ta_id',
        ]);

        $taSumber = TahunAkademik::findOrFail($request->source_ta_id);
        $taTujuan = TahunAkademik::findOrFail($request->target_ta_id);

        $kelasSumber = Kelas::where('tahun_akademik_id', $taSumber->id)->get();
        $kelasExisting = Kelas::where('tahun_akademik_id', $taTujuan->id)->get()->keyBy('nama');

        $berhasil = 0;
        $skip = 0;

        DB::beginTransaction();
        try {
            foreach ($kelasSumber as $kelas) {
                if ($kelasExisting->has($kelas->nama)) {
                    $skip++;
                    continue;
                }
                Kelas::create([
                    'nama' => $kelas->nama,
                    'tingkat' => $kelas->tingkat,
                    'jurusan_id' => $kelas->jurusan_id,
                    'tahun_akademik_id' => $taTujuan->id,
                    'wali_kelas_id' => null,
                    'is_aktif_absensi' => true,
                ]);
                $berhasil++;
            }

            DB::commit();

            ActivityLog::record(
                'create',
                'kelas',
                "Copy {$berhasil} kelas dari {$taSumber->nama} {$taSumber->semester} ke {$taTujuan->nama} {$taTujuan->semester} ({$skip} skip)",
                ['tahun_akademik_id_sumber' => $taSumber->id],
                ['tahun_akademik_id_tujuan' => $taTujuan->id, 'kelas_baru' => $berhasil, 'kelas_skip' => $skip]
            );

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyalin {$berhasil} kelas. {$skip} kelas sudah ada (di-skip)."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadSample()
    {
        $headers = ['nama', 'tingkat', 'jurusan_kode', 'wali_kelas', 'tahun_akademik'];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, ['X IPA 1', 'X', 'UMUM', 'Nama Guru Wali', '2023/2024 Ganjil']);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=sampel_import_kelas.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function pindahMassal(Request $request, Kelas $kelas)
    {
        $request->validate([
            'kelas_tujuan_id' => 'required|exists:kelas,id',
        ]);

        try {
            $count = $this->siswaService->pindahKelasMassal($kelas->id, (int) $request->kelas_tujuan_id);
            $kelasTujuan = Kelas::find($request->kelas_tujuan_id);
            
            return redirect()->route('admin.kelas.show', $kelas)
                ->with('success', "Berhasil memindahkan {$count} siswa dari kelas {$kelas->nama} ke kelas {$kelasTujuan->nama}.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}

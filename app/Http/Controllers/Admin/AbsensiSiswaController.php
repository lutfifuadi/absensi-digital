<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;

        // ── Filter / search params ──
        $search        = $request->query('search');
        $perPage       = (int) $request->query('per_page', 10);
        $sortBy        = $request->query('sort_by', 'tanggal');
        $sortDir       = $request->query('sort_dir', 'desc');
        $selectedKelasId = $request->query('kelas_id');
        $selectedStatus  = $request->query('status');
        $tanggalFrom   = $request->query('tanggal_from');
        $tanggalTo     = $request->query('tanggal_to');

        // Validate perPage
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Validate sort
        $allowedSorts = ['tanggal', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'tanggal';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $query = AbsensiSiswa::with(['siswa:id,nama_lengkap,kelas_id', 'kelas:id,nama', 'guru:id,nama_lengkap']);

        // ── Wali kelas restriction ──
        if ($isWaliKelas) {
            $guru = $user->guru;
            $kelasWaliId = null;
            if ($guru) {
                $kelasWali = \App\Models\Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $kelasWaliId = $kelasWali->id;
                }
            }

            if ($kelasWaliId) {
                // Saring absensi siswa yang ada di kelas bimbingan wali kelas saja
                $query->where('absensi_siswa.kelas_id', $kelasWaliId);
                // Paksa filter kelas ke kelas wali kelas
                $selectedKelasId = $kelasWaliId;
            } else {
                // Jika wali kelas belum memiliki kelas di TA aktif, paksa kosongkan data absensi
                $query->whereNull('absensi_siswa.id');
            }
        }

        // ── Filters ──
        if ($search) {
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%");
            });
        }

        if ($selectedKelasId) {
            $query->where('absensi_siswa.kelas_id', $selectedKelasId);
        }

        if ($selectedStatus) {
            $query->where('absensi_siswa.status', $selectedStatus);
        }

        if ($tanggalFrom) {
            $query->whereDate('absensi_siswa.tanggal', '>=', $tanggalFrom);
        }

        if ($tanggalTo) {
            $query->whereDate('absensi_siswa.tanggal', '<=', $tanggalTo);
        }

        // ── Sorting ──
        $query->orderBy('absensi_siswa.' . $sortBy, $sortDir);

        $absensi = $query->paginate($perPage)->withQueryString();

        // ── Kelas options for filter dropdown ──
        if ($isWaliKelas) {
            $kelasWali = $user->guru
                ? \App\Models\Kelas::where('wali_kelas_id', $user->guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first()
                : null;
            $kelasOptions = $kelasWali
                ? \App\Models\Kelas::where('id', $kelasWali->id)->get()
                : collect();
        } else {
            $kelasOptions = \App\Models\Kelas::orderBy('nama');
            if ($tahunAjaranId) {
                $kelasOptions->where('tahun_akademik_id', $tahunAjaranId);
            }
            $kelasOptions = $kelasOptions->get();
        }

        // ── AJAX: return only table partial ──
        if ($request->ajax()) {
            return view('admin.absensi-siswa.table', compact('absensi', 'sortBy', 'sortDir', 'isWaliKelas'))->render();
        }

        return view('admin.absensi-siswa.index', compact(
            'absensi', 'isWaliKelas', 'kelasOptions',
            'sortBy', 'sortDir', 'perPage',
            'search', 'selectedKelasId', 'selectedStatus',
            'tanggalFrom', 'tanggalTo'
        ));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $filters = [
            'search'       => $request->query('search'),
            'kelas_id'     => $request->query('kelas_id'),
            'status'       => $request->query('status'),
            'tanggal_from' => $request->query('tanggal_from'),
            'tanggal_to'   => $request->query('tanggal_to'),
            'sort_by'      => $request->query('sort_by', 'tanggal'),
            'sort_dir'     => $request->query('sort_dir', 'desc'),
        ];

        $export = new \App\Exports\AbsensiSiswaExport($filters, $isWaliKelas, $user, $tahunAjaranId);
        
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'absensi-siswa-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Tanggal',
            'NIS',
            'Status',
            'Jam Masuk',
            'Jam Pulang',
            'Keterangan'
        ];

        $data = [
            [
                now()->format('Y-m-d'),
                '12345',
                'hadir',
                '07:00',
                '14:00',
                'Hadir tepat waktu'
            ],
            [
                now()->format('Y-m-d'),
                '12346',
                'sakit',
                '',
                '',
                'Demam tinggi'
            ]
        ];

        $export = new class($headers, $data) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $headers;
            private $data;
            public function __construct($headers, $data) {
                $this->headers = $headers;
                $this->data = $data;
            }
            public function headings(): array {
                return $this->headers;
            }
            public function array(): array {
                return $this->data;
            }
        };

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'template-import-absensi.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $user = auth()->user();
        $guruId = null;
        if ($user && $user->guru) {
            $guruId = $user->guru->id;
        }

        $import = new \App\Imports\AbsensiSiswaImport($guruId);

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));
            $result = $import->getImportResult();

            $msg = "Berhasil mengimpor {$result['success']} data absensi.";
            if (count($result['errors']) > 0) {
                $errDetail = "<br>Beberapa baris gagal diimpor:<br><ul>";
                foreach (array_slice($result['errors'], 0, 10) as $err) {
                    $errDetail .= "<li>Baris {$err['row']} (NIS: {$err['nis']}): {$err['error']}</li>";
                }
                if (count($result['errors']) > 10) {
                    $errDetail .= "<li>...dan " . (count($result['errors']) - 10) . " error lainnya.</li>";
                }
                $errDetail .= "</ul>";
                
                return redirect()->route('admin.absensi-siswa.index')
                    ->with('success', $msg)
                    ->with('error_import', $errDetail);
            }

            return redirect()->route('admin.absensi-siswa.index')->with('success', $msg);
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, 'ZipArchive')) {
                $errorMsg = 'Ekstensi PHP "zip" (php_zip.dll / ZipArchive) belum diaktifkan pada php.ini server Anda. Silakan aktifkan extension=zip pada php.ini atau gunakan file berformat .csv untuk mengimpor data.';
            }
            return redirect()->route('admin.absensi-siswa.index')
                ->with('error', 'Terjadi kesalahan saat mengimpor file: ' . $errorMsg);
        }
    }

    public function create()
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
        if (! $tahunAjaranId) {
            $activeTa = \App\Models\TahunAkademik::where('is_aktif', true)->first();
            $tahunAjaranId = $activeTa ? $activeTa->id : null;
        }

        $siswaOptions = Siswa::with('kelas')->when($tahunAjaranId, function ($q, $taId) {
            $q->whereHas('kelas', fn($k) => $k->where('tahun_akademik_id', $taId));
        })->orderBy('nama_lengkap')->get();
        if ($siswaOptions->isEmpty()) {
            $siswaOptions = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        }

        $kelasOptions = Kelas::when($tahunAjaranId, function ($q, $taId) {
            $q->where('tahun_akademik_id', $taId);
        })->orderBy('nama')->get();
        if ($kelasOptions->isEmpty()) {
            $kelasOptions = Kelas::orderBy('nama')->get();
        }

        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-siswa.form', compact('siswaOptions', 'kelasOptions', 'guruOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'guru_id' => 'nullable|exists:guru,id',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        // Prevent duplicate absensi on the same date
        $duplicate = AbsensiSiswa::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['tanggal' => 'Absensi siswa ini sudah tercatat untuk tanggal tersebut.']);
        }

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
        if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $data['status'] === 'terlambat') {
            $data['status'] = 'hadir';
        }

        AbsensiSiswa::create($data);

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil disimpan.');
    }

    public function edit(AbsensiSiswa $absensiSiswa)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
        if (! $tahunAjaranId) {
            $activeTa = \App\Models\TahunAkademik::where('is_aktif', true)->first();
            $tahunAjaranId = $activeTa ? $activeTa->id : null;
        }

        $siswaOptions = Siswa::with('kelas')->orderBy('nama_lengkap')->get();
        $kelasOptions = Kelas::when($tahunAjaranId, function ($q, $taId) {
            $q->where('tahun_akademik_id', $taId);
        })->orderBy('nama')->get();

        if ($kelasOptions->isEmpty() || ($absensiSiswa->kelas_id && ! $kelasOptions->contains('id', $absensiSiswa->kelas_id))) {
            $kelasOptions = Kelas::orderBy('nama')->get();
        }

        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-siswa.form', compact('absensiSiswa', 'siswaOptions', 'kelasOptions', 'guruOptions'));
    }

    public function update(Request $request, AbsensiSiswa $absensiSiswa)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'guru_id' => 'nullable|exists:guru,id',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
        if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $data['status'] === 'terlambat') {
            $data['status'] = 'hadir';
        }

        $absensiSiswa->update($data);

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil diperbarui.');
    }

    public function destroy(AbsensiSiswa $absensiSiswa)
    {
        $absensiSiswa->delete();

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil dihapus.');
    }

    public function scan()
    {
        return view('admin.absensi-siswa.scan');
    }

    public function scanStore(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string',
            'tanggal' => 'nullable|date',
            'status'  => 'nullable|in:hadir,sakit,izin,alpha,terlambat',
        ]);

        $tanggal     = $data['tanggal'] ?? now()->toDateString();
        $currentTime = now()->format('H:i');

        $settings = \App\Models\Pengaturan::whereIn('key', [
            'jam_masuk', 'jam_batas_masuk', 'jam_pulang', 'jam_mulai_pulang', 'jam_akhir_pulang', 'toleransi_terlambat'
        ])->pluck('value', 'key');

        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $siswa = Siswa::where('qr_code', $data['qr_code'])->first();

        if (! $siswa) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'QR code tidak dikenal. Pastikan QR code siswa valid.');
        }

        // Gunakan jam khusus kelas jika diatur
        if ($siswa->kelas_id) {
            $kelas = Kelas::find($siswa->kelas_id);
            if ($kelas && $kelas->kustomisasi_jam) {
                if ($kelas->jam_masuk) {
                    $jamMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->format('H:i');
                    $jamBatasMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->addMinutes($toleransi)->format('H:i');
                }
                if ($kelas->jam_pulang) {
                    $jamMulaiPulang = \Carbon\Carbon::parse($kelas->jam_pulang)->format('H:i');
                }
            }
        }

        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // LOGIKA PULANG
        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($currentTime > $jamAkhirPulang) {
                return redirect()->route('admin.absensi-siswa.scan')
                    ->with('error', 'Sesi scan pulang sudah berakhir (Batas: ' . $jamAkhirPulang . ').');
            }

            if ($absensi->jam_pulang) {
                return redirect()->route('admin.absensi-siswa.scan')
                    ->with('error', 'Siswa ' . $siswa->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang);
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('success', 'Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.');
        }

        if ($absensi) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'Absensi siswa ' . $siswa->nama_lengkap . ' sudah dicatat untuk hari ini.');
        }

        // Cek Batas Masuk
        if ($currentTime > $jamBatasMasuk) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'Sesi scan masuk sudah berakhir (Batas: ' . $jamBatasMasuk . ').');
        }

        // Hitung status jika tidak dipaksa dari form
        $status = $data['status'] ?? 'hadir';
        if ($status === 'hadir') {
            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
            if ($currentTime > $limitHadir) {
                $status = 'terlambat';
            }
        }

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
        if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $status === 'terlambat') {
            $status = 'hadir';
        }

        AbsensiSiswa::create([
            'siswa_id'   => $siswa->id,
            'kelas_id'   => $siswa->kelas_id,
            'tanggal'    => $tanggal,
            'jam_masuk'  => $currentTime,
            'status'     => $status,
            'keterangan' => 'Absensi otomatis via QR scanner',
            'guru_id'    => null,
            'metode'     => 'qr',
        ]);

        return redirect()->route('admin.absensi-siswa.scan')
            ->with('success', 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.' . ($status === 'terlambat' ? ' (TERLAMBAT)' : ''));
    }

    /**
     * Halaman input absensi cepat (semua siswa satu kelas).
     */
    public function bulkForm(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelasRoute = $request->is('wali-kelas/*') || $request->routeIs('wali-kelas.*');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS || $isWaliKelasRoute;
        $isGuru = $activeRole === \App\Models\User::ROLE_GURU;
        $kelasWaliId = null;

        if ($isWaliKelas) {
            $guru = $user->guru;
            if ($guru) {
                $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
                $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $kelasWaliId = $kelasWali->id;
                }
            }
            // Paksa kelas_id ke kelas bimbingan wali kelas
            $selectedKelasId = $kelasWaliId;
            $kelasOptions = $kelasWaliId 
                ? Kelas::where('id', $kelasWaliId)->get() 
                : collect();
        } elseif ($isGuru) {
            $guru = $user->guru;
            $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

            // Tampilkan seluruh pilihan kelas di tahun akademik aktif
            $kelasOptions = Kelas::orderBy('nama');
            if ($tahunAjaranId) {
                $kelasOptions->where('tahun_akademik_id', $tahunAjaranId);
            }
            $kelasOptions = $kelasOptions->get();

            $selectedKelasId = $request->query('kelas_id');

            // Jika belum pilih kelas secara manual, otomatis utamakan kelas yang sedang diampu di jam mengajar hari ini
            if (!$selectedKelasId && $guru) {
                $hariIni = \Carbon\Carbon::now()->locale('id')->isoFormat('dddd');
                $jamSekarang = \Carbon\Carbon::now()->format('H:i:s');

                // 1. Cari kelas di jam pelajaran sekarang
                $jadwalSekarang = \App\Models\JadwalPelajaran::where('guru_id', $guru->id)
                    ->where('hari', $hariIni)
                    ->where('jam_mulai', '<=', $jamSekarang)
                    ->where('jam_selesai', '>=', $jamSekarang)
                    ->first();

                if ($jadwalSekarang) {
                    $selectedKelasId = $jadwalSekarang->kelas_id;
                } else {
                    // 2. Fallback: Cari kelas pada jadwal mengajar hari ini
                    $jadwalHariIni = \App\Models\JadwalPelajaran::where('guru_id', $guru->id)
                        ->where('hari', $hariIni)
                        ->orderBy('jam_mulai')
                        ->first();
                    if ($jadwalHariIni) {
                        $selectedKelasId = $jadwalHariIni->kelas_id;
                    } elseif ($kelasOptions->isNotEmpty()) {
                        $selectedKelasId = $kelasOptions->first()->id;
                    }
                }
            }
        } else {
            $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
            $kelasOptions = Kelas::orderBy('nama');
            if ($tahunAjaranId) {
                $kelasOptions->where('tahun_akademik_id', $tahunAjaranId);
            }
            $kelasOptions = $kelasOptions->get();
            $selectedKelasId = $request->query('kelas_id');
        }

        $siswa = collect();
        if ($selectedKelasId) {
            $siswa = Siswa::with(['absensi' => function($q) use ($request) {
                    $q->whereDate('tanggal', $request->query('tanggal', now()->toDateString()));
                }])
                ->where('kelas_id', $selectedKelasId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->limit(200)
                ->get();
        }

        return view('admin.absensi-siswa.bulk', compact('kelasOptions', 'selectedKelasId', 'siswa', 'isWaliKelas', 'isGuru'));
    }

    /**
     * AJAX: Cari siswa berdasarkan nama / NIS / NISN (tanpa perlu pilih kelas).
     */
    public function searchStudent(Request $request)
    {
        $query = $request->input('query', '');

        if (strlen($query) < 2) {
            return response()->json(['data' => [], 'message' => 'Ketik minimal 2 karakter.']);
        }

        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;
        $isGuru = $activeRole === \App\Models\User::ROLE_GURU;
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $siswaQuery = Siswa::with(['kelas:id,nama'])
            ->where('status', 'aktif')
            ->where(function ($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                  ->orWhere('nis', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            });

        // Filter by tahun akademik via kelas relation
        if ($tahunAjaranId) {
            $siswaQuery->whereHas('kelas', function ($q) use ($tahunAjaranId) {
                $q->where('tahun_akademik_id', $tahunAjaranId);
            });
        }

        // Wali kelas: hanya siswa di kelas bimbingan
        if ($isWaliKelas) {
            $guru = $user->guru;
            if ($guru) {
                $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $siswaQuery->where('kelas_id', $kelasWali->id);
                } else {
                    return response()->json(['data' => [], 'message' => 'Anda belum memiliki kelas bimbingan.']);
                }
            }
        }
        // Guru & Admin dapat mencari siswa di seluruh kelas aktif

        $results = $siswaQuery->orderBy('nama_lengkap')
            ->limit(20)
            ->get()
            ->map(function ($s) {
                return [
                    'id'        => $s->id,
                    'nama'      => $s->nama_lengkap,
                    'nis'       => $s->nis,
                    'nisn'      => $s->nisn,
                    'kelas_id'  => $s->kelas_id,
                    'kelas_nama'=> $s->kelas->nama ?? '-',
                    'label'     => $s->nama_lengkap . ' — ' . ($s->kelas->nama ?? '-'),
                ];
            });

        return response()->json(['data' => $results]);
    }

    /**
     * Simpan absensi cepat per kelas.
     */
    public function bulkStore(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelasRoute = $request->is('wali-kelas/*') || $request->routeIs('wali-kelas.*');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS || $isWaliKelasRoute;
        $isGuru = $activeRole === \App\Models\User::ROLE_GURU;

        if ($isWaliKelas) {
            $guru = $user->guru;
            $kelasWaliId = null;
            if ($guru) {
                $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
                $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $kelasWaliId = $kelasWali->id;
                }
            }
            // Cegah modifikasi kelas lain oleh wali kelas
            if ($request->kelas_id != $kelasWaliId) {
                abort(403, 'Anda hanya diizinkan menginput absensi kelas bimbingan Anda.');
            }
        } elseif ($isGuru) {
            $guru = $user->guru;
            if ($guru) {
                $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
                $guruKelasIds = \App\Models\JadwalPelajaran::where('guru_id', $guru->id)
                    ->pluck('kelas_id')
                    ->unique()
                    ->filter()
                    ->toArray();

                $waliKelasIds = Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->pluck('id')->toArray();
                $guruKelasIds = array_values(array_unique(array_merge($guruKelasIds, $waliKelasIds)));

                if (!in_array($request->kelas_id, $guruKelasIds)) {
                    abort(403, 'Anda hanya diizinkan menginput absensi pada kelas mengajar Anda.');
                }
            }
        }

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal'  => 'required|date',
            'absensi'  => 'required|array',
            'absensi.*.siswa_id'   => 'required|exists:siswa,id',
            'absensi.*.status'     => 'nullable|in:hadir,sakit,izin,alpha,terlambat',
            'absensi.*.keterangan' => 'nullable|string',
        ]);

        $tanggal = $request->tanggal;
        $kelasId = $request->kelas_id;
        $count = 0;

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();

        foreach ($request->absensi as $item) {
            $status = $item['status'] ?? null;
            if (!$status) {
                continue; // Skip jika status belum dipilih oleh admin
            }
            
            if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $status === 'terlambat') {
                $status = 'hadir';
            }

            AbsensiSiswa::updateOrCreate(
                [
                    'siswa_id' => $item['siswa_id'],
                    'tanggal'  => $tanggal,
                ],
                [
                    'kelas_id'   => $kelasId,
                    'status'     => $status,
                    'keterangan' => $item['keterangan'] ?? null,
                    'metode'     => 'manual',
                    'jam_masuk'  => ($status === 'hadir' || $status === 'terlambat') ? now()->format('H:i') : null,
                ]
            );
            $count++;
        }


        if ($isWaliKelasRoute) {
            return redirect()->route('wali-kelas.absensi-siswa.index')
                ->with('success', "Berhasil menyimpan $count data absensi kelas.");
        }

        return redirect()->route('admin.absensi-siswa.index')
            ->with('success', "Berhasil menyimpan $count data absensi kelas.");
    }

    /**
     * Pemicu manual Auto Alpha untuk siswa yang belum absen hingga batas waktu.
     */
    public function triggerAutoAlpha(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('absensi:auto-alpha', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Proses Auto Alpha berhasil dijalankan! Siswa yang belum absen masuk sampai batas waktu telah ditandai Alpha.',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AbsensiSiswaController: Gagal trigger Auto Alpha - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menjalankan Auto Alpha: ' . $e->getMessage()
            ], 500);
        }
    }
}

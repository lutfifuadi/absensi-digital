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
        $perPage       = (int) $request->query('per_page', 50);
        $sortBy        = $request->query('sort_by', 'tanggal');
        $sortDir       = $request->query('sort_dir', 'desc');
        $selectedKelasId = $request->query('kelas_id');
        $selectedStatus  = $request->query('status');
        $tanggalFrom   = $request->query('tanggal_from');
        $tanggalTo     = $request->query('tanggal_to');

        // Validate perPage
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 50;
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

    public function create()
    {
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        $kelasOptions = Kelas::orderBy('nama')->get();
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
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        $kelasOptions = Kelas::orderBy('nama')->get();
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
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;
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
        } else {
            $kelasOptions = Kelas::orderBy('nama')->get();
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

        return view('admin.absensi-siswa.bulk', compact('kelasOptions', 'selectedKelasId', 'siswa', 'isWaliKelas'));
    }

    /**
     * Simpan absensi cepat per kelas.
     */
    public function bulkStore(Request $request)
    {
        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;

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
        }

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal'  => 'required|date',
            'absensi'  => 'required|array',
            'absensi.*.siswa_id'  => 'required|exists:siswa,id',
            'absensi.*.status'    => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'absensi.*.keterangan' => 'nullable|string',
        ]);

        $tanggal = $request->tanggal;
        $kelasId = $request->kelas_id;
        $count = 0;

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();

        foreach ($request->absensi as $item) {
            // Jika status tidak dipilih (fallback ke hadir)
            $status = $item['status'] ?? 'hadir';
            
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

        return redirect()->route('admin.absensi-siswa.index')
            ->with('success', "Berhasil menyimpan $count data absensi kelas.");
    }

    public function manualCreate(Request $request)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;

        if (!$isWaliKelas) {
            abort(403, 'Akses khusus Wali Kelas.');
        }

        $guru = $user->guru;
        $kelasWaliId = null;
        $siswaOptions = collect();

        if ($guru) {
            $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                ->where('tahun_akademik_id', $tahunAjaranId)
                ->first();
            if ($kelasWali) {
                $kelasWaliId = $kelasWali->id;
                $siswaOptions = Siswa::where('kelas_id', $kelasWaliId)
                    ->where('status', 'aktif')
                    ->orderBy('nama_lengkap')
                    ->get();
            }
        }

        $selectedSiswaId = $request->query('siswa_id');
        $selectedTanggal = $request->query('tanggal', now()->toDateString());

        return view('admin.absensi-siswa.manual', compact('siswaOptions', 'selectedSiswaId', 'selectedTanggal', 'kelasWaliId'));
    }

    public function manualStore(Request $request)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;

        if (!$isWaliKelas) {
            abort(403, 'Akses khusus Wali Kelas.');
        }

        $guru = $user->guru;
        $kelasWaliId = null;
        if ($guru) {
            $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                ->where('tahun_akademik_id', $tahunAjaranId)
                ->first();
            if ($kelasWali) {
                $kelasWaliId = $kelasWali->id;
            }
        }

        if (!$kelasWaliId) {
            return back()->with('error', 'Anda belum ditugaskan sebagai wali kelas di tahun akademik ini.');
        }

        $data = $request->validate([
            'siswa_id' => [
                'required',
                'exists:siswa,id',
                \Illuminate\Validation\Rule::exists('siswa', 'id')->where('kelas_id', $kelasWaliId)
            ],
            'tanggal' => 'required|date',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $duplicate = AbsensiSiswa::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors(['siswa_id' => 'Siswa sudah memiliki catatan absensi pada tanggal tersebut.']);
        }

        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
        if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $data['status'] === 'terlambat') {
            $data['status'] = 'hadir';
        }

        AbsensiSiswa::create([
            'siswa_id' => $data['siswa_id'],
            'kelas_id' => $kelasWaliId,
            'tanggal' => $data['tanggal'],
            'status' => $data['status'],
            'keterangan' => $data['keterangan'],
            'guru_id' => $guru?->id,
            'metode' => 'manual',
            'jam_masuk' => ($data['status'] === 'hadir' || $data['status'] === 'terlambat') ? now()->format('H:i') : null,
        ]);

        return redirect()->route('wali-kelas.belum-absen', ['tanggal' => $data['tanggal']])
            ->with('success', 'Absensi berhasil disimpan secara manual.');
    }
}

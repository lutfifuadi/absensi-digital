<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\User;
use App\Exports\RekapBulananSiswaExport;
use App\Exports\RekapBulananGuruExport;
use App\Exports\RekapBulananStaffExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Dapatkan kelas khusus jika user yang sedang login bertindak sebagai Wali Kelas.
     */
    private function getWaliKelasAssignedClass()
    {
        $user = auth()->user();
        if (!$user) return null;

        // Super Admin, Admin Sekolah, Operator memiliki akses penuh ke seluruh kelas
        if ($user->isSuperAdmin() || $user->isRole(User::ROLE_ADMIN_SEKOLAH) || $user->isRole(User::ROLE_OPERATOR)) {
            return null;
        }

        // Cari data Guru berdasarkan user_id
        $guru = \App\Models\Guru::where('user_id', $user->id)->first();
        if ($guru) {
            $assignedClass = Kelas::where('wali_kelas_id', $guru->id)->first();
            if ($assignedClass) {
                return $assignedClass;
            }
        }

        // Fallback jika user memiliki role wali_kelas
        if ($user->isRole(User::ROLE_WALI_KELAS)) {
            $assignedClass = Kelas::whereHas('waliKelas', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();
            return $assignedClass;
        }

        return null;
    }

    public function index(Request $request)
    {
        $assignedClass = $this->getWaliKelasAssignedClass();
        $isWaliKelasLocked = $assignedClass !== null;

        // Jika Wali Kelas, kunci otomatis kelas_id ke kelas yang diampunya
        $kelasId = $assignedClass ? $assignedClass->id : $request->input('kelas_id');
        $kelasOptions = $assignedClass ? collect([$assignedClass]) : Kelas::orderBy('nama')->get();

        $filters = [
            'kelas_id' => $kelasId,
            'bulan'    => (int) $request->input('bulan', now()->month),
            'tahun'    => (int) $request->input('tahun', now()->year),
        ];

        $kelas = $filters['kelas_id'] ? Kelas::find($filters['kelas_id']) : null;
        $siswaList = collect();
        $absensiPivot = [];

        if ($filters['kelas_id']) {
            $siswaList = Siswa::where('kelas_id', $filters['kelas_id'])
                ->orderBy('nama_lengkap')
                ->get();

            if ($siswaList->isNotEmpty()) {
                $daysInMonth = Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->daysInMonth;
                $startDate = Carbon::createFromDate($filters['tahun'], $filters['bulan'], 1)->toDateString();
                $endDate = Carbon::createFromDate($filters['tahun'], $filters['bulan'], $daysInMonth)->toDateString();

                $absensiRows = AbsensiSiswa::whereIn('siswa_id', $siswaList->pluck('id'))
                    ->whereBetween('tanggal', [$startDate, $endDate])
                    ->select('siswa_id', 'tanggal', 'status')
                    ->get()
                    ->groupBy('siswa_id');

                $dates = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dates[] = Carbon::createFromDate($filters['tahun'], $filters['bulan'], $d)->format('Y-m-d');
                }

                foreach ($siswaList as $s) {
                    $rows = $absensiRows->get($s->id, collect())->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));
                    foreach ($dates as $date) {
                        $st = $rows->get($date)?->status ?? null;
                        // Cek jika tanggal libur bagi siswa (Holidays + Jadwal Kelas + Weekend)
                        if (\App\Helpers\JadwalAbsensiHelper::isHariLiburSiswa($s, $date)) {
                            if ($st === 'alpha' || $st === null) {
                                $st = 'libur';
                            }
                        }
                        $absensiPivot[$s->id][$date] = $st;
                    }
                }
            }
        }

        $dates = $dates ?? [];
        
        // Hitung Summary Stats dari $absensiPivot agar mengabaikan Hari Libur
        $summary = (object) [
            'total'     => 0,
            'hadir'     => 0,
            'izin'      => 0,
            'sakit'     => 0,
            'alpha'     => 0,
            'terlambat' => 0,
        ];

        foreach ($absensiPivot as $sId => $datesMap) {
            foreach ($datesMap as $dStr => $st) {
                if ($st === 'hadir') { $summary->hadir++; $summary->total++; }
                elseif ($st === 'izin') { $summary->izin++; $summary->total++; }
                elseif ($st === 'sakit') { $summary->sakit++; $summary->total++; }
                elseif ($st === 'alpha') { $summary->alpha++; $summary->total++; }
                elseif ($st === 'terlambat') { $summary->terlambat++; $summary->total++; }
            }
        }

        return view('admin.laporan.index', compact(
            'kelasOptions', 'filters', 'summary', 'siswaList', 'dates', 'absensiPivot', 'kelas',
            'isWaliKelasLocked', 'assignedClass'
        ));
    }

    public function exportExcel(Request $request)
    {
        $assignedClass = $this->getWaliKelasAssignedClass();
        $kelasId = $assignedClass ? $assignedClass->id : ($request->input('kelas_id') ? (int) $request->kelas_id : null);
        $bulan   = (int) $request->input('bulan', now()->month);
        $tahun   = (int) $request->input('tahun', now()->year);

        return Excel::download(
            new RekapBulananSiswaExport($bulan, $tahun, $kelasId),
            sprintf('rekap-absensi-siswa-%04d-%02d.xlsx', $tahun, $bulan)
        );
    }

    public function exportPdf(Request $request)
    {
        $assignedClass = $this->getWaliKelasAssignedClass();
        $kelasId      = $assignedClass ? $assignedClass->id : $request->input('kelas_id');
        $bulan        = (int) $request->input('bulan', now()->month);
        $tahun        = (int) $request->input('tahun', now()->year);
        $tipeLaporan  = $request->input('tipe_laporan', 'matriks');

        $kelas = $kelasId ? Kelas::find($kelasId) : null;
        $namaBulan   = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
        $namaSekolah   = setting('nama_sekolah') ?: setting('nama_lembaga') ?: 'Madrasah Aliyah';
        $kepalaSekolah = setting('nama_kepala_lembaga') ?: setting('kepala_sekolah') ?: setting('nama_kepala_sekolah') ?: setting('kepala_lembaga') ?: '';
        $nipKepala     = setting('nip_kepala_lembaga') ?: setting('nip_kepala_sekolah') ?: setting('nip_kepala') ?: '';

        if ($tipeLaporan === 'detail') {
            $absensiLogs = AbsensiSiswa::with(['siswa.kelas', 'guru'])
                ->when($kelasId, fn ($q) => $q->where('kelas_id', $kelasId))
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->orderBy('tanggal', 'asc')
                ->orderBy('kelas_id', 'asc')
                ->get();

            $pdf = Pdf::loadView('admin.laporan.rekap-detail-pdf', compact(
                'absensiLogs', 'kelas', 'bulan', 'tahun', 'namaBulan', 'namaSekolah', 'kepalaSekolah', 'nipKepala'
            ))->setPaper('a4', 'landscape');

            return $pdf->download(sprintf('rincian-jam-presensi-%04d-%02d.pdf', $tahun, $bulan));
        }

        $siswaList = $kelasId
            ? Siswa::where('kelas_id', $kelasId)->orderBy('nama_lengkap')->get()
            : collect();

        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
        }

        $absensiPivot = [];
        if ($siswaList->isNotEmpty()) {
            $absensiRows = AbsensiSiswa::whereIn('siswa_id', $siswaList->pluck('id'))
                ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
                ->get()->groupBy('siswa_id');

            foreach ($siswaList as $s) {
                $rows = $absensiRows->get($s->id, collect())->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));
                foreach ($dates as $date) {
                    $st = $rows->get($date)?->status ?? null;
                    if (\App\Helpers\JadwalAbsensiHelper::isHariLiburSiswa($s, $date)) {
                        if ($st === 'alpha' || $st === null) {
                            $st = 'libur';
                        }
                    }
                    $absensiPivot[$s->id][$date] = $st;
                }
            }
        }

        $pdf = Pdf::loadView('admin.laporan.rekap-pdf', compact(
            'siswaList', 'dates', 'absensiPivot', 'kelas',
            'bulan', 'tahun', 'namaBulan', 'namaSekolah', 'kepalaSekolah', 'nipKepala'
        ))->setPaper('a4', 'landscape');

        return $pdf->download(sprintf('rekap-absensi-%04d-%02d.pdf', $tahun, $bulan));
    }

    public function absensiHariIni(Request $request)
    {
        $tanggal = now()->toDateString();

        $assignedClass = $this->getWaliKelasAssignedClass();
        $isWaliKelasLocked = $assignedClass !== null;
        $kelasId = $assignedClass ? $assignedClass->id : $request->input('kelas_id');
        $kelasOptions = $assignedClass ? collect([$assignedClass]) : Kelas::orderBy('nama')->get();

        $query = \App\Models\Siswa::with(['kelas', 'absensi' => function($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
        ->leftJoin('absensi_siswa', function($join) use ($tanggal) {
            $join->on('siswa.id', '=', 'absensi_siswa.siswa_id')
                 ->whereDate('absensi_siswa.tanggal', $tanggal);
        })
        ->select('siswa.*');

        if ($kelasId) {
            $query->where('siswa.kelas_id', $kelasId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('siswa.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('siswa.nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'belum' || $status === 'belum_absen') {
                $query->whereNull('absensi_siswa.id');
            } else {
                $query->where('absensi_siswa.status', $status);
            }
        }

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $siswa = $query->orderBy('siswa.nama_lengkap')->paginate($perPage)->withQueryString();
        $siswaList = $siswa;

        $totalSiswa = $kelasId ? Siswa::where('kelas_id', $kelasId)->count() : Siswa::count();
        $absensiStats = AbsensiSiswa::whereDate('tanggal', $tanggal)
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->selectRaw("
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status='terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status='izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) as alpha
            ")->first();

        $hadir = (int) ($absensiStats->hadir ?? 0);
        $terlambat = (int) ($absensiStats->terlambat ?? 0);
        $izin = (int) ($absensiStats->izin ?? 0);
        $sakit = (int) ($absensiStats->sakit ?? 0);
        $alpha = (int) ($absensiStats->alpha ?? 0);
        $belum = max(0, $totalSiswa - ($hadir + $terlambat + $izin + $sakit + $alpha));

        $summary = [
            'total' => $totalSiswa,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'belum' => $belum,
            'belum_absen' => $belum,
        ];

        return view('admin.laporan.absensi-hari-ini', compact('kelasOptions', 'summary', 'siswa', 'siswaList', 'isWaliKelasLocked', 'assignedClass'));
    }

    public function exportExcelGuru(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        return Excel::download(
            new RekapBulananGuruExport($bulan, $tahun),
            sprintf('rekap-absensi-guru-%04d-%02d.xlsx', $tahun, $bulan)
        );
    }

    public function exportExcelStaff(Request $request)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        return Excel::download(
            new RekapBulananStaffExport($bulan, $tahun),
            sprintf('rekap-absensi-staff-%04d-%02d.xlsx', $tahun, $bulan)
        );
    }

    public function rekapHarian(Request $request)
    {
        $assignedClass = $this->getWaliKelasAssignedClass();
        $isWaliKelasLocked = $assignedClass !== null;

        $tanggal = $request->input('tanggal', now()->toDateString());
        $kelasId = $assignedClass ? $assignedClass->id : $request->input('kelas_id');
        $kelasOptions = $assignedClass ? collect([$assignedClass]) : Kelas::orderBy('nama')->get();

        $kelas = $kelasId ? Kelas::find($kelasId) : null;

        $siswaQuery = Siswa::with(['kelas', 'absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }]);

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $siswaList = $siswaQuery->orderBy('nama_lengkap')->get();

        $summary = [
            'total' => $siswaList->count(),
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'belum' => 0,
        ];

        foreach ($siswaList as $s) {
            $absen = $s->absensi->first();
            $st = $absen ? strtolower($absen->status) : 'belum';
            if (isset($summary[$st])) {
                $summary[$st]++;
            } else {
                $summary['belum']++;
            }
        }

        return view('admin.laporan.rekap-harian', compact(
            'siswaList', 'kelasOptions', 'summary', 'tanggal', 'kelasId', 'kelas',
            'isWaliKelasLocked', 'assignedClass'
        ));
    }

    public function belumAbsen(Request $request)
    {
        $assignedClass = $this->getWaliKelasAssignedClass();
        $isWaliKelasLocked = $assignedClass !== null;

        $tanggal = $request->input('tanggal', now()->toDateString());
        $kelasId = $assignedClass ? $assignedClass->id : $request->input('kelas_id');
        $kelasOptions = $assignedClass ? collect([$assignedClass]) : Kelas::orderBy('nama')->get();

        $kelas = $kelasId ? Kelas::find($kelasId) : null;

        $siswaQuery = Siswa::with(['kelas'])
            ->whereDoesntHave('absensi', function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            });

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $siswaList = $siswaQuery->orderBy('nama_lengkap')->get();

        return view('admin.laporan.belum-absen', compact(
            'siswaList', 'kelasOptions', 'tanggal', 'kelasId', 'kelas',
            'isWaliKelasLocked', 'assignedClass'
        ));
    }
}

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
                        $absensiPivot[$s->id][$date] = $rows->get($date)?->status ?? null;
                    }
                }
            }
        }

        $dates = $dates ?? [];
        $summary = $filters['kelas_id'] ? AbsensiSiswa::where('kelas_id', $filters['kelas_id'])
            ->whereYear('tanggal', $filters['tahun'])
            ->whereMonth('tanggal', $filters['bulan'])
            ->selectRaw("COUNT(*) as total,
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status='izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) as alpha,
                SUM(CASE WHEN status='terlambat' THEN 1 ELSE 0 END) as terlambat")
            ->first() : null;

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
                    $absensiPivot[$s->id][$date] = $rows->get($date)?->status ?? null;
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
        $query = \App\Models\Siswa::with(['kelas', 'absensi' => function($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }])
        ->leftJoin('absensi_siswa', function($join) use ($tanggal) {
            $join->on('siswa.id', '=', 'absensi_siswa.siswa_id')
                 ->whereDate('absensi_siswa.tanggal', $tanggal);
        })
        ->select('siswa.*');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('siswa.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('siswa.nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('siswa.kelas_id', $request->input('kelas_id'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'belum') {
                $query->whereNull('absensi_siswa.id');
            } else {
                $query->where('absensi_siswa.status', $status);
            }
        }

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 25;

        $siswaList = $query->orderBy('siswa.nama_lengkap')->paginate($perPage)->withQueryString();
        $kelasOptions = Kelas::orderBy('nama')->get();

        $summary = [
            'total' => \App\Models\Siswa::count(),
            'hadir' => AbsensiSiswa::whereDate('tanggal', $tanggal)->where('status', 'hadir')->count(),
            'terlambat' => AbsensiSiswa::whereDate('tanggal', $tanggal)->where('status', 'terlambat')->count(),
            'izin' => AbsensiSiswa::whereDate('tanggal', $tanggal)->where('status', 'izin')->count(),
            'sakit' => AbsensiSiswa::whereDate('tanggal', $tanggal)->where('status', 'sakit')->count(),
            'alpha' => AbsensiSiswa::whereDate('tanggal', $tanggal)->where('status', 'alpha')->count(),
        ];
        $summary['belum'] = max(0, $summary['total'] - ($summary['hadir'] + $summary['terlambat'] + $summary['izin'] + $summary['sakit'] + $summary['alpha']));

        return view('admin.laporan.index', compact('kelasOptions', 'filters', 'summary', 'siswaList', 'dates', 'absensiPivot', 'kelas'));
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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapBulananGuruExport;
use App\Exports\RekapBulananSiswaExport;
use App\Exports\RekapBulananStaffExport;
use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        $filters = [
            'kelas_id' => $request->input('kelas_id'),
            'bulan'    => (int) $request->input('bulan', now()->month),
            'tahun'    => (int) $request->input('tahun', now()->year),
        ];

        $kelas = $filters['kelas_id'] ? Kelas::find($filters['kelas_id']) : null;
        $siswaList = collect();
        $absensiPivot = [];

        if ($filters['kelas_id']) {
            $siswaList = Siswa::where('kelas_id', $filters['kelas_id'])
                ->orderBy('nama_lengkap')
                ->paginate(10)
                ->withQueryString();

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

        return view('admin.laporan.index', compact('kelasOptions', 'filters', 'summary', 'siswaList', 'dates', 'absensiPivot', 'kelas'));
    }

    public function exportExcel(Request $request)
    {
        $bulan   = (int) $request->input('bulan', now()->month);
        $tahun   = (int) $request->input('tahun', now()->year);
        $kelasId = $request->input('kelas_id') ? (int) $request->kelas_id : null;

        return Excel::download(
            new RekapBulananSiswaExport($bulan, $tahun, $kelasId),
            sprintf('rekap-absensi-siswa-%04d-%02d.xlsx', $tahun, $bulan)
        );
    }

    public function exportPdf(Request $request)
    {
        $bulan   = (int) $request->input('bulan', now()->month);
        $tahun   = (int) $request->input('tahun', now()->year);
        $kelasId = $request->input('kelas_id');

        $kelas = $kelasId ? Kelas::find($kelasId) : null;
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

        $namaBulan   = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
        $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
        $kepalaSekolah = Pengaturan::where('key', 'kepala_sekolah')->value('value') ?? '-';
        $nipKepala     = Pengaturan::where('key', 'nip_kepala_sekolah')->value('value') ?? '';

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

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('siswa.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('siswa.nis', 'like', "%{$search}%");
            });
        }

        // Kelas filter
        if ($request->filled('kelas_id')) {
            $query->where('siswa.kelas_id', $request->input('kelas_id'));
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'belum_absen') {
                $query->whereDoesntHave('absensi', function($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                });
            } else {
                $query->whereHas('absensi', function($q) use ($tanggal, $status) {
                    $q->whereDate('tanggal', $tanggal)->where('status', $status);
                });
            }
        } else {
            // Default: only show students who have check-in/absensi today
            $query->whereHas('absensi', function($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
            });
        }

        $siswa = $query->orderByRaw('CASE WHEN absensi_siswa.jam_masuk IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('absensi_siswa.jam_masuk', 'asc')
            ->orderBy('siswa.nama_lengkap', 'asc')
            ->paginate(10)
            ->withQueryString();

        $today = now()->toDateString();
        $totalSiswa = \App\Models\Siswa::count();
        $stats = \App\Models\AbsensiSiswa::whereDate('tanggal', $today)
            ->selectRaw("
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status='terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status='izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) as alpha
            ")->first();

        $summary = [
            'total' => $totalSiswa,
            'hadir' => ($stats->hadir ?? 0) + ($stats->terlambat ?? 0),
            'terlambat' => $stats->terlambat ?? 0,
            'sakit' => $stats->sakit ?? 0,
            'izin' => $stats->izin ?? 0,
            'alpha' => $stats->alpha ?? 0,
            'belum_absen' => $totalSiswa - (($stats->hadir ?? 0) + ($stats->terlambat ?? 0) + ($stats->sakit ?? 0) + ($stats->izin ?? 0) + ($stats->alpha ?? 0))
        ];

        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = \App\Models\Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();

        return view('admin.laporan.absensi-hari-ini', compact('siswa', 'summary', 'kelasOptions'));
    }

    public function rekapHarian(Request $request)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $user = auth()->user();
        $activeRole = session('active_role', $user ? $user->role : 'guest');
        $isWaliKelas = $activeRole === \App\Models\User::ROLE_WALI_KELAS;
        $kelasWaliId = null;

        // Ambil rentang tanggal dari request, default hari ini
        $tanggalMulai = $request->input('tanggal_mulai', now()->toDateString());
        $tanggalSelesai = $request->input('tanggal_selesai', now()->toDateString());
        $kelasId = $request->input('kelas_id');

        if ($isWaliKelas) {
            $guru = $user->guru;
            if ($guru) {
                $kelasWali = Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $kelasWaliId = $kelasWali->id;
                }
            }
            // Paksa kelasId ke kelas bimbingan wali kelas
            $kelasId = $kelasWaliId;
            $kelasOptions = $kelasWaliId 
                ? Kelas::where('id', $kelasWaliId)->get() 
                : collect();
        } else {
            $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)->orderBy('nama')->get();
        }

        // Query absensi siswa dengan filter rentang tanggal
        $querySiswa = AbsensiSiswa::with(['siswa:id,nama_lengkap,kelas_id', 'kelas:id,nama', 'guru:id,nama_lengkap'])
            ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
            ->orderBy('tanggal', 'desc')
            ->orderBy('siswa_id');

        if ($isWaliKelas) {
            if ($kelasWaliId) {
                $querySiswa->where('absensi_siswa.kelas_id', $kelasWaliId);
            } else {
                $querySiswa->whereNull('absensi_siswa.id');
            }
        } elseif ($kelasId) {
            $querySiswa->where('kelas_id', $kelasId);
        }

        $absensiSiswa = $querySiswa->paginate(100)->withQueryString();

        // Absensi Guru dan Staff (hanya untuk Admin/Operator, kosongkan untuk Wali Kelas)
        if (!$isWaliKelas) {
            $absensiGuru = AbsensiGuru::with('guru:id,nama_lengkap')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal', 'desc')
                ->orderBy('guru_id')
                ->paginate(100)->withQueryString();

            $absensiStaff = AbsensiStaff::with('staff:id,nama_lengkap')
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                ->orderBy('tanggal', 'desc')
                ->orderBy('staff_id')
                ->paginate(100)->withQueryString();
        } else {
            $absensiGuru = collect();
            $absensiStaff = collect();
        }

        // Statistiche rekap dalam periode
        $qSummary = AbsensiSiswa::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai]);
        if ($isWaliKelas) {
            if ($kelasWaliId) {
                $qSummary->where('kelas_id', $kelasWaliId);
            } else {
                $qSummary->whereNull('id');
            }
        } elseif ($kelasId) {
            $qSummary->where('kelas_id', $kelasId);
        }

        $summaryStats = (clone $qSummary)
            ->selectRaw("
                SUM(CASE WHEN status='hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status='sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status='izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status='alpha' THEN 1 ELSE 0 END) as alpha,
                SUM(CASE WHEN status='terlambat' THEN 1 ELSE 0 END) as terlambat
            ")->first();

        $summaryHarian = [
            'siswa_hadir'    => $summaryStats->hadir ?? 0,
            'siswa_sakit'   => $summaryStats->sakit ?? 0,
            'siswa_izin'    => $summaryStats->izin ?? 0,
            'siswa_alpha'   => $summaryStats->alpha ?? 0,
            'siswa_terlambat'=> $summaryStats->terlambat ?? 0,
            'guru_hadir'    => !$isWaliKelas ? AbsensiGuru::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])->where('status', 'hadir')->count() : 0,
            'staff_hadir'   => !$isWaliKelas ? AbsensiStaff::whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])->where('status', 'hadir')->count() : 0,
        ];

        return view('admin.laporan.rekap-harian', compact(
            'absensiSiswa', 'absensiGuru', 'absensiStaff',
            'tanggalMulai', 'tanggalSelesai', 'kelasOptions', 'kelasId', 'summaryHarian', 'isWaliKelas'
        ));
    }

    public function individualSiswa(Request $request, Siswa $siswa)
    {
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal')
            ->get();

        $summary = [
            'hadir'     => $absensi->where('status', 'hadir')->count(),
            'sakit'     => $absensi->where('status', 'sakit')->count(),
            'izin'      => $absensi->where('status', 'izin')->count(),
            'alpha'     => $absensi->where('status', 'alpha')->count(),
            'terlambat' => $absensi->where('status', 'terlambat')->count(),
        ];

        return view('admin.laporan.individual-siswa', compact('siswa', 'absensi', 'summary', 'bulan', 'tahun'));
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

    public function reset(Request $request)
    {
        $request->validate([
            'confirm' => 'required|string|in:RESET',
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        AbsensiSiswa::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        return redirect()->route('admin.laporan.index')
            ->with('success', 'Semua data kehadiran berhasil dihapus!');
    }
}

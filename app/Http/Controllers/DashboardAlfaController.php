<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAlfaController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $isWeekend = $today->isSaturday() || $today->isSunday();
        $start7Days = Carbon::now()->subDays(6)->startOfDay();

        // Filter
        $filterKelas = $request->input('kelas_id');
        $filterTanggalMulai = $request->input('start_date', $start7Days->format('Y-m-d'));
        $filterTanggalAkhir = $request->input('end_date', $today->format('Y-m-d'));
        $filterDate = Carbon::parse($filterTanggalAkhir);

        // ══════════════════════════════════════════════════════════
        // 1. Total Siswa Aktif
        // ══════════════════════════════════════════════════════════
        $querySiswaAktif = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $querySiswaAktif->where('kelas_id', $filterKelas);
        }
        $totalSiswaAktif = $querySiswaAktif->count();

        // ══════════════════════════════════════════════════════════
        // 2. Siswa yang BELUM Absen Hari Ini
        // ══════════════════════════════════════════════════════════
        $siswaAktifHariIni = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $siswaAktifHariIni->where('kelas_id', $filterKelas);
        }
        $idsAktifHariIni = $siswaAktifHariIni->pluck('id');

        $idsSudahAbsenHariIni = AbsensiSiswa::where('tanggal', $today)
            ->whereIn('siswa_id', $idsAktifHariIni)
            ->pluck('siswa_id')
            ->unique();

        $totalBelumAbsenHariIni = $idsAktifHariIni->diff($idsSudahAbsenHariIni)->count();

        // ══════════════════════════════════════════════════════════
        // 3. Bar Chart: Belum Absen per Kelas (berdasarkan tanggal akhir filter)
        // ══════════════════════════════════════════════════════════
        $kelasQuery = Kelas::query();
        if ($filterKelas) {
            $kelasQuery->where('kelas.id', $filterKelas);
        }
        $allKelas = $kelasQuery->orderBy('nama')->get();

        $barChartLabels = [];
        $barChartData = [];

        foreach ($allKelas as $kelasItem) {
            // Siswa aktif di kelas ini
            $siswaIds = Siswa::where('status', 'aktif')
                ->where('kelas_id', $kelasItem->id)
                ->pluck('id');

            if ($siswaIds->isEmpty()) {
                continue;
            }

            // Yang sudah absen di tanggal akhir filter
            $sudahAbsen = AbsensiSiswa::where('tanggal', $filterDate)
                ->whereIn('siswa_id', $siswaIds)
                ->count();

            $belumAbsen = $siswaIds->count() - $sudahAbsen;

            if ($belumAbsen > 0) {
                $barChartLabels[] = $kelasItem->nama;
                $barChartData[] = $belumAbsen;
            }
        }

        // ══════════════════════════════════════════════════════════
        // 4. Line Chart: Tren Belum Absen per Hari (7 hari kerja terakhir, skip weekend)
        // ══════════════════════════════════════════════════════════
        $trendDates = [];
        $trendData = [];

        // Kumpulkan 7 hari kerja terakhir
        $hariKerja = collect();
        $tmp = $today->copy();
        while ($hariKerja->count() < 7) {
            if (!$tmp->isSaturday() && !$tmp->isSunday()) {
                $hariKerja->prepend($tmp->copy());
            }
            $tmp->subDay();
        }

        $idsAktifAll = Siswa::where('status', 'aktif')->pluck('id');
        foreach ($hariKerja as $date) {
            $dateStr = $date->format('Y-m-d');
            $sudahAbsen = AbsensiSiswa::where('tanggal', $dateStr)
                ->whereIn('siswa_id', $idsAktifAll)
                ->count();

            $trendDates[] = $date->format('d M');
            $trendData[] = $idsAktifAll->count() - $sudahAbsen;
        }

        // ══════════════════════════════════════════════════════════
        // 5. Detail Tabel: Daftar Siswa yang Belum Absen
        // ══════════════════════════════════════════════════════════
        $queryDetail = Siswa::with(['kelas'])
            ->where('status', 'aktif');

        if ($filterKelas) {
            $queryDetail->where('kelas_id', $filterKelas);
        }

        // Subquery: ambil siswa yang sudah absen di rentang tanggal filter
        $siswaSudahAbsen = AbsensiSiswa::whereBetween('tanggal', [$filterTanggalMulai, $filterTanggalAkhir])
            ->pluck('siswa_id')
            ->unique();

        // Filter: hanya tampilkan yang belum absen di rentang tanggal tsb
        $queryDetail->whereNotIn('id', $siswaSudahAbsen);

        $detailBelumAbsen = $queryDetail->orderBy('nama_lengkap')->paginate(10);
        $kelasList = Kelas::orderBy('nama')->get();

        $data = [
            'isWeekend' => $isWeekend,
            'totalSiswaAktif' => $totalSiswaAktif,
            'totalBelumAbsenHariIni' => $totalBelumAbsenHariIni,
            'barChartLabels' => $barChartLabels,
            'barChartData' => $barChartData,
            'lineChartLabels' => $trendDates,
            'lineChartData' => $trendData,
            'detailBelumAbsen' => $detailBelumAbsen,
            'kelasList' => $kelasList,
            // Filter states
            'filterKelas' => $filterKelas,
            'filterTanggalMulai' => $filterTanggalMulai,
            'filterTanggalAkhir' => $filterTanggalAkhir,
        ];

        return view('admin.dashboard.alfa', $data);
    }
}

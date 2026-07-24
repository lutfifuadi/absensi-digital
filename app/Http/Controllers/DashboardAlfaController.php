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
        $start7Days = Carbon::now()->subDays(6)->startOfDay(); // 7 hari terakhir (termasuk hari ini)

        // 1. Total Alfa Hari Ini & 7 Hari Terakhir
        $totalAlfaHariIni = AbsensiSiswa::where('tanggal', $today)
            ->where('status', 'alfa')
            ->count();

        $totalAlfa7Hari = AbsensiSiswa::whereBetween('tanggal', [$start7Days, $today])
            ->where('status', 'alfa')
            ->count();

        // 2. Data Bar Chart (Alfa per Kelas — 7 hari terakhir)
        $alfaPerKelas = Kelas::select('kelas.id', 'kelas.nama')
            ->leftJoin('absensi_siswa', function ($join) use ($start7Days, $today) {
                $join->on('kelas.id', '=', 'absensi_siswa.kelas_id')
                    ->whereBetween('absensi_siswa.tanggal', [$start7Days, $today])
                    ->where('absensi_siswa.status', 'alfa');
            })
            ->selectRaw('COUNT(absensi_siswa.id) as total_alfa')
            ->groupBy('kelas.id', 'kelas.nama')
            ->orderByDesc('total_alfa')
            ->get();

        // 3. Data Line Chart (Tren Waktu per Hari — 7 hari terakhir)
        $alfaTrend = AbsensiSiswa::select(DB::raw('DATE(tanggal) as date'), DB::raw('COUNT(id) as total'))
            ->whereBetween('tanggal', [$start7Days, $today])
            ->where('status', 'alfa')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Buat rentang tanggal untuk sumbu X
        $trendDates = [];
        $trendData = [];
        for ($date = $start7Days->copy(); $date->lte(Carbon::today()); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $trendDates[] = $date->format('d M');
            $trendData[] = isset($alfaTrend[$dateString]) ? $alfaTrend[$dateString]->total : 0;
        }

        // 4. Data Detail Tabel (Pagination)
        // Ambil filter jika ada
        $filterKelas = $request->input('kelas_id');
        $filterTanggalMulai = $request->input('start_date', $start7Days->format('Y-m-d'));
        $filterTanggalAkhir = $request->input('end_date', $today->format('Y-m-d'));

        $queryDetail = AbsensiSiswa::with(['siswa', 'kelas'])
            ->where('status', 'alfa');

        if ($filterKelas) {
            $queryDetail->where('kelas_id', $filterKelas);
        }

        if ($filterTanggalMulai && $filterTanggalAkhir) {
            $queryDetail->whereBetween('tanggal', [$filterTanggalMulai, $filterTanggalAkhir]);
        } else {
            // Default: 7 hari terakhir
            $queryDetail->whereBetween('tanggal', [$start7Days, $today]);
        }

        $detailAlfa = $queryDetail->orderBy('tanggal', 'desc')->paginate(10);
        $kelasList = Kelas::orderBy('nama')->get();

        $data = [
            'totalAlfaHariIni' => $totalAlfaHariIni,
            'totalAlfa7Hari' => $totalAlfa7Hari,
            'barChartLabels' => $alfaPerKelas->pluck('nama'),
            'barChartData' => $alfaPerKelas->pluck('total_alfa'),
            'lineChartLabels' => $trendDates,
            'lineChartData' => $trendData,
            'detailAlfa' => $detailAlfa,
            'kelasList' => $kelasList,
            // Filter states
            'filterKelas' => $filterKelas,
            'filterTanggalMulai' => $filterTanggalMulai,
            'filterTanggalAkhir' => $filterTanggalAkhir,
        ];

        return view('admin.dashboard.alfa', $data);
    }
}

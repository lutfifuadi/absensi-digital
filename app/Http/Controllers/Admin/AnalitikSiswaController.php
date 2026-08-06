<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalitikSiswaController extends Controller
{
    /**
     * Tampilkan Halaman Utama Analitik Kehadiran Siswa
     */
    public function index(Request $request)
    {
        $kelases = Kelas::orderBy('nama', 'asc')->get();
        $jurusans = Jurusan::orderBy('nama', 'asc')->get();

        return view('admin.analitik-siswa.index', [
            'pageTitle' => 'Grafik & Analitik Kehadiran Siswa',
            'kelases' => $kelases,
            'jurusans' => $jurusans,
        ]);
    }

    /**
     * API JSON Endpoint untuk menyuplai data chart secara AJAX
     */
    public function getData(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        $kelasId = $request->input('kelas_id');
        $tingkat = $request->input('tingkat');
        $jurusanId = $request->input('jurusan_id');

        // Query Dasar Absensi
        $query = AbsensiSiswa::query()
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($kelasId && $kelasId !== 'all') {
            $query->where('kelas_id', $kelasId);
        }

        if ($tingkat && $tingkat !== 'all') {
            $query->whereHas('kelas', function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat);
            });
        }

        if ($jurusanId && $jurusanId !== 'all') {
            $query->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }

        $allRecords = (clone $query)->get();

        // 1. Ringkasan KPI Summary
        $totalRecords = $allRecords->count();
        $countHadir = $allRecords->whereIn('status', ['Hadir', 'Tepat Waktu'])->count();
        $countTerlambat = $allRecords->where('status', 'Terlambat')->count();
        $countIzin = $allRecords->where('status', 'Izin')->count();
        $countSakit = $allRecords->where('status', 'Sakit')->count();
        $countAlpa = $allRecords->whereIn('status', ['Alpa', 'Tanpa Keterangan'])->count();

        $persentaseKehadiran = $totalRecords > 0 ? round((($countHadir + $countTerlambat) / $totalRecords) * 100, 1) : 0;
        $persentaseTepatWaktu = ($countHadir + $countTerlambat) > 0 ? round(($countHadir / ($countHadir + $countTerlambat)) * 100, 1) : 0;

        // 2. Chart 1: Tren Harian (Line/Area Chart)
        $dailyTrends = (clone $query)
            ->select(
                'tanggal',
                DB::raw("SUM(CASE WHEN status IN ('Hadir', 'Tepat Waktu') THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as total_terlambat"),
                DB::raw("SUM(CASE WHEN status IN ('Izin', 'Sakit') THEN 1 ELSE 0 END) as total_izin_sakit"),
                DB::raw("SUM(CASE WHEN status IN ('Alpa', 'Tanpa Keterangan') THEN 1 ELSE 0 END) as total_alpa")
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        $trendLabels = [];
        $trendHadir = [];
        $trendTerlambat = [];
        $trendIzinSakit = [];
        $trendAlpa = [];

        foreach ($dailyTrends as $trend) {
            $trendLabels[] = Carbon::parse($trend->tanggal)->format('d M');
            $trendHadir[] = (int) $trend->total_hadir;
            $trendTerlambat[] = (int) $trend->total_terlambat;
            $trendIzinSakit[] = (int) $trend->total_izin_sakit;
            $trendAlpa[] = (int) $trend->total_alpa;
        }

        // 3. Chart 2: Status Distribution (Donut Chart)
        $statusDistribution = [
            'labels' => ['Tepat Waktu', 'Terlambat', 'Izin', 'Sakit', 'Alpa'],
            'series' => [$countHadir, $countTerlambat, $countIzin, $countSakit, $countAlpa]
        ];

        // 4. Chart 3: Perbandingan Kehadiran per Kelas (Bar Chart)
        $kelasStats = (clone $query)
            ->select('kelas_id',
                DB::raw("SUM(CASE WHEN status IN ('Hadir', 'Tepat Waktu') THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as total_terlambat"),
                DB::raw("SUM(CASE WHEN status IN ('Alpa', 'Tanpa Keterangan') THEN 1 ELSE 0 END) as total_alpa")
            )
            ->with('kelas:id,nama')
            ->groupBy('kelas_id')
            ->get()
            ->sortByDesc(function ($item) {
                return $item->total_hadir + $item->total_terlambat;
            })
            ->take(10); // Top 10 Kelas

        $kelasLabels = [];
        $kelasHadir = [];
        $kelasTerlambat = [];
        $kelasAlpa = [];

        foreach ($kelasStats as $ks) {
            $kelasLabels[] = $ks->kelas ? $ks->kelas->nama : 'Kelas #'.$ks->kelas_id;
            $kelasHadir[] = (int) $ks->total_hadir;
            $kelasTerlambat[] = (int) $ks->total_terlambat;
            $kelasAlpa[] = (int) $ks->total_alpa;
        }

        // 5. Chart 4: Sebaran Jam Kedatangan (Column Chart)
        $jamBins = [
            '< 06:30' => 0,
            '06:30 - 06:45' => 0,
            '06:45 - 07:00' => 0,
            '07:00 - 07:15' => 0,
            '> 07:15 (Terlambat)' => 0,
        ];

        foreach ($allRecords as $rec) {
            if (!$rec->jam_masuk) continue;
            $timeStr = Carbon::parse($rec->jam_masuk)->format('H:i');
            if ($timeStr < '06:30') {
                $jamBins['< 06:30']++;
            } elseif ($timeStr >= '06:30' && $timeStr < '06:45') {
                $jamBins['06:30 - 06:45']++;
            } elseif ($timeStr >= '06:45' && $timeStr <= '07:00') {
                $jamBins['06:45 - 07:00']++;
            } elseif ($timeStr > '07:00' && $timeStr <= '07:15') {
                $jamBins['07:00 - 07:15']++;
            } else {
                $jamBins['> 07:15 (Terlambat)']++;
            }
        }

        // Peak hour text
        $maxBinValue = count($jamBins) > 0 ? max($jamBins) : 0;
        $maxBinKey = $maxBinValue > 0 ? array_search($maxBinValue, $jamBins) : '-';
        $peakHourText = $maxBinKey;

        // 6. Chart 5: Pola Kehadiran per Hari (Radar Chart)
        $daysOfWeekMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        $radarHadir = array_fill_keys(array_values($daysOfWeekMap), 0);
        $radarTerlambat = array_fill_keys(array_values($daysOfWeekMap), 0);
        $radarAlpa = array_fill_keys(array_values($daysOfWeekMap), 0);

        foreach ($allRecords as $rec) {
            $dayNum = Carbon::parse($rec->tanggal)->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
            if (isset($daysOfWeekMap[$dayNum])) {
                $dayName = $daysOfWeekMap[$dayNum];
                if (in_array($rec->status, ['Hadir', 'Tepat Waktu'])) {
                    $radarHadir[$dayName]++;
                } elseif ($rec->status === 'Terlambat') {
                    $radarTerlambat[$dayName]++;
                } elseif (in_array($rec->status, ['Alpa', 'Tanpa Keterangan'])) {
                    $radarAlpa[$dayName]++;
                }
            }
        }

        // 7. Ranking Siswa Sering Terlambat / Alpa (Top 5)
        $topProblematicSiswa = (clone $query)
            ->whereIn('status', ['Terlambat', 'Alpa', 'Tanpa Keterangan'])
            ->select('siswa_id',
                DB::raw("SUM(CASE WHEN status = 'Terlambat' THEN 1 ELSE 0 END) as count_terlambat"),
                DB::raw("SUM(CASE WHEN status IN ('Alpa', 'Tanpa Keterangan') THEN 1 ELSE 0 END) as count_alpa"),
                DB::raw("COUNT(*) as total_pelanggaran")
            )
            ->with(['siswa:id,nama,nis,kelas_id', 'siswa.kelas:id,nama'])
            ->groupBy('siswa_id')
            ->orderByDesc('total_pelanggaran')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->siswa ? $item->siswa->nama : 'Siswa #'.$item->siswa_id,
                    'kelas' => ($item->siswa && $item->siswa->kelas) ? $item->siswa->kelas->nama : '-',
                    'terlambat' => (int) $item->count_terlambat,
                    'alpa' => (int) $item->count_alpa,
                    'total' => (int) $item->total_pelanggaran,
                ];
            });

        return response()->json([
            'success' => true,
            'kpi' => [
                'total_presensi' => number_format($totalRecords),
                'count_hadir' => number_format($countHadir),
                'count_terlambat' => number_format($countTerlambat),
                'count_izin_sakit' => number_format($countIzin + $countSakit),
                'count_alpa' => number_format($countAlpa),
                'persentase_kehadiran' => $persentaseKehadiran,
                'persentase_tepat_waktu' => $persentaseTepatWaktu,
                'peak_hour' => $peakHourText,
            ],
            'chart_trend' => [
                'labels' => $trendLabels,
                'hadir' => $trendHadir,
                'terlambat' => $trendTerlambat,
                'izin_sakit' => $trendIzinSakit,
                'alpa' => $trendAlpa,
            ],
            'chart_status' => $statusDistribution,
            'chart_kelas' => [
                'labels' => $kelasLabels,
                'hadir' => $kelasHadir,
                'terlambat' => $kelasTerlambat,
                'alpa' => $kelasAlpa,
            ],
            'chart_jam' => [
                'labels' => array_keys($jamBins),
                'series' => array_values($jamBins),
            ],
            'chart_radar' => [
                'categories' => array_values($daysOfWeekMap),
                'hadir' => array_values($radarHadir),
                'terlambat' => array_values($radarTerlambat),
                'alpa' => array_values($radarAlpa),
            ],
            'ranking_siswa' => $topProblematicSiswa,
        ]);
    }
}

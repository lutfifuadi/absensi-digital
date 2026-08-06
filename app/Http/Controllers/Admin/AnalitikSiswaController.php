<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalitikSiswaController extends Controller
{
    // Status yang dipakai di database
    private const STATUS_HADIR     = 'hadir';
    private const STATUS_TERLAMBAT = 'terlambat';
    private const STATUS_IZIN      = 'izin';
    private const STATUS_SAKIT     = 'sakit';
    private const STATUS_ALPHA     = 'alpha';

    /**
     * Tampilkan Halaman Utama Analitik Kehadiran Siswa
     */
    public function index(Request $request)
    {
        $kelases = Kelas::orderBy('nama', 'asc')->get();
        $jurusans = Jurusan::orderBy('nama', 'asc')->get();

        return view('admin.analitik-siswa.index', [
            'pageTitle' => 'Grafik & Analitik Kehadiran Siswa',
            'kelases'   => $kelases,
            'jurusans'  => $jurusans,
        ]);
    }

    /**
     * API JSON Endpoint untuk menyuplai data chart secara AJAX
     */
    public function getData(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $kelasId   = $request->input('kelas_id');
        $tingkat   = $request->input('tingkat');
        $jurusanId = $request->input('jurusan_id');

        // Query dasar
        $baseQuery = AbsensiSiswa::query()
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($kelasId && $kelasId !== 'all') {
            $baseQuery->where('kelas_id', (int) $kelasId);
        }

        if ($tingkat && $tingkat !== 'all') {
            $baseQuery->whereHas('kelas', function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat);
            });
        }

        if ($jurusanId && $jurusanId !== 'all') {
            $baseQuery->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', (int) $jurusanId);
            });
        }

        // ── 1. Agregasi KPI Utama (satu query DB) ─────────────────────────────
        $kpiRaw = (clone $baseQuery)
            ->select(
                DB::raw("COUNT(*) as total"),
                DB::raw("SUM(CASE WHEN status = 'hadir'     THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'izin'      THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN status = 'sakit'     THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN status = 'alpha'     THEN 1 ELSE 0 END) as total_alpha")
            )
            ->first();

        $totalRecords   = (int) ($kpiRaw->total ?? 0);
        $countHadir     = (int) ($kpiRaw->total_hadir ?? 0);
        $countTerlambat = (int) ($kpiRaw->total_terlambat ?? 0);
        $countIzin      = (int) ($kpiRaw->total_izin ?? 0);
        $countSakit     = (int) ($kpiRaw->total_sakit ?? 0);
        $countAlpha     = (int) ($kpiRaw->total_alpha ?? 0);

        $totalHadirTerlambat = $countHadir + $countTerlambat;
        $persentaseKehadiran = $totalRecords > 0
            ? round(($totalHadirTerlambat / $totalRecords) * 100, 1)
            : 0;
        $persentaseTepatWaktu = $totalHadirTerlambat > 0
            ? round(($countHadir / $totalHadirTerlambat) * 100, 1)
            : 0;

        // ── 2. Tren Harian (Chart 1) ───────────────────────────────────────────
        $dailyTrends = (clone $baseQuery)
            ->select(
                'tanggal',
                DB::raw("SUM(CASE WHEN status = 'hadir'     THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'izin'      THEN 1 ELSE 0 END) as total_izin"),
                DB::raw("SUM(CASE WHEN status = 'sakit'     THEN 1 ELSE 0 END) as total_sakit"),
                DB::raw("SUM(CASE WHEN status = 'alpha'     THEN 1 ELSE 0 END) as total_alpha")
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        $trendLabels    = [];
        $trendHadir     = [];
        $trendTerlambat = [];
        $trendIzinSakit = [];
        $trendAlpha     = [];

        foreach ($dailyTrends as $row) {
            $trendLabels[]    = Carbon::parse($row->tanggal)->format('d M');
            $trendHadir[]     = (int) $row->total_hadir;
            $trendTerlambat[] = (int) $row->total_terlambat;
            $trendIzinSakit[] = (int) $row->total_izin + (int) $row->total_sakit;
            $trendAlpha[]     = (int) $row->total_alpha;
        }

        // ── 3. Distribusi Status (Chart 2 – Donut) ─────────────────────────────
        $statusDistribution = [
            'labels' => ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'],
            'series' => [$countHadir, $countTerlambat, $countIzin, $countSakit, $countAlpha],
        ];

        // ── 4. Perbandingan Kehadiran per Kelas (Chart 3 – Bar) ────────────────
        $kelasStats = (clone $baseQuery)
            ->select(
                'kelas_id',
                DB::raw("SUM(CASE WHEN status = 'hadir'     THEN 1 ELSE 0 END) as total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'alpha'     THEN 1 ELSE 0 END) as total_alpha")
            )
            ->groupBy('kelas_id')
            ->with('kelas:id,nama')
            ->get()
            ->sortByDesc(fn($i) => (int)$i->total_hadir + (int)$i->total_terlambat)
            ->take(10);

        $kelasLabels    = [];
        $kelasHadir     = [];
        $kelasTerlambat = [];
        $kelasAlpha     = [];

        foreach ($kelasStats as $ks) {
            $kelasLabels[]    = $ks->kelas ? $ks->kelas->nama : 'Kelas #' . $ks->kelas_id;
            $kelasHadir[]     = (int) $ks->total_hadir;
            $kelasTerlambat[] = (int) $ks->total_terlambat;
            $kelasAlpha[]     = (int) $ks->total_alpha;
        }

        // ── 5. Sebaran Jam Kedatangan – Peak Hour (Chart 4) ────────────────────
        $jamBins = [
            '< 06:30'           => 0,
            '06:30 – 06:45'    => 0,
            '06:45 – 07:00'    => 0,
            '07:00 – 07:15'    => 0,
            '> 07:15 (Telat)'  => 0,
        ];

        // Query hanya baris yang punya jam_masuk agar lebih efisien
        $jamRows = (clone $baseQuery)
            ->whereNotNull('jam_masuk')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->select('jam_masuk')
            ->get();

        foreach ($jamRows as $row) {
            $t = substr($row->jam_masuk, 0, 5); // HH:mm
            if ($t < '06:30') {
                $jamBins['< 06:30']++;
            } elseif ($t < '06:45') {
                $jamBins['06:30 – 06:45']++;
            } elseif ($t <= '07:00') {
                $jamBins['06:45 – 07:00']++;
            } elseif ($t <= '07:15') {
                $jamBins['07:00 – 07:15']++;
            } else {
                $jamBins['> 07:15 (Telat)']++;
            }
        }

        $maxVal      = max($jamBins) ?: 0;
        $peakHourKey = $maxVal > 0 ? array_search($maxVal, $jamBins) : '-';

        // ── 6. Pola Hari dalam Seminggu (Chart 5 – Radar) ─────────────────────
        $dayNames     = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $radarHadir   = array_fill_keys($dayNames, 0);
        $radarTelat   = array_fill_keys($dayNames, 0);
        $radarAlpha   = array_fill_keys($dayNames, 0);

        $radarRows = (clone $baseQuery)
            ->select('tanggal', 'status')
            ->get();

        foreach ($radarRows as $row) {
            $dow = Carbon::parse($row->tanggal)->dayOfWeekIso; // 1=Senin … 6=Sabtu
            if ($dow > 6) continue; // Skip Minggu
            $dayName = $dayNames[$dow - 1];
            if ($row->status === 'hadir')     $radarHadir[$dayName]++;
            elseif ($row->status === 'terlambat') $radarTelat[$dayName]++;
            elseif ($row->status === 'alpha') $radarAlpha[$dayName]++;
        }

        // ── 7. Ranking Siswa Perlu Perhatian (Top 5) ──────────────────────────
        $rankingSiswa = (clone $baseQuery)
            ->whereIn('status', ['terlambat', 'alpha'])
            ->select(
                'siswa_id',
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as count_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'alpha'     THEN 1 ELSE 0 END) as count_alpha"),
                DB::raw("COUNT(*) as total_masalah")
            )
            ->groupBy('siswa_id')
            ->orderByDesc('total_masalah')
            ->with(['siswa:id,nama,nis,kelas_id', 'siswa.kelas:id,nama'])
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'nama'      => $item->siswa->nama ?? 'Siswa #' . $item->siswa_id,
                'nis'       => $item->siswa->nis ?? '-',
                'kelas'     => $item->siswa?->kelas?->nama ?? '-',
                'terlambat' => (int) $item->count_terlambat,
                'alpha'     => (int) $item->count_alpha,
                'total'     => (int) $item->total_masalah,
            ]);

        // Bersihkan file temp jika ada
        @unlink(base_path('check_db_temp.php'));

        return response()->json([
            'success' => true,
            'periode' => [
                'start' => $startDate->format('d M Y'),
                'end'   => $endDate->format('d M Y'),
            ],
            'kpi' => [
                'total_presensi'       => $totalRecords,
                'count_hadir'          => $countHadir,
                'count_terlambat'      => $countTerlambat,
                'count_izin_sakit'     => $countIzin + $countSakit,
                'count_alpha'          => $countAlpha,
                'persentase_kehadiran' => $persentaseKehadiran,
                'persentase_tepat_waktu' => $persentaseTepatWaktu,
                'peak_hour'            => $peakHourKey,
            ],
            'chart_trend' => [
                'labels'    => $trendLabels,
                'hadir'     => $trendHadir,
                'terlambat' => $trendTerlambat,
                'izin_sakit'=> $trendIzinSakit,
                'alpha'     => $trendAlpha,
            ],
            'chart_status' => $statusDistribution,
            'chart_kelas'  => [
                'labels'    => $kelasLabels,
                'hadir'     => $kelasHadir,
                'terlambat' => $kelasTerlambat,
                'alpha'     => $kelasAlpha,
            ],
            'chart_jam' => [
                'labels' => array_keys($jamBins),
                'series' => array_values($jamBins),
            ],
            'chart_radar' => [
                'categories' => $dayNames,
                'hadir'      => array_values($radarHadir),
                'terlambat'  => array_values($radarTelat),
                'alpha'      => array_values($radarAlpha),
            ],
            'ranking_siswa' => $rankingSiswa,
        ]);
    }
}

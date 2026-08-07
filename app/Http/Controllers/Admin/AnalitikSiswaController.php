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
        $user = $request->user();
        $assignedClass = null;
        $isWaliKelasLocked = false;

        if ($user && $user->isRole(\App\Models\User::ROLE_WALI_KELAS) && !$user->hasAnyRole(['super_admin', 'admin_sekolah', 'operator'])) {
            $guru = \App\Models\Guru::where('user_id', $user->id)->first();
            if ($guru) {
                $assignedClass = Kelas::where('wali_kelas_id', $guru->id)->first();
                if ($assignedClass) {
                    $isWaliKelasLocked = true;
                }
            }
        }

        // Ambil Tahun Akademik yang sedang aktif (dari session atau DB)
        $activeTaId = session('tahun_akademik_id')
            ?? session('tahun_ajaran_id')
            ?? \App\Models\TahunAkademik::where('is_aktif', true)->value('id');

        $kelasesQuery = Kelas::query();
        if ($activeTaId) {
            $kelasesQuery->where('tahun_akademik_id', $activeTaId);
        }

        $kelases = $kelasesQuery->orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        // Fallback jika tidak ada kelas untuk Tahun Akademik aktif tersebut
        if ($kelases->isEmpty() && $activeTaId) {
            $kelases = Kelas::orderBy('tingkat', 'asc')
                ->orderBy('nama', 'asc')
                ->get();
        }

        // Deteksi otomatis jurusan yang hanya digunakan oleh kelas aktif
        $usedJurusanIds = $kelases->pluck('jurusan_id')->filter()->unique()->toArray();
        $jurusans = Jurusan::whereIn('id', $usedJurusanIds)->orderBy('nama', 'asc')->get();
        if ($jurusans->isEmpty()) {
            $jurusans = Jurusan::orderBy('nama', 'asc')->get();
        }

        // Deteksi otomatis tingkat yang hanya digunakan oleh kelas aktif
        $dbTingkat = $kelases->pluck('tingkat')->filter()->unique()->toArray();
        if (empty($dbTingkat)) {
            $dbTingkat = \App\Helpers\JenjangHelper::getTingkatOptions();
        } else {
            usort($dbTingkat, function ($a, $b) {
                return strnatcasecmp($a, $b);
            });
        }
        $tingkatOptions = array_values(array_unique($dbTingkat));

        return view('admin.analitik-siswa.index', [
            'pageTitle'         => 'Grafik & Analitik Kehadiran Siswa',
            'kelases'           => $kelases,
            'jurusans'          => $jurusans,
            'tingkatOptions'    => $tingkatOptions,
            'assignedClass'     => $assignedClass,
            'isWaliKelasLocked' => $isWaliKelasLocked,
        ]);
    }

    /**
     * API JSON Endpoint untuk menyuplai data chart secara AJAX
     */
    public function getData(Request $request)
    {
        try {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->subDays(29)->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfDay();

        $kelasId   = $request->input('kelas_id');
        $tingkat   = $request->input('tingkat');
        $jurusanId = $request->input('jurusan_id');

        // Scoping khusus untuk Wali Kelas
        $user = $request->user();
        if ($user && $user->isRole(\App\Models\User::ROLE_WALI_KELAS) && !$user->hasAnyRole(['super_admin', 'admin_sekolah', 'operator'])) {
            $guru = \App\Models\Guru::where('user_id', $user->id)->first();
            if ($guru) {
                $assignedClass = Kelas::where('wali_kelas_id', $guru->id)->first();
                if ($assignedClass) {
                    $kelasId = (string) $assignedClass->id;
                    $tingkat = 'all';
                    $jurusanId = 'all';
                }
            }
        }

        // Query dasar
        $baseQuery = AbsensiSiswa::query()
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($kelasId && $kelasId !== 'all') {
            $baseQuery->where('kelas_id', (int) $kelasId);
        }

        if ($tingkat && $tingkat !== 'all') {
            $tingkatMap = [
                '10' => 'X', '11' => 'XI', '12' => 'XII',
                'X'  => '10', 'XI' => '11', 'XII' => '12',
                '7'  => 'VII', '8'  => 'VIII', '9'  => 'IX',
                'VII' => '7', 'VIII' => '8', 'IX' => '9',
                '1'  => 'I', '2'  => 'II', '3'  => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI',
                'I'  => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5', 'VI' => '6',
            ];
            $tingkatValues = array_unique([$tingkat, $tingkatMap[$tingkat] ?? $tingkat]);

            $baseQuery->whereHas('kelas', function ($q) use ($tingkatValues) {
                $q->whereIn('tingkat', $tingkatValues);
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
        // Pisahkan eager load dari aggregate query untuk menghindari konflik SELECT
        $rankingRows = (clone $baseQuery)
            ->whereIn('status', ['terlambat', 'alpha'])
            ->select(
                'siswa_id',
                DB::raw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as count_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'alpha'     THEN 1 ELSE 0 END) as count_alpha"),
                DB::raw("COUNT(*) as total_masalah")
            )
            ->groupBy('siswa_id')
            ->orderByDesc('total_masalah')
            ->limit(5)
            ->get();

        // Ambil siswa & kelas secara terpisah
        $siswaIds    = $rankingRows->pluck('siswa_id')->unique()->all();
        $siswaLookup = \App\Models\Siswa::with('kelas:id,nama')
            ->whereIn('id', $siswaIds)
            ->get(['id', 'nama_lengkap', 'nis', 'kelas_id'])
            ->keyBy('id');

        $rankingSiswa = $rankingRows->map(function ($item) use ($siswaLookup) {
            $s = $siswaLookup->get($item->siswa_id);
            return [
                'nama'      => $s ? $s->nama_lengkap : 'Siswa #' . $item->siswa_id,
                'nis'       => $s ? ($s->nis ?? '-') : '-',
                'kelas'     => $s?->kelas?->nama ?? '-',
                'terlambat' => (int) $item->count_terlambat,
                'alpha'     => (int) $item->count_alpha,
                'total'     => (int) $item->total_masalah,
            ];
        });

        // ── 8. Siswa Belum Absensi (Hari Ini) ─────────────────────────────────
        $todayStr = Carbon::now()->toDateString();
        $siswaQuery = \App\Models\Siswa::query()->where('status', 'aktif');

        if ($kelasId && $kelasId !== 'all') {
            $siswaQuery->where('kelas_id', (int) $kelasId);
        }
        if ($tingkat && $tingkat !== 'all') {
            $tingkatMap = [
                '10' => 'X', '11' => 'XI', '12' => 'XII',
                'X'  => '10', 'XI' => '11', 'XII' => '12',
                '7'  => 'VII', '8'  => 'VIII', '9'  => 'IX',
                'VII' => '7', 'VIII' => '8', 'IX' => '9',
                '1'  => 'I', '2'  => 'II', '3'  => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI',
                'I'  => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5', 'VI' => '6',
            ];
            $tingkatValues = array_unique([$tingkat, $tingkatMap[$tingkat] ?? $tingkat]);
            $siswaQuery->whereHas('kelas', function ($q) use ($tingkatValues) {
                $q->whereIn('tingkat', $tingkatValues);
            });
        }
        if ($jurusanId && $jurusanId !== 'all') {
            $siswaQuery->whereHas('kelas', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', (int) $jurusanId);
            });
        }

        $totalSiswaScope = (clone $siswaQuery)->count();

        $belumAbsenQuery = (clone $siswaQuery)
            ->whereDoesntHave('absensi', function ($q) use ($todayStr) {
                $q->whereDate('tanggal', $todayStr);
            })
            ->with(['kelas:id,nama', 'ortuUser:id,no_hp']);

        $countBelumAbsenToday = (clone $belumAbsenQuery)->count();

        $listBelumAbsen = $belumAbsenQuery->limit(10)->get()->map(function ($s) {
            $noHp = $s->no_hp_ortu ?: ($s->ortuUser?->no_hp ?: $s->no_hp);
            $formattedPhone = '';
            if (!empty($noHp)) {
                $clean = preg_replace('/[^0-9]/', '', $noHp);
                if (str_starts_with($clean, '08')) {
                    $formattedPhone = '62' . substr($clean, 1);
                } elseif (str_starts_with($clean, '62')) {
                    $formattedPhone = $clean;
                } else {
                    $formattedPhone = $clean;
                }
            }
            $pesan = "Halo Bapak/Ibu, menginfokan bahwa putra/putri Anda {$s->nama_lengkap} belum melakukan absensi masuk sekolah hari ini. Terima kasih.";
            $waUrl = !empty($formattedPhone)
                ? 'https://wa.me/' . $formattedPhone . '?text=' . rawurlencode($pesan)
                : null;

            return [
                'id'           => $s->id,
                'nama'         => $s->nama_lengkap,
                'nis'          => $s->nis ?? '-',
                'kelas'        => $s->kelas?->nama ?? '-',
                'no_hp'        => $noHp ?: '-',
                'wa_url'       => $waUrl,
            ];
        });

        return response()->json([
            'success' => true,
            'periode' => [
                'start' => $startDate->format('d M Y'),
                'end'   => $endDate->format('d M Y'),
            ],
            'kpi' => [
                'total_presensi'         => $totalRecords,
                'count_hadir'            => $countHadir,
                'count_terlambat'        => $countTerlambat,
                'count_izin_sakit'       => $countIzin + $countSakit,
                'count_alpha'            => $countAlpha,
                'count_belum_absen'      => $countBelumAbsenToday,
                'total_siswa_scope'      => $totalSiswaScope,
                'persentase_kehadiran'   => $persentaseKehadiran,
                'persentase_tepat_waktu' => $persentaseTepatWaktu,
                'peak_hour'              => $peakHourKey,
            ],
            'list_belum_absen' => $listBelumAbsen,
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

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }
}

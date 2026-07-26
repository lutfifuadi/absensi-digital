<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Holiday;
use App\Models\KelasJadwalAbsensi;
use App\Helpers\JadwalAbsensiHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardAlfaController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $start7Days = Carbon::now()->subDays(6)->startOfDay();

        // Filter
        $filterKelas = $request->input('kelas_id');
        $filterTanggalMulai = $request->input('start_date', $start7Days->format('Y-m-d'));
        $filterTanggalAkhir = $request->input('end_date', $today->format('Y-m-d'));
        $filterDate = Carbon::parse($filterTanggalAkhir);

        // Ambil daftar tanggal libur nasional dari DB
        $holidaysDates = Holiday::pluck('tanggal')->map(function ($d) {
            return Carbon::parse($d)->format('Y-m-d');
        })->toArray();

        // Helper fungsi penentu hari libur (Weekend / Tanggal Merah)
        $isLiburFn = function (Carbon $date) use (&$holidaysDates) {
            if ($date->isSaturday() || $date->isSunday()) {
                return true;
            }
            return in_array($date->format('Y-m-d'), $holidaysDates);
        };

        // Helper fungsi penentu status libur per kelas berdasarkan jadwal absensi kelas & fallback
        $checkIsLiburKelas = function ($kelasId, Carbon $date) use (&$holidaysDates) {
            $mappingHari = [
                'monday'    => 'senin',
                'tuesday'   => 'selasa',
                'wednesday' => 'rabu',
                'thursday'  => 'kamis',
                'friday'    => 'jumat',
                'saturday'  => 'sabtu',
                'sunday'    => 'minggu',
            ];
            $hariIndo = $mappingHari[strtolower($date->format('l'))] ?? 'senin';

            $hasCustom = KelasJadwalAbsensi::where('kelas_id', $kelasId)
                ->where('hari', $hariIndo)
                ->exists();

            if ($hasCustom) {
                $jadwal = JadwalAbsensiHelper::getJadwalForKelas($kelasId, $hariIndo);
                return (bool) ($jadwal['is_libur'] ?? false);
            }

            if ($date->isSaturday() || $date->isSunday()) {
                return true;
            }
            return in_array($date->format('Y-m-d'), $holidaysDates);
        };

        if ($filterKelas) {
            $isHoliday = $checkIsLiburKelas($filterKelas, $filterDate);
            if ($isHoliday) {
                $kelasObjForFilter = Kelas::find($filterKelas);
                $tingkat = $kelasObjForFilter?->tingkat;
                $holidayObj = Holiday::whereDate('tanggal', $filterDate->format('Y-m-d'))
                    ->where(function ($query) use ($filterKelas, $tingkat) {
                        $query->where(function ($q) {
                            $q->whereNull('tingkat')->whereNull('kelas_id');
                        });
                        if ($tingkat) {
                            $query->orWhere(function ($q) use ($tingkat) {
                                $q->where('tingkat', $tingkat)->whereNull('kelas_id');
                            });
                        }
                        $query->orWhere('kelas_id', $filterKelas);
                    })
                    ->first();

                if ($holidayObj) {
                    $holidayName = $holidayObj->nama;
                } elseif ($filterDate->isWeekend()) {
                    $holidayName = "Akhir Pekan (Jadwal Kelas)";
                } else {
                    $holidayName = "Hari Libur (Jadwal Kelas)";
                }
            } else {
                $holidayName = null;
            }
        } else {
            $activeClasses = Kelas::where('is_aktif_absensi', true)->get();
            $allActiveClassesLibur = true;
            if ($activeClasses->isEmpty()) {
                $allActiveClassesLibur = false;
            } else {
                foreach ($activeClasses as $c) {
                    if (!$checkIsLiburKelas($c->id, $filterDate)) {
                        $allActiveClassesLibur = false;
                        break;
                    }
                }
            }

            if ($allActiveClassesLibur) {
                $isHoliday = true;
                $holidayObj = Holiday::whereDate('tanggal', $filterDate->format('Y-m-d'))->first();
                $holidayName = $holidayObj ? $holidayObj->nama : ($filterDate->isWeekend() ? 'Akhir Pekan (' . $filterDate->translatedFormat('l') . ')' : 'Hari Libur');
            } else {
                $isHoliday = false;
                $holidayName = null;
            }
        }

        // Cari Hari Kerja Pembanding (Previous Working Day - skip weekend & libur)
        $prevWorkingDate = $filterDate->copy()->subDay();
        while ($isLiburFn($prevWorkingDate)) {
            $prevWorkingDate->subDay();
        }

        $currentDateStr = $filterDate->format('Y-m-d');
        $prevDateStr = $prevWorkingDate->format('Y-m-d');

        $kelasQuery = Kelas::query();
        if ($filterKelas) {
            $kelasQuery->where('id', $filterKelas);
        }
        $kelasList = $kelasQuery->orderBy('nama')->get();

        // ══════════════════════════════════════════════════════════
        // 1. Total Siswa Aktif
        // ══════════════════════════════════════════════════════════
        $querySiswaAktif = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $querySiswaAktif->where('kelas_id', $filterKelas);
        }
        $totalSiswaAktif = $querySiswaAktif->count();

        // ══════════════════════════════════════════════════════════
        // 2. Siswa yang BELUM Absen (Tanggal Terpilih vs Tanggal Pembanding)
        // ══════════════════════════════════════════════════════════
        $siswaAktifHariIni = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $siswaAktifHariIni->where('kelas_id', $filterKelas);
        }
        $idsAktifHariIni = $siswaAktifHariIni->pluck('id');

        $idsSudahAbsenHariIni = AbsensiSiswa::where('tanggal', $currentDateStr)
            ->whereIn('siswa_id', $idsAktifHariIni)
            ->pluck('siswa_id')
            ->unique()
            ->toArray();
        $idsSudahAbsenHariIniMap = array_flip($idsSudahAbsenHariIni);

        $idsSudahAbsenKemarin = AbsensiSiswa::where('tanggal', $prevDateStr)
            ->whereIn('siswa_id', $idsAktifHariIni)
            ->pluck('siswa_id')
            ->unique()
            ->toArray();
        $idsSudahAbsenKemarinMap = array_flip($idsSudahAbsenKemarin);

        $totalBelumAbsenHariIni = 0;
        $totalBelumAbsenKemarin = 0;

        $siswaAktifGrouped = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $siswaAktifGrouped->where('kelas_id', $filterKelas);
        }
        $siswaAktifGrouped = $siswaAktifGrouped->get()->groupBy('kelas_id');

        foreach ($siswaAktifGrouped as $kelasId => $siswaList) {
            $isLiburHariIni = $checkIsLiburKelas($kelasId, $filterDate);
            $isLiburKemarin = $checkIsLiburKelas($kelasId, $prevWorkingDate);

            if (!$isLiburHariIni) {
                foreach ($siswaList as $siswa) {
                    if (!isset($idsSudahAbsenHariIniMap[$siswa->id])) {
                        $totalBelumAbsenHariIni++;
                    }
                }
            }

            if (!$isLiburKemarin) {
                foreach ($siswaList as $siswa) {
                    if (!isset($idsSudahAbsenKemarinMap[$siswa->id])) {
                        $totalBelumAbsenKemarin++;
                    }
                }
            }
        }

        $deltaBelumAbsen = $totalBelumAbsenHariIni - $totalBelumAbsenKemarin;

        // ══════════════════════════════════════════════════════════
        // 3. Bar Chart: Belum Absen per Kelas (Optimized Batch Query & Dual Comparison)
        // ══════════════════════════════════════════════════════════
        // Batch Query 1: Total Siswa Aktif per Kelas
        $querySiswaPerKelas = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $querySiswaPerKelas->where('kelas_id', $filterKelas);
        }
        $siswaPerKelas = $querySiswaPerKelas
            ->select('kelas_id', DB::raw('COUNT(*) as total_siswa'))
            ->groupBy('kelas_id')
            ->pluck('total_siswa', 'kelas_id');

        // Batch Query 2: Total Sudah Absen per Kelas (Tanggal Terpilih)
        $sudahAbsenCurrent = collect();
        $querySudahCurrent = AbsensiSiswa::where('tanggal', $currentDateStr)
            ->join('siswa', 'siswa.id', '=', 'absensi_siswa.siswa_id')
            ->where('siswa.status', 'aktif');
        if ($filterKelas) {
            $querySudahCurrent->where('siswa.kelas_id', $filterKelas);
        }
        $sudahAbsenCurrent = $querySudahCurrent
            ->select('siswa.kelas_id', DB::raw('COUNT(DISTINCT absensi_siswa.siswa_id) as total_sudah'))
            ->groupBy('siswa.kelas_id')
            ->pluck('total_sudah', 'siswa.kelas_id');

        // Batch Query 3: Total Sudah Absen per Kelas (Tanggal Pembanding)
        $querySudahPrev = AbsensiSiswa::where('tanggal', $prevDateStr)
            ->join('siswa', 'siswa.id', '=', 'absensi_siswa.siswa_id')
            ->where('siswa.status', 'aktif');
        if ($filterKelas) {
            $querySudahPrev->where('siswa.kelas_id', $filterKelas);
        }
        $sudahAbsenPrev = $querySudahPrev
            ->select('siswa.kelas_id', DB::raw('COUNT(DISTINCT absensi_siswa.siswa_id) as total_sudah'))
            ->groupBy('siswa.kelas_id')
            ->pluck('total_sudah', 'siswa.kelas_id');

        $kelasStats = collect();
        foreach ($kelasList as $kelasItem) {
            $totalSiswa = $siswaPerKelas[$kelasItem->id] ?? 0;
            if ($totalSiswa == 0) continue;

            $isLiburHariIni = $checkIsLiburKelas($kelasItem->id, $filterDate);
            $isLiburKemarin = $checkIsLiburKelas($kelasItem->id, $prevWorkingDate);

            $sudahC = $isLiburHariIni ? $totalSiswa : ($sudahAbsenCurrent[$kelasItem->id] ?? 0);
            $sudahP = $isLiburKemarin ? $totalSiswa : ($sudahAbsenPrev[$kelasItem->id] ?? 0);

            $belumC = max(0, $totalSiswa - $sudahC);
            $belumP = max(0, $totalSiswa - $sudahP);

            if ($belumC > 0 || $belumP > 0) {
                $kelasStats->push([
                    'id' => $kelasItem->id,
                    'nama' => $kelasItem->nama,
                    'tingkat' => $kelasItem->tingkat ?? '',
                    'belum_current' => $belumC,
                    'belum_prev' => $belumP,
                ]);
            }
        }

        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();

        // Helper fungsi sorting bertingkat (Primary: belum_current DESC, Secondary: belum_prev DESC, Tertiary: nama ASC)
        $sortStatsFn = function ($collection) {
            return $collection->sort(function ($a, $b) {
                if ($a['belum_current'] !== $b['belum_current']) {
                    return $b['belum_current'] <=> $a['belum_current'];
                }
                if ($a['belum_prev'] !== $b['belum_prev']) {
                    return $b['belum_prev'] <=> $a['belum_prev'];
                }
                return strcmp($a['nama'], $b['nama']);
            })->take(10)->values();
        };

        // Data Per Tingkatan
        $barChartTingkatData = [];

        // 1. Tab 'semua'
        $topKelasSemua = $sortStatsFn($kelasStats);
        $barChartTingkatData['semua'] = [
            'labels' => $topKelasSemua->pluck('nama')->toArray(),
            'current' => $topKelasSemua->pluck('belum_current')->toArray(),
            'prev' => $topKelasSemua->pluck('belum_prev')->toArray(),
        ];

        // 2. Tab per Tingkat (X, XI, XII / VII, VIII, IX / I-VI)
        foreach ($tingkatOptions as $tkt) {
            $filteredTkt = $kelasStats->filter(function ($item) use ($tkt) {
                return strcasecmp(trim($item['tingkat']), trim($tkt)) === 0;
            });
            $topKelasTkt = $sortStatsFn($filteredTkt);

            $barChartTingkatData[$tkt] = [
                'labels' => $topKelasTkt->pluck('nama')->toArray(),
                'current' => $topKelasTkt->pluck('belum_current')->toArray(),
                'prev' => $topKelasTkt->pluck('belum_prev')->toArray(),
            ];
        }

        $barChartLabels = $barChartTingkatData['semua']['labels'];
        $barChartDataCurrent = $barChartTingkatData['semua']['current'];
        $barChartDataPrev = $barChartTingkatData['semua']['prev'];

        // ══════════════════════════════════════════════════════════
        // 4. Line Chart: Tren Belum Absen per Hari (7 HARI KERJA TERAKHIR, skip weekend & libur)
        // ══════════════════════════════════════════════════════════
        $trendDates = [];
        $trendData = [];

        $hariKerja = collect();
        $tmp = $today->copy();
        while ($hariKerja->count() < 7) {
            if (!$isLiburFn($tmp)) {
                $hariKerja->prepend($tmp->copy());
            }
            $tmp->subDay();
        }

        $idsAktifAll = Siswa::where('status', 'aktif');
        if ($filterKelas) {
            $idsAktifAll->where('kelas_id', $filterKelas);
        }
        $idsAktifAllList = $idsAktifAll->pluck('id');

        foreach ($hariKerja as $date) {
            $dateStr = $date->format('Y-m-d');
            $sudahAbsen = AbsensiSiswa::where('tanggal', $dateStr)
                ->whereIn('siswa_id', $idsAktifAllList)
                ->count();

            $trendDates[] = $date->format('d M');
            $trendData[] = max(0, $idsAktifAllList->count() - $sudahAbsen);
        }

        // ══════════════════════════════════════════════════════════
        // 5. Detail Tabel: Daftar Siswa yang Belum Absen
        // ══════════════════════════════════════════════════════════
        $queryDetail = Siswa::with(['kelas.waliKelas'])
            ->where('status', 'aktif');

        if ($filterKelas) {
            $queryDetail->where('kelas_id', $filterKelas);
        }

        // Cari daftar kelas yang TIDAK libur pada filterTanggalAkhir
        $nonHolidayKelasIds = [];
        foreach ($kelasList as $kelasItem) {
            if (!$checkIsLiburKelas($kelasItem->id, $filterDate)) {
                $nonHolidayKelasIds[] = $kelasItem->id;
            }
        }

        if (empty($nonHolidayKelasIds)) {
            $queryDetail->whereRaw('1 = 0');
        } else {
            $queryDetail->whereIn('kelas_id', $nonHolidayKelasIds);

            $siswaSudahAbsen = AbsensiSiswa::where('tanggal', $filterTanggalAkhir)
                ->pluck('siswa_id')
                ->unique();

            $queryDetail->whereNotIn('id', $siswaSudahAbsen);
        }

        $detailBelumAbsen = $queryDetail->orderBy('nama_lengkap')->paginate(10);
        $kelasIdMap = $kelasList->pluck('id', 'nama')->toArray();

        $data = [
            'isWeekend' => $isHoliday, // Kompatibilitas flag
            'isHoliday' => $isHoliday,
            'holidayName' => $holidayName,
            'totalSiswaAktif' => $totalSiswaAktif,
            'totalBelumAbsenHariIni' => $totalBelumAbsenHariIni,
            'totalBelumAbsenKemarin' => $totalBelumAbsenKemarin,
            'deltaBelumAbsen' => $deltaBelumAbsen,
            'prevDateLabel' => $prevWorkingDate->translatedFormat('d M'),
            'currentDateLabel' => $filterDate->translatedFormat('d M'),
            'barChartLabels' => $barChartLabels,
            'barChartDataCurrent' => $barChartDataCurrent,
            'barChartDataPrev' => $barChartDataPrev,
            'barChartTingkatData' => $barChartTingkatData,
            'tingkatOptions' => $tingkatOptions,
            'lineChartLabels' => $trendDates,
            'lineChartData' => $trendData,
            'detailBelumAbsen' => $detailBelumAbsen,
            'kelasList' => $kelasList,
            'kelasIdMap' => $kelasIdMap,
            // Filter states
            'filterKelas' => $filterKelas,
            'filterTanggalMulai' => $filterTanggalMulai,
            'filterTanggalAkhir' => $filterTanggalAkhir,
        ];

        if ($request->ajax()) {
            return view('admin.dashboard.alfa-table', $data);
        }

        return view('admin.dashboard.alfa', $data);
    }

    // ══════════════════════════════════════════════════════════════
    // AJAX: Grafik Tren Historis Belum Absen
    // GET /admin/dashboard/belum-absen/chart-data
    // Params: period (weekly|monthly|semester|yearly), kelas_id?, tingkat?
    // ══════════════════════════════════════════════════════════════
    public function chartData(Request $request)
    {
        $period    = $request->input('period', 'weekly');
        $kelasId   = $request->input('kelas_id');
        $tingkat   = $request->input('tingkat');

        $today = Carbon::today();

        // Ambil semua tanggal libur nasional
        $holidaysDates = Holiday::pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        $isLiburFn = function (Carbon $date) use (&$holidaysDates) {
            if ($date->isSaturday() || $date->isSunday()) return true;
            return in_array($date->format('Y-m-d'), $holidaysDates);
        };

        // Ambil daftar siswa aktif sesuai filter
        $siswaQuery = Siswa::where('status', 'aktif');
        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        } elseif ($tingkat) {
            $siswaQuery->whereHas('kelas', fn($q) => $q->where('tingkat', $tingkat));
        }
        $siswaIds = $siswaQuery->pluck('id');
        $totalSiswa = $siswaIds->count();

        if ($totalSiswa === 0) {
            return response()->json(['labels' => [], 'data' => []]);
        }

        // ── Helper: hitung belum absen untuk satu tanggal ──
        $countBelumOnDate = function (string $dateStr) use ($siswaIds) {
            $sudah = AbsensiSiswa::where('tanggal', $dateStr)
                ->whereIn('siswa_id', $siswaIds)
                ->distinct('siswa_id')
                ->count('siswa_id');
            return max(0, $siswaIds->count() - $sudah);
        };

        // ── Helper: hitung rata-rata belum absen untuk range tanggal ──
        $countBelumInRange = function (Carbon $from, Carbon $to) use ($siswaIds, $isLiburFn, $countBelumOnDate) {
            $total = 0;
            $days  = 0;
            $cur   = $from->copy();
            while ($cur->lte($to)) {
                if (!$isLiburFn($cur)) {
                    $total += $countBelumOnDate($cur->format('Y-m-d'));
                    $days++;
                }
                $cur->addDay();
            }
            return $days > 0 ? round($total / $days) : 0;
        };

        $labels = [];
        $data   = [];

        switch ($period) {
            // ── MINGGUAN: 7 hari kerja terakhir ──
            case 'weekly':
                $hariKerja = collect();
                $tmp = $today->copy();
                while ($hariKerja->count() < 7) {
                    if (!$isLiburFn($tmp)) {
                        $hariKerja->prepend($tmp->copy());
                    }
                    $tmp->subDay();
                }
                foreach ($hariKerja as $date) {
                    $labels[] = $date->translatedFormat('D, d M');
                    $data[]   = $countBelumOnDate($date->format('Y-m-d'));
                }
                break;

            // ── BULANAN: 4 minggu terakhir (rata-rata per minggu) ──
            case 'monthly':
                for ($w = 3; $w >= 0; $w--) {
                    $weekStart = $today->copy()->startOfWeek()->subWeeks($w);
                    $weekEnd   = $weekStart->copy()->endOfWeek();
                    if ($weekEnd->gt($today)) $weekEnd = $today->copy();
                    $labels[] = $weekStart->translatedFormat('d M') . ' – ' . $weekEnd->translatedFormat('d M');
                    $data[]   = $countBelumInRange($weekStart, $weekEnd);
                }
                break;

            // ── SEMESTER: 6 bulan terakhir (rata-rata per bulan) ──
            case 'semester':
                for ($m = 5; $m >= 0; $m--) {
                    $monthStart = $today->copy()->subMonths($m)->startOfMonth();
                    $monthEnd   = $monthStart->copy()->endOfMonth();
                    if ($monthEnd->gt($today)) $monthEnd = $today->copy();
                    $labels[] = $monthStart->translatedFormat('M Y');
                    $data[]   = $countBelumInRange($monthStart, $monthEnd);
                }
                break;

            // ── TAHUNAN: 12 bulan terakhir (rata-rata per bulan) ──
            case 'yearly':
            default:
                for ($m = 11; $m >= 0; $m--) {
                    $monthStart = $today->copy()->subMonths($m)->startOfMonth();
                    $monthEnd   = $monthStart->copy()->endOfMonth();
                    if ($monthEnd->gt($today)) $monthEnd = $today->copy();
                    $labels[] = $monthStart->translatedFormat('M Y');
                    $data[]   = $countBelumInRange($monthStart, $monthEnd);
                }
                break;
        }

        return response()->json(compact('labels', 'data'));
    }
}

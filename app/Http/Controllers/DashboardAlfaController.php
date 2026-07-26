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

        $isHoliday = $isLiburFn($filterDate);
        $holidayObj = Holiday::whereDate('tanggal', $filterDate->format('Y-m-d'))->first();
        $holidayName = $isHoliday
            ? ($holidayObj ? $holidayObj->nama : ($filterDate->isWeekend() ? 'Akhir Pekan (' . $filterDate->translatedFormat('l') . ')' : 'Hari Libur'))
            : null;

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
        $queryDetail = Siswa::with(['kelas'])
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
}

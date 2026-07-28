<?php

namespace App\Services;

use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BKDashboardService
{
    /**
     * Get dashboard overview data for Guru BK.
     */
    public function getDashboardData(?int $tahunAkademikId = null): array
    {
        $ta = $tahunAkademikId 
            ? TahunAkademik::find($tahunAkademikId) 
            : (TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first());
        
        $taId = $ta ? $ta->id : null;

        // 1. Top Violators (Top 10 siswa bermasalah)
        $topViolators = Siswa::with(['kelas', 'pelanggaran' => function ($q) use ($taId) {
                if ($taId) {
                    $q->where('tahun_akademik_id', $taId);
                }
            }])
            ->whereHas('pelanggaran', function ($q) use ($taId) {
                if ($taId) {
                    $q->where('tahun_akademik_id', $taId);
                }
            })
            ->select('siswa.*')
            ->selectSub(function ($query) use ($taId) {
                $query->from('pelanggaran_siswa')
                    ->selectRaw('COALESCE(SUM(poin_saat_itu), 0)')
                    ->whereColumn('pelanggaran_siswa.siswa_id', 'siswa.id');
                if ($taId) {
                    $query->where('pelanggaran_siswa.tahun_akademik_id', $taId);
                }
            }, 'total_poin')
            ->selectSub(function ($query) use ($taId) {
                $query->from('pelanggaran_siswa')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('pelanggaran_siswa.siswa_id', 'siswa.id');
                if ($taId) {
                    $query->where('pelanggaran_siswa.tahun_akademik_id', $taId);
                }
            }, 'total_pelanggaran')
            ->orderByDesc('total_poin')
            ->limit(10)
            ->get();

        // 2. Rekap per Kategori Pelanggaran (Bulan Ini)
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        $rekapKategori = DB::table('pelanggaran_kategori as pk')
            ->leftJoin('pelanggaran_jenis as pj', 'pk.id', '=', 'pj.kategori_id')
            ->leftJoin('pelanggaran_siswa as ps', function ($join) use ($bulanIni, $tahunIni, $taId) {
                $join->on('pj.id', '=', 'ps.jenis_id')
                    ->whereMonth('ps.tanggal_kejadian', $bulanIni)
                    ->whereYear('ps.tanggal_kejadian', $tahunIni);
                if ($taId) {
                    $join->where('ps.tahun_akademik_id', $taId);
                }
            })
            ->select('pk.id', 'pk.nama as nama_kategori', DB::raw('COUNT(ps.id) as total_pelanggaran'), DB::raw('COALESCE(SUM(ps.poin_saat_itu), 0) as total_poin'))
            ->groupBy('pk.id', 'pk.nama')
            ->get();

        // 3. SP Aktif
        $spAktif = PelanggaranSp::with(['siswa.kelas', 'penerbit'])
            ->when($taId, function ($q) use ($taId) {
                $q->where('tahun_akademik_id', $taId);
            })
            ->latest()
            ->limit(10)
            ->get();

        // 4. Chart Tren Pelanggaran (6 Bulan Terakhir)
        $chartMonths = [];
        $chartData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('F Y');
            $chartMonths[] = $monthName;

            $count = PelanggaranSiswa::whereMonth('tanggal_kejadian', $date->month)
                ->whereYear('tanggal_kejadian', $date->year)
                ->when($taId, function ($q) use ($taId) {
                    $q->where('tahun_akademik_id', $taId);
                })
                ->count();

            $chartData[] = $count;
        }

        // Summary Counts
        $totalPelanggaranBulanIni = PelanggaranSiswa::whereMonth('tanggal_kejadian', $bulanIni)
            ->whereYear('tanggal_kejadian', $tahunIni)
            ->when($taId, function ($q) use ($taId) {
                $q->where('tahun_akademik_id', $taId);
            })
            ->count();

        $totalSiswaBermasalah = Siswa::whereHas('pelanggaran', function ($q) use ($taId) {
            if ($taId) {
                $q->where('tahun_akademik_id', $taId);
            }
        })->count();

        $totalSpDiterbitkan = PelanggaranSp::when($taId, function ($q) use ($taId) {
            $q->where('tahun_akademik_id', $taId);
        })->count();

        return [
            'tahunAkademik' => $ta,
            'topViolators' => $topViolators,
            'rekapKategori' => $rekapKategori,
            'spAktif' => $spAktif,
            'chartMonths' => $chartMonths,
            'chartData' => $chartData,
            'summary' => [
                'totalPelanggaranBulanIni' => $totalPelanggaranBulanIni,
                'totalSiswaBermasalah' => $totalSiswaBermasalah,
                'totalSpDiterbitkan' => $totalSpDiterbitkan,
            ],
        ];
    }
}

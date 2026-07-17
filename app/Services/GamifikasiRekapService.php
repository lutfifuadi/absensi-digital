<?php

namespace App\Services;

use App\Models\AbsensiSiswa;
use App\Models\Badge;
use App\Models\ClassLeaderboard;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StudentBadge;
use App\Models\StudentLeaderboard;
use App\Models\TahunAkademik;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamifikasiRekapService
{
    /**
     * Rekap per-siswa dengan detail kehadiran, skor, rank, dan badge.
     *
     * Filter yang didukung:
     *   - kelas_id        : int|string  → filter berdasarkan kelas
     *   - bulan           : string      → "YYYY-MM", hitung ulang dari absensi_siswa
     *   - tahun_akademik_id : int|string → filter tahun akademik
     *
     * @param  array $filters
     * @return Collection
     */
    /**
     * Get date range based on filters.
     *
     * @param  array $filters
     * @return array
     */
    private function getDateRange(array $filters): array
    {
        $periode = $filters['periode'] ?? null;
        $bulan = $filters['bulan'] ?? null;
        $tahunAkademikId = $filters['tahun_akademik_id'] ?? null;

        $startDate = null;
        $endDate = null;

        switch ($periode) {
            case 'minggu':
                $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY);
                break;

            case 'bulan':
                if ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
                    $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
                } else {
                    $carbonBulan = Carbon::now();
                }
                $startDate = $carbonBulan->copy()->startOfMonth();
                $endDate = $carbonBulan->copy()->endOfMonth();
                break;

            case 'semester':
                $ta = null;
                if ($tahunAkademikId) {
                    $ta = TahunAkademik::find($tahunAkademikId);
                }
                if (!$ta) {
                    $ta = TahunAkademik::where('is_aktif', true)->first()
                        ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
                }

                if ($ta) {
                    $startDate = Carbon::parse($ta->tanggal_mulai)->startOfDay();
                    $endDate = Carbon::parse($ta->tanggal_selesai)->endOfDay();
                }
                break;

            case 'tahun':
                $ta = null;
                if ($tahunAkademikId) {
                    $ta = TahunAkademik::find($tahunAkademikId);
                }
                if (!$ta) {
                    $ta = TahunAkademik::where('is_aktif', true)->first()
                        ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
                }

                if ($ta) {
                    $taName = $ta->nama;
                    $allSemesters = TahunAkademik::where('nama', $taName)->get();
                    $minStart = $allSemesters->min('tanggal_mulai');
                    $maxEnd = $allSemesters->max('tanggal_selesai');

                    if ($minStart) {
                        $startDate = Carbon::parse($minStart)->startOfDay();
                    }
                    if ($maxEnd) {
                        $endDate = Carbon::parse($maxEnd)->endOfDay();
                    }
                }
                break;

            case 'semua':
            default:
                break;
        }

        return [$startDate, $endDate];
    }

    /**
     * Rekap per-siswa dengan detail kehadiran, skor, rank, dan badge.
     *
     * Filter yang didukung:
     *   - kelas_id        : int|string  → filter berdasarkan kelas
     *   - periode         : string      → "minggu", "bulan", "semester", "tahun", "semua"
     *   - bulan           : string      → "YYYY-MM", hitung ulang dari absensi_siswa
     *   - tahun_akademik_id : int|string → filter tahun akademik
     *
     * @param  array $filters
     * @return Collection
     */
    public function getRekapSiswa(array $filters = []): Collection
    {
        $kelasId          = $filters['kelas_id'] ?? null;
        $tahunAkademikId  = $filters['tahun_akademik_id'] ?? null;

        // ── Base query siswa ─────────────────────────────────────────────────
        $siswaQuery = Siswa::query()
            ->with([
                'kelas:id,nama,jurusan_id',
                'kelas.jurusan:id,nama',
                'studentLeaderboard' => function ($q) use ($tahunAkademikId) {
                    if ($tahunAkademikId) {
                        $q->where('tahun_akademik_id', $tahunAkademikId);
                    }
                    $q->orderByDesc('calculated_at')->limit(1);
                },
                'studentBadges.badge:id,name,icon',
            ])
            ->where('status', 'aktif');

        if ($kelasId) {
            $siswaQuery->where('kelas_id', $kelasId);
        }

        if ($tahunAkademikId) {
            $siswaQuery->where('tahun_akademik_id', $tahunAkademikId);
        }

        $siswaList = $siswaQuery->select('id', 'nama_lengkap', 'nis', 'kelas_id', 'tahun_akademik_id')
            ->get();

        if ($siswaList->isEmpty()) {
            return collect();
        }

        $siswaIds = $siswaList->pluck('id');

        // ── Hitung absensi ───────────────────────────────────────────────────
        [$startDate, $endDate] = $this->getDateRange($filters);

        $absensiQuery = AbsensiSiswa::whereIn('siswa_id', $siswaIds)
            ->select(
                'siswa_id',
                DB::raw("SUM(CASE WHEN status = 'Hadir' OR status = 'hadir' THEN 1 ELSE 0 END) AS total_hadir"),
                DB::raw("SUM(CASE WHEN status = 'Terlambat' OR status = 'terlambat' THEN 1 ELSE 0 END) AS total_terlambat"),
                DB::raw("SUM(CASE WHEN status = 'Sakit' OR status = 'sakit' THEN 1 ELSE 0 END) AS total_sakit"),
                DB::raw("SUM(CASE WHEN status = 'Izin' OR status = 'izin' THEN 1 ELSE 0 END) AS total_izin"),
                DB::raw("SUM(CASE WHEN status = 'Alpha' OR status = 'alpha' THEN 1 ELSE 0 END) AS total_alpha"),
                DB::raw('COUNT(*) AS total_absensi')
            );

        if ($startDate && $endDate) {
            $absensiQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $absensiStats = $absensiQuery->groupBy('siswa_id')->get()->keyBy('siswa_id');

        // ── Bangun hasil ─────────────────────────────────────────────────────
        return $siswaList->map(function (Siswa $siswa) use ($absensiStats, $startDate, $endDate) {
            $stats = $absensiStats->get($siswa->id);

            // Ambil data leaderboard terbaru untuk siswa ini
            $leaderboard = $siswa->studentLeaderboard->first();

            // Kumpulkan badge
            $badgeList = $siswa->studentBadges
                ->map(fn ($sb) => [
                    'id'        => $sb->badge_id,
                    'name'      => $sb->badge?->name ?? '-',
                    'icon'      => $sb->badge?->icon ?? '',
                    'earned_at' => $sb->earned_at?->format('Y-m-d'),
                ]);

            return [
                'siswa_id'        => $siswa->id,
                'nama_lengkap'    => $siswa->nama_lengkap,
                'nis'             => $siswa->nis,
                'kelas'           => $siswa->kelas?->nama ?? '-',
                'jurusan'         => $siswa->kelas?->jurusan?->nama ?? '-',
                'total_hadir'     => (int) ($stats->total_hadir ?? 0),
                'total_terlambat' => (int) ($stats->total_terlambat ?? 0),
                'total_sakit'     => (int) ($stats->total_sakit ?? 0),
                'total_izin'      => (int) ($stats->total_izin ?? 0),
                'total_alpha'     => (int) ($stats->total_alpha ?? 0),
                'total_absensi'   => (int) ($stats->total_absensi ?? 0),
                'skor'            => ($startDate && $endDate) ? null : ($leaderboard?->score ?? 0),
                'rank'            => ($startDate && $endDate) ? null : ($leaderboard?->rank ?? null),
                'jumlah_badge'    => $siswa->studentBadges->count(),
                'badge_list'      => $badgeList->values()->toArray(),
            ];
        })->sortBy(function ($item) {
            // Urutkan: rank terkecil dulu, null rank paling bawah
            return $item['rank'] ?? PHP_INT_MAX;
        })->values();
    }

    /**
     * Rekap per-kelas dengan total kehadiran, percentage, rank, dan jumlah badge.
     *
     * Filter yang didukung:
     *   - periode          : string
     *   - bulan            : string "YYYY-MM" → hitung ulang dari absensi_siswa
     *   - tahun_akademik_id : int|string
     *
     * @param  array $filters
     * @return Collection
     */
    public function getRekapKelas(array $filters = []): Collection
    {
        $tahunAkademikId = $filters['tahun_akademik_id'] ?? null;
        $kelasId = $filters['kelas_id'] ?? null;

        // ── Ambil semua kelas ────────────────────────────────────────────────
        $kelasQuery = Kelas::query()->with([
            'jurusan',
            'classLeaderboard' => function ($q) use ($tahunAkademikId) {
                if ($tahunAkademikId) {
                    $q->where('tahun_akademik_id', $tahunAkademikId);
                }
                $q->orderByDesc('calculated_at')->limit(1);
            },
        ]);

        if ($tahunAkademikId) {
            $kelasQuery->where('tahun_akademik_id', $tahunAkademikId);
        }

        if ($kelasId) {
            $kelasQuery->where('id', $kelasId);
        }

        $kelasList = $kelasQuery->get();

        if ($kelasList->isEmpty()) {
            return collect();
        }

        $kelasIds = $kelasList->pluck('id');

        // ── Hitung total siswa per kelas ─────────────────────────────────────
        $siswaPerKelas = Siswa::whereIn('kelas_id', $kelasIds)
            ->where('status', 'aktif')
            ->when($tahunAkademikId, fn ($q) => $q->where('tahun_akademik_id', $tahunAkademikId))
            ->select('kelas_id', DB::raw('COUNT(*) AS total_siswa'))
            ->groupBy('kelas_id')
            ->get()
            ->keyBy('kelas_id');

        // ── Hitung absensi per kelas ─────────────────────────────────────────
        [$startDate, $endDate] = $this->getDateRange($filters);

        $absensiQuery = AbsensiSiswa::whereIn('kelas_id', $kelasIds)
            ->select(
                'kelas_id',
                DB::raw("SUM(CASE WHEN status IN ('Hadir','hadir','Terlambat','terlambat') THEN 1 ELSE 0 END) AS total_present"),
                DB::raw('COUNT(*) AS total_attendance')
            );

        if ($startDate && $endDate) {
            $absensiQuery->whereBetween('tanggal', [$startDate, $endDate]);
        }

        $absensiStats = $absensiQuery->groupBy('kelas_id')->get()->keyBy('kelas_id');

        // ── Hitung jumlah badge yang diraih siswa per kelas ──────────────────
        // Ambil siswa_id per kelas dulu
        $siswaByKelas = Siswa::whereIn('kelas_id', $kelasIds)
            ->where('status', 'aktif')
            ->when($tahunAkademikId, fn ($q) => $q->where('tahun_akademik_id', $tahunAkademikId))
            ->select('id', 'kelas_id')
            ->get()
            ->groupBy('kelas_id');

        $badgePerKelas = [];
        foreach ($siswaByKelas as $kId => $siswaGroup) {
            $siswaIds = $siswaGroup->pluck('id');
            $studentBadgeQuery = StudentBadge::whereIn('siswa_id', $siswaIds);
            if ($startDate && $endDate) {
                $studentBadgeQuery->whereBetween('earned_at', [$startDate, $endDate]);
            }
            $badgePerKelas[$kId] = $studentBadgeQuery->count();
        }

        // ── Bangun hasil ─────────────────────────────────────────────────────
        return $kelasList->map(function (Kelas $kelas) use ($siswaPerKelas, $absensiStats, $badgePerKelas, $startDate, $endDate) {
            $stats     = $absensiStats->get($kelas->id);
            $leaderboard = $kelas->classLeaderboard->first();

            $totalPresent    = (int) ($stats?->total_present ?? 0);
            $totalAttendance = (int) ($stats?->total_attendance ?? 0);
            $totalSiswa      = (int) ($siswaPerKelas->get($kelas->id)?->total_siswa ?? 0);
            $percentage      = $totalAttendance > 0
                ? round(($totalPresent / $totalAttendance) * 100, 2)
                : 0;

            return [
                'kelas_id'            => $kelas->id,
                'nama'                => $kelas->nama,
                'jurusan'             => $kelas->jurusan?->nama ?? '-',
                'total_siswa'         => $totalSiswa,
                'total_kehadiran'     => $totalAttendance,
                'total_present'       => $totalPresent,
                'percentage'          => $percentage,
                'rank'                => ($startDate && $endDate) ? null : ($leaderboard?->rank ?? null),
                'jumlah_badge_diraih' => $badgePerKelas[$kelas->id] ?? 0,
            ];
        })->sortBy(function ($item) {
            return $item['rank'] ?? PHP_INT_MAX;
        })->values();
    }

    /**
     * Rekap badge: daftar badge aktif beserta penerima dan statistiknya.
     *
     * Filter yang didukung:
     *   - kelas_id : int|string
     *   - periode  : string
     *   - bulan    : string "YYYY-MM"
     *
     * @param  array $filters
     * @return Collection
     */
    public function getRekapBadge(array $filters = []): Collection
    {
        $kelasId = $filters['kelas_id'] ?? null;

        // ── Ambil semua badge aktif ──────────────────────────────────────────
        $badges = Badge::where('is_active', true)->get();

        if ($badges->isEmpty()) {
            return collect();
        }

        // ── Siapkan filter siswa_id jika ada filter kelas ────────────────────
        $siswaIds = null;
        if ($kelasId) {
            $siswaIds = Siswa::where('kelas_id', $kelasId)
                ->where('status', 'aktif')
                ->pluck('id');
        }

        [$startDate, $endDate] = $this->getDateRange($filters);

        // ── Untuk setiap badge, ambil daftar penerima ────────────────────────
        return $badges->map(function (Badge $badge) use ($siswaIds, $startDate, $endDate) {
            $sbQuery = StudentBadge::where('badge_id', $badge->id)
                ->with(['siswa' => fn ($q) => $q->select('id', 'nama_lengkap', 'kelas_id')->with('kelas:id,nama')]);

            if ($siswaIds !== null) {
                $sbQuery->whereIn('siswa_id', $siswaIds);
            }

            if ($startDate && $endDate) {
                $sbQuery->whereBetween('earned_at', [$startDate, $endDate]);
            }

            $penerima = $sbQuery->get()->map(fn ($sb) => [
                'siswa_id'   => $sb->siswa_id,
                'nama'       => $sb->siswa?->nama_lengkap ?? '-',
                'kelas'      => $sb->siswa?->kelas?->nama ?? '-',
                'earned_at'  => $sb->earned_at?->format('Y-m-d H:i:s'),
            ]);

            return [
                'badge_id'       => $badge->id,
                'name'           => $badge->name,
                'icon'           => $badge->icon,
                'description'    => $badge->description,
                'badge_type'     => $badge->badge_type,
                'total_penerima' => $penerima->count(),
                'penerima'       => $penerima->values()->toArray(),
            ];
        });
    }

    /**
     * Statistik ringkasan gamifikasi untuk dashboard.
     *
     * @param  string|int|null $tahunAkademikId
     * @return array
     */
    public function getSummaryStats($tahunAkademikId = null): array
    {
        $leaderboardQuery = StudentLeaderboard::query();
        $classLeaderboardQuery = ClassLeaderboard::query();

        if ($tahunAkademikId) {
            $leaderboardQuery->where('tahun_akademik_id', $tahunAkademikId);
            $classLeaderboardQuery->where('tahun_akademik_id', $tahunAkademikId);
        }

        $totalSiswaAktif = $leaderboardQuery->distinct('siswa_id')->count('siswa_id');
        $totalBadgeDiraih = StudentBadge::count();
        $totalKelasAktif = $classLeaderboardQuery->distinct('kelas_id')->count('kelas_id');
        $avgKehadiranPersen = round(
            (float) ($classLeaderboardQuery->avg('percentage') ?? 0),
            2
        );

        return [
            'total_siswa_aktif'    => $totalSiswaAktif,
            'total_badge_diraih'   => $totalBadgeDiraih,
            'total_kelas_aktif'    => $totalKelasAktif,
            'avg_kehadiran_persen' => $avgKehadiranPersen,
        ];
    }
}

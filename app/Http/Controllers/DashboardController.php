<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\Holiday;
use App\Models\IzinSakit;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Models\User;
use App\Support\QrScanLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on the user's role.
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        // Redirect based on role if accessed via generic /dashboard
        if ($request->routeIs('dashboard')) {
            if ($role === User::ROLE_SISWA) return redirect()->route('siswa.dashboard');
            if ($role === User::ROLE_GURU) return redirect()->route('guru.dashboard');
            if ($role === User::ROLE_WALI_KELAS) return redirect()->route('wali-kelas.dashboard');
            if ($role === User::ROLE_ORANG_TUA) return redirect()->route('ortu.dashboard');
            if ($role === User::ROLE_SUPER_ADMIN || $role === User::ROLE_ADMIN_SEKOLAH) return view('dashboards.super-admin', array_merge(['user' => $user, 'pageTitle' => 'Admin Panel'], $this->superAdminData()));
        }

        $viewMap = [
            User::ROLE_SUPER_ADMIN    => 'dashboards.super-admin',
            User::ROLE_ADMIN_SEKOLAH  => 'dashboards.admin-sekolah',
            User::ROLE_OPERATOR       => 'dashboards.operator',
            User::ROLE_GURU           => 'dashboards.guru',
            User::ROLE_WALI_KELAS     => 'dashboards.wali-kelas',
            User::ROLE_STAFF_TU       => 'dashboards.staff-tu',
            User::ROLE_SISWA          => 'dashboards.siswa',
            User::ROLE_ORANG_TUA      => 'dashboards.orang-tua',
        ];

        $titleMap = [
            User::ROLE_SUPER_ADMIN    => 'Admin Panel',
            User::ROLE_ADMIN_SEKOLAH  => 'Admin Panel',
            User::ROLE_OPERATOR       => 'Panel Operator',
            User::ROLE_GURU           => 'Portal Pendidik',
            User::ROLE_WALI_KELAS     => 'Portal Wali Kelas',
            User::ROLE_STAFF_TU       => 'Portal Staff',
            User::ROLE_SISWA          => 'Portal Siswa',
            User::ROLE_ORANG_TUA      => 'Portal Orang Tua',
        ];

        $view = $viewMap[$role] ?? 'dashboards.default';
        $pageTitle = $titleMap[$role] ?? 'Dashboard';

        $data = [
            'user' => $user,
            'pageTitle' => $pageTitle
        ];

        // Specific data fetching based on role
        if ($role === User::ROLE_SUPER_ADMIN || $role === User::ROLE_ADMIN_SEKOLAH) {
            $data = array_merge($data, $this->superAdminData());
        } elseif ($role === User::ROLE_OPERATOR) {
            $data = array_merge($data, $this->operatorData());
        } elseif ($role === User::ROLE_STAFF_TU) {
            $data = array_merge($data, $this->staffTuData($user));
        } elseif ($role === User::ROLE_WALI_KELAS) {
            $data = array_merge($data, $this->waliKelasData($user));
        } elseif ($role === User::ROLE_GURU) {
            $data = array_merge($data, $this->guruData($user));
        } elseif ($role === User::ROLE_ORANG_TUA) {
            $data = array_merge($data, $this->orangTuaData($user));
        } elseif ($role === User::ROLE_SISWA) {
            $data = array_merge($data, $this->siswaData($user));
        }

        return view($view, $data);
    }

    /**
     * Halaman Live Attendance Monitor (Fullscreen untuk TV/Proyektor).
     */
    public function liveMonitor()
    {
        return view('admin.live-monitor', $this->superAdminData());
    }

    /**
     * Halaman Statistik & Perbandingan Kelas.
     */
    public function statistikKelas(Request $request)
    {
        $month = $request->query('month', now()->month);
        $year  = $request->query('year', now()->year);
        $today = Carbon::today();

        // 1. Ranking Kelas
        // Hitung % hadir: (total hadir + terlambat) / (total siswa * hari efektif)
        $kelas = Kelas::withCount('siswa')->get();
        $rankingKelas = [];
        
        // Asumsi hari efektif di bulan ini adalah hari yang sudah lewat
        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfPeriod = ($month == now()->month && $year == now()->year) ? now() : $startOfMonth->copy()->endOfMonth();
        $daysPassed = AbsensiSiswa::whereBetween('tanggal', [$startOfMonth, $endOfPeriod])
            ->distinct('tanggal')
            ->count('tanggal') ?: 1;

        foreach ($kelas as $k) {
            $totalSiswa = $k->siswa_count;
            if ($totalSiswa == 0) continue;

            $totalHadir = AbsensiSiswa::where('kelas_id', $k->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfPeriod])
                ->whereIn('status', ['hadir', 'terlambat'])
                ->count();
            
            $percentage = ($totalHadir / ($totalSiswa * $daysPassed)) * 100;
            
            $rankingKelas[] = [
                'id' => $k->id,
                'nama' => $k->nama,
                'total_siswa' => $totalSiswa,
                'total_hadir' => $totalHadir,
                'percentage' => round($percentage, 1)
            ];
        }
        usort($rankingKelas, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        // 2. Top 5 Siswa Rajin
        $topSiswa = AbsensiSiswa::whereBetween('tanggal', [$startOfMonth, $endOfPeriod])
            ->whereIn('status', ['hadir', 'terlambat'])
            ->selectRaw('siswa_id, COUNT(*) as total_hadir')
            ->groupBy('siswa_id')
            ->orderByDesc('total_hadir')
            ->limit(10)
            ->with('siswa.kelas')
            ->get();

        // 3. Early Warning (Alpha >= 3)
        $warningSiswa = AbsensiSiswa::whereBetween('tanggal', [$startOfMonth, $endOfPeriod])
            ->where('status', 'alpha')
            ->selectRaw('siswa_id, COUNT(*) as total_alpha')
            ->groupBy('siswa_id')
            ->having('total_alpha', '>=', 3)
            ->orderByDesc('total_alpha')
            ->with('siswa.kelas')
            ->get();

        return view('admin.statistik-kelas', compact('rankingKelas', 'topSiswa', 'warningSiswa', 'month', 'year', 'daysPassed'));
    }

    /**
     * AJAX: Proses scan QR dari dashboard utama (Super Admin/Admin Sekolah).
     */
    /**
     * Process QR code scan via AJAX.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scanQrAjax(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string|max:255',
        ]);

        $ip     = $request->ip();
        $qrCode = $data['qr_code'];

        $siswa = Siswa::with('kelas')->where('qr_code', $qrCode)->first();

        if (! $siswa) {
            return response()->json([
                'success' => false,
                'message' => 'QR code tidak dikenal.',
            ]);
        }

        $tanggal = now()->toDateString();
        $sudahAda = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($sudahAda) {
            return response()->json([
                'success' => false,
                'already' => true,
                'message' => 'Sudah tercatat absen.',
                'siswa'   => [
                    'nama'  => $siswa->nama_lengkap,
                    'kelas' => $siswa->kelas?->nama ?? '-',
                    'jam'   => $sudahAda->jam_masuk,
                ],
            ]);
        }

        $jamMasuk = now()->format('H:i:s');
        $absensi = AbsensiSiswa::create([
            'siswa_id'    => $siswa->id,
            'kelas_id'    => $siswa->kelas_id,
            'tanggal'     => $tanggal,
            'jam_masuk'   => $jamMasuk,
            'status'      => 'hadir',
            'keterangan'  => 'Scan QR Dashboard Utama',
            'metode'      => 'qr',
        ]);

        ActivityLog::record('scan', 'absensi', "Scan QR: {$siswa->nama_lengkap} ({$siswa->kelas?->nama}) — {$jamMasuk}");

        return response()->json([
            'success' => true,
            'message' => 'Berhasil tercatat!',
            'siswa'   => [
                'nama'  => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas?->nama ?? '-',
                'jam'   => $jamMasuk,
            ],
        ]);
    }

    /**
     * Halaman Kalender Absensi visual bulanan.
     */
    public function kalenderAbsensi(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        // Ambil semua absensi dalam bulan ini, group by tanggal
        $rawAbsensi = AbsensiSiswa::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->selectRaw('tanggal, status, COUNT(*) as total')
            ->groupBy('tanggal', 'status')
            ->get();

        $totalSiswa = Siswa::count();

        // Bangun map: tanggal => ['hadir'=>N, 'sakit'=>N, ...]
        $calendarData = [];
        foreach ($rawAbsensi as $row) {
            $key = $row->tanggal;
            if (!isset($calendarData[$key])) {
                $calendarData[$key] = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'terlambat' => 0, 'total' => 0];
            }
            $calendarData[$key][$row->status] = $row->total;
            $calendarData[$key]['total'] += $row->total;
        }

        // Hitung hari pertama dalam bulan (0=Sun ... 6=Sat) → Bootstrap offset
        $firstDayOfWeek = $startOfMonth->dayOfWeek; // 0=Sun
        // Konversi ke Senin = 0
        $offset = ($firstDayOfWeek + 6) % 7;

        $daysInMonth = $startOfMonth->daysInMonth;
        $today       = Carbon::today()->toDateString();

        // Ambil data libur dalam bulan ini
        $holidays = Holiday::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->pluck('nama', 'tanggal')
            ->toArray();

        // Prev / Next bulan
        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        return view('admin.kalender-absensi', compact(
            'calendarData', 'totalSiswa', 'offset', 'daysInMonth',
            'month', 'year', 'today', 'prevMonth', 'nextMonth', 'startOfMonth',
            'holidays'
        ));
    }

    /**
     * AJAX: Detail absensi untuk 1 tanggal (dipakai modal kalender).
     */
    public function kalenderDetail(Request $request)
    {
        $tanggal = $request->query('tanggal');
        if (!$tanggal) return response()->json([]);

        $stats = AbsensiSiswa::whereDate('tanggal', $tanggal)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

return response()->json([
            'hadir'     => $stats['hadir']     ?? 0,
            'sakit'     => $stats['sakit']     ?? 0,
            'izin'      => $stats['izin']      ?? 0,
            'alpha'     => $stats['alpha']     ?? 0,
            'terlambat' => $stats['terlambat'] ?? 0,
            'tanggal'   => $tanggal,
        ]);
    }

    public function holidays(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $holidays = Holiday::whereYear('tanggal', $year)->orderBy('tanggal')->get();

        return view('admin.holidays', compact('holidays', 'year'));
    }

    public function holidaysStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:school',
        ]);

        Holiday::create([
            'tanggal' => $validated['tanggal'],
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'is_national_holiday' => false,
        ]);

        return back()->with('success', 'Hari libur sekolah berhasil ditambahkan.');
    }

    public function holidaysDestroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        if ($holiday->jenis === 'school') {
            $holiday->delete();
            return back()->with('success', 'Hari libur berhasil dihapus.');
        }
        return back()->with('error', 'Tidak dapat menghapus hari libur nasional.');
    }

private function superAdminData(): array
    {
        $today = Carbon::today();

        // ── Summary counts dengan cache query ───────────────────────────────
        $tahunId = session('tahun_akademik_id');
        $totalSiswa = Cache::remember('superadmin_total_siswa_'.$tahunId, 60, function() use ($tahunId) {
            return Siswa::where('tahun_akademik_id', $tahunId)->count();
        });
        $totalSiswaWajibAbsen = Cache::remember('superadmin_total_siswa_absen_'.$tahunId, 60, function() use ($tahunId) {
            return Siswa::where('tahun_akademik_id', $tahunId)
                ->whereHas('kelas', fn($q) => $q->where('is_aktif_absensi', true))
                ->count();
        });
        $totalGuru  = Cache::remember('superadmin_total_guru', 60, fn() => Guru::count());
        $totalStaff = Cache::remember('superadmin_total_staff', 60, fn() => StaffTataUsaha::count());
        $totalKelas = Cache::remember('superadmin_total_kelas_'.$tahunId, 60, function() use ($tahunId) {
            return Kelas::where('tahun_akademik_id', $tahunId)->count();
        });

        $totalAbsensiHariIni = Cache::remember('superadmin_absensi_hari_ini', 30, function() use ($today) {
            return AbsensiSiswa::whereDate('tanggal', $today)->count();
        });
        $totalIzinPending = Cache::remember('superadmin_izin_pending', 30, fn() => IzinSakit::where('status', 'pending')->count());

        // ── Donut chart: status distribution today ────────────────────────
        $statusHariIni = AbsensiSiswa::whereDate('tanggal', $today)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $hadirCount    = $statusHariIni['hadir']     ?? 0;
        $sakitCount    = $statusHariIni['sakit']     ?? 0;
        $izinCount     = $statusHariIni['izin']      ?? 0;
        $alphaCount    = $statusHariIni['alpha']     ?? 0;
        $terlambatCount = $statusHariIni['terlambat'] ?? 0;
        $belumAbsen    = max(0, $totalSiswaWajibAbsen - $totalAbsensiHariIni);

        // ── Bar chart: 7-day multi-series (optimasi: single query) ────────────────
        $chartDays   = [];
        $chartHadir  = [];
        $chartSakit  = [];
        $chartIzin   = [];
        $chartAlpha  = [];

        $startDate = $today->copy()->subDays(6);
        $allStats = AbsensiSiswa::whereBetween('tanggal', [$startDate, $today])
            ->select('tanggal', 'status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('tanggal', 'status')
            ->get()
            ->groupBy('tanggal');

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $chartDays[] = $date->translatedFormat('D d/m');

            $dayData = $allStats[$date->toDateString()] ?? collect();
            $chartHadir[] = $dayData->firstWhere('status', 'hadir')?->total ?? 0;
            $chartSakit[] = $dayData->firstWhere('status', 'sakit')?->total ?? 0;
            $chartIzin[]  = $dayData->firstWhere('status', 'izin')?->total ?? 0;
            $chartAlpha[] = $dayData->firstWhere('status', 'alpha')?->total ?? 0;
        }

        // ── Absensi Guru & Staff hari ini ─────────────────────────────
        $absensiGuruHariIni  = AbsensiGuru::whereDate('tanggal', $today)->count();
        $absensiStaffHariIni = AbsensiStaff::whereDate('tanggal', $today)->count();

        // ── Leaderboards (limit, select spesifik) ────────────────────────────
        $palingAwal = AbsensiSiswa::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->where('status', 'hadir')
            ->orderBy('jam_masuk', 'asc')
            ->limit(10)
            ->with(['siswa' => fn($q) => $q->select('id', 'nama_lengkap', 'kelas_id')->with(['kelas' => fn($q2) => $q2->select('id', 'nama')])])
            ->get();

        $palingAkhir = AbsensiSiswa::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->orderBy('jam_masuk', 'desc')
            ->limit(10)
            ->with(['siswa' => fn($q) => $q->select('id', 'nama_lengkap', 'kelas_id')->with(['kelas' => fn($q2) => $q2->select('id', 'nama')])])
            ->get();

        // ── Pengaturan & Info ─────────────────────────────────────────────
        $pengaturanArr = Cache::remember('superadmin_pengaturan', 300, fn() => Pengaturan::pluck('value', 'key')->toArray());

        return compact(
            'totalSiswa', 'totalGuru', 'totalStaff', 'totalKelas',
            'totalAbsensiHariIni', 'totalIzinPending',
            'hadirCount', 'sakitCount', 'izinCount', 'alphaCount', 'terlambatCount', 'belumAbsen',
            'chartDays', 'chartHadir', 'chartSakit', 'chartIzin', 'chartAlpha',
            'absensiGuruHariIni', 'absensiStaffHariIni',
            'palingAwal', 'palingAkhir', 'pengaturanArr'
        );
    }

    private function operatorData(): array
    {
        $today = Carbon::today();
        $tahunId = session('tahun_akademik_id');

        return [
            'totalSiswa' => Siswa::where('tahun_akademik_id', $tahunId)->count(),
            'totalGuru'  => Guru::count(),
            'totalStaff' => StaffTataUsaha::count(),
            'totalIzinPending' => IzinSakit::where('status', 'pending')->count(),
            'totalAbsensiHariIni' => AbsensiSiswa::whereDate('tanggal', $today)->count(),
            'kegiatanAktif' => \App\Models\Kegiatan::whereDate('tanggal_pelaksanaan', $today)->count(),
        ];
    }

    private function waliKelasData($user): array
    {
        $today = Carbon::today();
        $kelas = Kelas::where('guru_id', $user->id)->first();

        if (!$kelas) {
            return ['has_class' => false];
        }

        $siswaIds = Siswa::where('kelas_id', $kelas->id)->pluck('id');
        
        return [
            'has_class' => true,
            'kelas_nama' => $kelas->nama,
            'total_siswa' => $siswaIds->count(),
            'hadir_hari_ini' => AbsensiSiswa::whereIn('siswa_id', $siswaIds)->whereIn('status', ['hadir', 'terlambat'])->whereDate('tanggal', $today)->count(),
            'tidak_hadir' => AbsensiSiswa::whereIn('siswa_id', $siswaIds)->whereIn('status', ['sakit', 'izin', 'alpha'])->whereDate('tanggal', $today)->count(),
            'pending_izin_kelas' => IzinSakit::whereIn('reference_id', $siswaIds)
                ->where('tipe', 'siswa')
                ->where('status', 'pending')
                ->count(),
        ];
    }

    private function guruData($user): array
    {
        $today = Carbon::today();
        $guru = Guru::where('user_id', $user->id)->first();
        
        if (!$guru) {
            return [
                'hadir_saya' => null,
                'total_absen_bulan_ini' => 0,
                'total_izin_bulan_ini' => 0,
                'total_jam_mengajar' => 0,
                'attendance_streak' => 0,
            ];
        }

        // Hitung total jam mengajar bulan ini (asumsi 1 kehadiran guru = X jam, tapi mari kita ambil dari JadwalPelajaran atau hitung secara dummy karena belum ada struktur 'jam_mengajar' spesifik)
        // Kita hitung jumlah mapel/jadwal yang diajar guru ini bulan ini
        // Atau jika AbsensiGuru hanya mencatat kehadiran harian, kita gunakan kehadiran harian * 8 jam (asumsi 8 jam per hari).
        $jamMengajar = AbsensiGuru::where('guru_id', $guru->id)
                                  ->whereMonth('tanggal', now()->month)
                                  ->whereIn('status', ['hadir', 'terlambat'])
                                  ->count() * 8; // Asumsi 8 jam per hari

        // Hitung Attendance Streak (Consecutive days hadir)
        $streakGuru = 0;
        $absensiLaluGuru = AbsensiGuru::where('guru_id', $guru->id)
                                ->whereIn('status', ['hadir', 'terlambat'])
                                ->orderBy('tanggal', 'desc')
                                ->pluck('tanggal')->toArray();
        $checkDateGuru = Carbon::today();
        foreach ($absensiLaluGuru as $tgl) {
            if (Carbon::parse($tgl)->isSameDay($checkDateGuru) || Carbon::parse($tgl)->isSameDay($checkDateGuru->copy()->subDay()) && (Carbon::parse($tgl)->isWeekday() || \App\Models\Holiday::where('tanggal', $tgl)->exists() == false)) {
                $streakGuru++;
                $checkDateGuru = Carbon::parse($tgl)->subDay();
                // skip weekends if needed, simplified here
            } else {
                break;
            }
        }

        return [
            'hadir_saya' => AbsensiGuru::where('guru_id', $guru->id)->whereDate('tanggal', $today)->first(),
            'total_absen_bulan_ini' => AbsensiGuru::where('guru_id', $guru->id)->whereMonth('tanggal', now()->month)->count(),
            'total_izin_bulan_ini' => AbsensiGuru::where('guru_id', $guru->id)->whereIn('status', ['sakit', 'izin'])->whereMonth('tanggal', now()->month)->count(),
            'total_jam_mengajar' => $jamMengajar,
            'attendance_streak' => $streakGuru,
        ];
    }

    private function staffTuData($user): array
    {
        $today = Carbon::today();
        $staff = StaffTataUsaha::where('user_id', $user->id)->first();
        
        if (!$staff) {
            return [
                'hadir_saya' => null,
                'total_absen_bulan_ini' => 0,
                'total_izin_bulan_ini' => 0,
            ];
        }

        return [
            'hadir_saya' => AbsensiStaff::where('staff_id', $staff->id)->whereDate('tanggal', $today)->first(),
            'total_absen_bulan_ini' => AbsensiStaff::where('staff_id', $staff->id)->whereMonth('tanggal', now()->month)->count(),
            'total_izin_bulan_ini' => AbsensiStaff::where('staff_id', $staff->id)->whereIn('status', ['sakit', 'izin'])->whereMonth('tanggal', now()->month)->count(),
        ];
    }

    private function orangTuaData($user): array
    {
        $children = Siswa::with('kelas')
            ->where('ortu_user_id', $user->id)
            ->get();

        $today = Carbon::today();
        $month = now()->month;
        $year = now()->year;

        $anakData = $children->map(function ($anak) use ($today, $month, $year) {
            $absensiHariIni = AbsensiSiswa::where('siswa_id', $anak->id)
                ->whereDate('tanggal', $today)
                ->first();

            $statsRaw = AbsensiSiswa::where('siswa_id', $anak->id)
                ->whereMonth('tanggal', $month)
                ->whereYear('tanggal', $year)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $hadir = ($statsRaw['hadir'] ?? 0) + ($statsRaw['terlambat'] ?? 0);
            $alpha = $statsRaw['alpha'] ?? 0;
            $izinSakit = ($statsRaw['izin'] ?? 0) + ($statsRaw['sakit'] ?? 0);

            return [
                'siswa' => $anak,
                'absensi_hari_ini' => $absensiHariIni,
                'stats' => [
                    'hadir' => $hadir,
                    'alpha' => $alpha,
                    'izin_sakit' => $izinSakit,
                ],
                'early_warning' => $alpha >= 3,
            ];
        });

        return [
            'anakList' => $anakData,
        ];
    }

    private function siswaData($user): array
    {
        $siswa = Siswa::where('user_id', $user->id)->first();
        if (!$siswa) return ['attendance_streak' => 0, 'greeting_message' => ''];

        // Calculate streak
        $streak = 0;
        $today = Carbon::today();
        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
                    ->whereIn('status', ['hadir', 'terlambat'])
                    ->orderBy('tanggal', 'desc')
                    ->pluck('tanggal')->toArray();
        
        $checkDate = $today;
        // Simple streak logic: check if present today or yesterday (ignoring weekends for simplicity, or just straight days)
        // A more robust logic would skip weekends/holidays.
        foreach ($absensi as $tgl) {
            $tglCarbon = Carbon::parse($tgl);
            // If the date is the check date, increment streak and check previous day
            // Or if it's Friday and checkdate is Monday...
            if ($tglCarbon->isSameDay($checkDate)) {
                $streak++;
                $checkDate->subDay();
                // Skip weekends
                while ($checkDate->isWeekend()) {
                    $checkDate->subDay();
                }
            } else if ($tglCarbon->isSameDay($checkDate->copy()->subDay()) || $tglCarbon->isSameDay($today->copy()->subDay())) {
                // To allow streak to continue if they haven't checked in yet today
                if ($streak == 0 && $tglCarbon->isSameDay($today->copy()->subDay())) {
                    $streak++;
                    $checkDate = $tglCarbon->subDay();
                    while ($checkDate->isWeekend()) {
                        $checkDate->subDay();
                    }
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        $messages = [
            1 => "Awal yang baik! Pertahankan kehadiranmu.",
            3 => "Luar biasa! 3 hari berturut-turut hadir.",
            5 => "Fantastic! Kamu tidak pernah absen minggu ini.",
            10 => "Super! 10 hari tanpa henti, kamu memang luar biasa!",
            20 => "Unstoppable! Dedikasimu patut diacungi jempol."
        ];

        $greeting = "Terima kasih sudah absen hari ini!";
        $highestKey = 1;
        foreach ($messages as $days => $msg) {
            if ($streak >= $days) {
                $highestKey = $days;
            }
        }
        if ($streak > 0) {
            $greeting = $messages[$highestKey];
        }

        return [
            'attendance_streak' => $streak,
            'greeting_message' => $greeting
        ];
    }

    public function analytics(Request $request)
    {
        return view('admin.analytics.index');
    }

    public function gamifikasi(Request $request)
    {
        return view('admin.gamifikasi.index');
    }

    public function reminderSettings(Request $request)
    {
        return view('admin.reminder.index');
    }

    public function sendWeeklyDigest(Request $request)
    {
        try {
            // Get data for the last 7 days
            $startDate = Carbon::today()->subDays(7);
            $endDate = Carbon::today();
            
            $totalHadir = AbsensiSiswa::whereBetween('tanggal', [$startDate, $endDate])->whereIn('status', ['hadir', 'terlambat'])->count();
            $totalSakit = AbsensiSiswa::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'sakit')->count();
            $totalIzin = AbsensiSiswa::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'izin')->count();
            $totalAlpha = AbsensiSiswa::whereBetween('tanggal', [$startDate, $endDate])->where('status', 'alpha')->count();
            
            // Build message
            $message = "*Weekly Digest Kehadiran Siswa*\n";
            $message .= "Periode: " . $startDate->translatedFormat('d M Y') . " - " . $endDate->translatedFormat('d M Y') . "\n\n";
            $message .= "📊 *Ringkasan 7 Hari Terakhir:*\n";
            $message .= "✅ Hadir/Terlambat: $totalHadir\n";
            $message .= "🤒 Sakit: $totalSakit\n";
            $message .= "📝 Izin: $totalIzin\n";
            $message .= "❌ Alpha: $totalAlpha\n\n";
            $message .= "Pesan otomatis dari Sistem Presensi Digital.";

            // Assume the headmaster's number is saved in settings or hardcoded for now
            $headmasterNumber = Pengaturan::where('key', 'nomor_wa_kepsek')->value('value');
            if (!$headmasterNumber) {
                // fallback to a dummy number for testing if not set
                $headmasterNumber = '6281234567890';
            }

            $waSettings = Pengaturan::whereIn('key', ['wa_api_key', 'wa_sender'])->pluck('value', 'key')->toArray();
            $apiKey = $waSettings['wa_api_key'] ?? env('WA_API_KEY');
            $sender = $waSettings['wa_sender'] ?? env('WA_SENDER');

            if (!$apiKey || !$sender) {
                return response()->json(['success' => false, 'message' => 'Konfigurasi WA Gateway belum diset.']);
            }

            // Send via Http Facade
            $response = Http::withoutVerifying()->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $data);

            $result = $response->json();
            $httpCode = $response->status();
            
            if ($httpCode == 200 && isset($result['status']) && $result['status'] === true) {
                return response()->json(['success' => true, 'message' => 'Weekly Digest berhasil dikirim ke Kepala Sekolah.']);
            } else {
                Log::error('WA Gateway Error: ' . $response);
                return response()->json(['success' => false, 'message' => 'Gagal mengirim pesan via WA Gateway.']);
            }

        } catch (\Exception $e) {
            Log::error('Weekly Digest Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.']);
        }
    }
}

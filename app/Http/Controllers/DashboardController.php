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
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\GamifikasiRekapService;
use App\Support\QrScanLogger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        
        $availableRoles = array_unique(array_filter(array_merge([$user->role], $user->roles ?? [])));
        
        if (count($availableRoles) > 1) {
            if (!session()->has('active_role')) {
                return redirect()->route('role.select');
            }
            $role = session('active_role');
        } else {
            if (!session()->has('active_role')) {
                session(['active_role' => $user->role]);
            }
            $role = session('active_role', $user->role);
        }

        // Redirect based on role if accessed via generic /dashboard
        if ($request->routeIs('dashboard')) {
            if ($role === User::ROLE_SISWA) return redirect()->route('siswa.dashboard');
            if ($role === User::ROLE_GURU) return redirect()->route('guru.dashboard');
            if ($role === User::ROLE_WALI_KELAS) return redirect()->route('wali-kelas.dashboard');
            if ($role === User::ROLE_ORANG_TUA) return redirect()->route('ortu.dashboard');
            if ($role === User::ROLE_PIKET || ($role === User::ROLE_PIKET && $user->isPiket())) return redirect()->route('piket.dashboard');
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
            User::ROLE_PIKET          => 'dashboards.piket',
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
            User::ROLE_PIKET          => 'Portal Guru Piket',
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
        } elseif ($role === User::ROLE_PIKET) {
            $data = array_merge($data, $this->piketData());
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
     * AJAX: Refresh dashboard statistics.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshStats(Request $request): JsonResponse
    {
        return response()->json($this->superAdminData());
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
        $kelas = Kelas::with(['siswa' => fn($q) => $q->where('status', 'aktif')])->withCount('siswa')->get();
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
            ->limit(5)
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
            $key = $row->tanggal->format('Y-m-d');
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

        // Ambil data libur dalam bulan ini beserta kelasnya
        $holidaysCollection = Holiday::with('kelas')->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->get();
        $holidays = [];
        foreach ($holidaysCollection as $holiday) {
            $dateStr = $holiday->tanggal->toDateString();
            $scopeStr = '';
            if ($holiday->kelas_id) {
                $scopeStr = " ({$holiday->kelas->nama})";
            } elseif ($holiday->tingkat) {
                $scopeStr = " (Tingkat {$holiday->tingkat})";
            }
            $holidayName = $holiday->nama . $scopeStr;
            
            if (isset($holidays[$dateStr])) {
                $holidays[$dateStr] .= ' | ' . $holidayName;
            } else {
                $holidays[$dateStr] = $holidayName;
            }
        }

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
        $kelas = Kelas::where('tahun_akademik_id', session('tahun_akademik_id'))->orderBy('nama')->get();

        return view('admin.holidays', compact('holidays', 'year', 'kelas'));
    }

    public function holidaysSync(Request $request)
    {
        $year = (int) $request->input('year', now()->year);

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()->get('https://raw.githubusercontent.com/guangrei/APIHariLibur_V2/main/calendar.json');
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Sync target year, year - 1, and year + 1
                $years = [$year - 1, $year, $year + 1];

                foreach ($data as $date => $info) {
                    if (isset($info['holiday']) && $info['holiday'] === true) {
                        $parsedYear = (int) date('Y', strtotime($date));
                        
                        if (in_array($parsedYear, $years)) {
                            $summary = $info['summary'][0] ?? 'Hari Libur Nasional';
                            
                            Holiday::updateOrCreate(
                                ['tanggal' => $date],
                                [
                                    'nama' => $summary,
                                    'jenis' => 'national',
                                    'is_national_holiday' => true
                                ]
                            );
                        }
                    }
                }

                return back()->with('success', 'Hari libur nasional berhasil disinkronkan dari API.');
            }
            
            return back()->with('error', 'Gagal mengambil data dari API Hari Libur.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

    public function holidaysStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'jenis' => 'required|in:school',
            'tingkat' => 'nullable|in:X,XI,XII',
            'kelas_id' => 'nullable|exists:kelas,id',
        ]);

        if (!empty($validated['tingkat']) && !empty($validated['kelas_id'])) {
            $kelasObj = Kelas::find($validated['kelas_id']);
            if ($kelasObj && $kelasObj->tingkat !== $validated['tingkat']) {
                return back()->withInput()->withErrors(['tingkat' => 'Tingkat tidak cocok dengan tingkat dari kelas yang dipilih.']);
            }
        }

        Holiday::create([
            'tanggal' => $validated['tanggal'],
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'is_national_holiday' => false,
            'tingkat' => $validated['tingkat'] ?? null,
            'kelas_id' => $validated['kelas_id'] ?? null,
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
        $isWeekend = $today->isSaturday() || $today->isSunday();
        $tahunId = session('tahun_akademik_id');

        // Dapatkan data top 5 poin pelanggaran tertinggi tahun akademik aktif
        $top5Pelanggaran = Siswa::where('tahun_akademik_id', $tahunId)
            ->where('status', 'aktif')
            ->withSum(['pelanggaranSiswa' => function($q) use ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            }], 'poin_saat_itu')
            ->get()
            ->filter(function($item) {
                return $item->pelanggaran_siswa_sum_poin_saat_itu > 0;
            })
            ->sortByDesc('pelanggaran_siswa_sum_poin_saat_itu')
            ->take(5);

        // ── Summary counts dengan cache query ───────────────────────────────
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
        $belumAbsen    = $isWeekend ? 0 : max(0, $totalSiswaWajibAbsen - $totalAbsensiHariIni);

        $tingkatKehadiran = $totalSiswaWajibAbsen > 0 
            ? round((($hadirCount + $terlambatCount) / $totalSiswaWajibAbsen) * 100, 1) 
            : 0;

        // ── Kehadiran per Kelas (hari ini) ──
        $kehadiranPerKelas = Cache::remember('superadmin_kehadiran_per_kelas_'.$tahunId, 5, function() use ($today) {
            return Kelas::where('tahun_akademik_id', session('tahun_akademik_id'))
                ->withCount(['siswa' => fn($q) => $q->where('status', 'aktif')])
                ->get()
                ->map(function($k) use ($today) {
                    $hadirCount = AbsensiSiswa::where('kelas_id', $k->id)
                        ->whereDate('tanggal', $today)
                        ->whereIn('status', ['hadir', 'terlambat'])
                        ->count();
                    return [
                        'nama' => $k->nama,
                        'total_siswa' => $k->siswa_count,
                        'total_hadir' => $hadirCount,
                        'percentage' => $k->siswa_count > 0 ? round(($hadirCount / $k->siswa_count) * 100, 1) : 0,
                    ];
                })
                ->sortByDesc('percentage')
                ->take(6)
                ->values()
                ->toArray();
        });

        // ── Statistik Bulanan (bulan ini) ──
        $monthlyStats = Cache::remember('superadmin_monthly_stats_'.now()->format('Ym'), 30, function() {
            $stats = AbsensiSiswa::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->selectRaw("COUNT(*) as total,
                    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha,
                    SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat")
                ->first();
            
            return [
                'total' => $stats->total ?? 0,
                'sakit' => $stats->sakit ?? 0,
                'izin' => $stats->izin ?? 0,
                'alpha' => $stats->alpha ?? 0,
                'terlambat' => $stats->terlambat ?? 0,
            ];
        });

        // ── Metode Absensi (hari ini) ──
        $metodeAbsensi = Cache::remember('superadmin_metode_absen_'.$today->toDateString(), 5, function() use ($today) {
            $raw = AbsensiSiswa::whereDate('tanggal', $today)
                ->selectRaw('metode, COUNT(*) as total')
                ->groupBy('metode')
                ->pluck('total', 'metode')
                ->toArray();
            
            $labels = [
                'qr' => 'Scan QR',
                'manual' => 'Input Manual',
                'face' => 'Face Recognition',
                'fingerprint' => 'Fingerprint',
                'kartu' => 'Kartu RFID',
            ];
            
            $result = [];
            foreach ($labels as $key => $label) {
                $result[] = [
                    'label' => $label,
                    'key' => $key,
                    'total' => $raw[$key] ?? 0,
                ];
            }
            return $result;
        });

        // ── Log Absensi Real-time ──
        $recentLogs = Cache::remember('superadmin_recent_logs_'.$today->toDateString(), 5, function() use ($today) {
            return AbsensiSiswa::whereDate('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->orderBy('jam_masuk', 'desc')
                ->limit(5)
                ->with(['siswa' => fn($q) => $q->select('id', 'nama_lengkap', 'kelas_id')
                    ->with('kelas:id,nama')])
                ->get();
        });

        // ── Bar chart: 7-hari kerja (skip Sabtu & Minggu) ────────────────
        $chartDays   = [];
        $chartHadir  = [];
        $chartSakit  = [];
        $chartIzin   = [];
        $chartAlpha  = [];

        // Kumpulkan 7 hari kerja terakhir (termasuk hari ini jika weekday)
        $hariKerja = collect();
        $tmp = $today->copy();
        while ($hariKerja->count() < 7) {
            if (!$tmp->isSaturday() && !$tmp->isSunday()) {
                $hariKerja->prepend($tmp->copy());
            }
            $tmp->subDay();
        }

        $startDate = $hariKerja->first();
        $endDate   = $hariKerja->last();
        $allStats = AbsensiSiswa::whereBetween('tanggal', [$startDate, $endDate])
            ->select('tanggal', 'status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('tanggal', 'status')
            ->get()
            ->groupBy(fn($item) => $item->tanggal->toDateString());

        foreach ($hariKerja as $date) {
            $chartDays[] = $date->translatedFormat('D d/m');

            $dayData = $allStats[$date->toDateString()] ?? collect();
            $chartHadir[] = $dayData->firstWhere('status', 'hadir')?->total ?? 0;
            $chartSakit[] = $dayData->firstWhere('status', 'sakit')?->total ?? 0;
            $chartIzin[]  = $dayData->firstWhere('status', 'izin')?->total ?? 0;
            $chartAlpha[] = $dayData->firstWhere('status', 'alpha')?->total ?? 0;
        }

        $sumHadir = array_sum($chartHadir);
        $totalHari = count($chartHadir) ?: 1;
        $rataRataHadir = $totalSiswaWajibAbsen > 0
            ? round(($sumHadir / ($totalSiswaWajibAbsen * $totalHari)) * 100, 1) . '%'
            : '0%';

        $maxHadir = -1;
        $maxIndex = -1;
        foreach ($chartHadir as $index => $value) {
            if ($value > $maxHadir) {
                $maxHadir = $value;
                $maxIndex = $index;
            }
        }
        $hariTerbaik = ($sumHadir > 0 && $maxIndex !== -1) ? $chartDays[$maxIndex] : '-';

        $sumTidakHadir = array_sum($chartSakit) + array_sum($chartIzin) + array_sum($chartAlpha);
        $tingkatKetidakhadiran = $totalSiswaWajibAbsen > 0
            ? round(($sumTidakHadir / ($totalSiswaWajibAbsen * $totalHari)) * 100, 1) . '%'
            : '0%';

        // ── Absensi Guru & Staff hari ini ─────────────────────────────
        $absensiGuruHariIni  = AbsensiGuru::whereDate('tanggal', $today)->count();
        $absensiStaffHariIni = AbsensiStaff::whereDate('tanggal', $today)->count();

        // ── Widget: Belum Absen per Kelas (hari kerja saja) ─────────────────────
        $belumAbsenPerKelas = $isWeekend ? [] : Cache::remember('superadmin_belum_absen_kelas_'.$today->toDateString(), 5, function() use ($today, $tahunId) {
            return Kelas::where('tahun_akademik_id', $tahunId)
                ->withCount(['siswa' => fn($q) => $q->where('status', 'aktif')])
                ->get()
                ->map(function($k) use ($today) {
                    $sudahAbsen = AbsensiSiswa::where('kelas_id', $k->id)
                        ->whereDate('tanggal', $today)
                        ->count();
                    $belumAbsen = max(0, $k->siswa_count - $sudahAbsen);
                    return [
                        'nama' => $k->nama,
                        'belum_absen' => $belumAbsen,
                        'total_siswa' => $k->siswa_count,
                    ];
                })
                ->filter(fn($k) => $k['belum_absen'] > 0)
                ->sortByDesc('belum_absen')
                ->take(10)
                ->values()
                ->toArray();
        });

        // ── Widget: Daftar siswa belum absen (hari kerja saja) ──────────────────
        $listBelumAbsen = $isWeekend ? [] : Cache::remember('superadmin_list_belum_absen_'.$today->toDateString(), 5, function() use ($today, $tahunId) {
            $sudahAbsenIds = AbsensiSiswa::whereDate('tanggal', $today)
                ->pluck('siswa_id')
                ->unique();

            return Siswa::where('tahun_akademik_id', $tahunId)
                ->where('status', 'aktif')
                ->whereNotIn('id', $sudahAbsenIds)
                ->with('kelas:id,nama')
                ->select('id', 'nama_lengkap', 'kelas_id', 'no_hp_ortu')
                ->orderBy('nama_lengkap')
                ->take(5)
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'nama' => $s->nama_lengkap,
                    'kelas' => $s->kelas->nama ?? '-',
                    'no_ortu' => $s->no_hp_ortu ?? '-',
                    'wa_url' => $s->no_hp_ortu ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $s->no_hp_ortu) : '#',
                ])
                ->toArray();
        });

        // ── Leaderboards (limit, select spesifik) ────────────────────────────
        $palingAwal = AbsensiSiswa::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->where('status', 'hadir')
            ->orderBy('jam_masuk', 'asc')
            ->limit(5)
            ->with(['siswa' => fn($q) => $q->select('id', 'nama_lengkap', 'kelas_id')->with(['kelas' => fn($q2) => $q2->select('id', 'nama')])])
            ->get();

        $palingAkhir = AbsensiSiswa::whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->orderBy('jam_masuk', 'desc')
            ->limit(5)
            ->with(['siswa' => fn($q) => $q->select('id', 'nama_lengkap', 'kelas_id')->with(['kelas' => fn($q2) => $q2->select('id', 'nama')])])
            ->get();

        // ── Pengaturan & Info ─────────────────────────────────────────────
        $pengaturanArr = Cache::remember('superadmin_pengaturan', 300, fn() => Pengaturan::pluck('value', 'key')->toArray());

        // ── Tahun Akademik Aktif ──
        $tahunAkademikAktif = Cache::remember('superadmin_ta_aktif', 300, function() {
            return TahunAkademik::where('is_aktif', true)->first();
        });

        return compact(
            'totalSiswa', 'totalGuru', 'totalStaff', 'totalKelas',
            'totalSiswaWajibAbsen',
            'totalAbsensiHariIni', 'totalIzinPending',
            'hadirCount', 'sakitCount', 'izinCount', 'alphaCount', 'terlambatCount', 'belumAbsen',
            'tingkatKehadiran',
            'chartDays', 'chartHadir', 'chartSakit', 'chartIzin', 'chartAlpha',
            'rataRataHadir', 'hariTerbaik', 'tingkatKetidakhadiran',
            'absensiGuruHariIni', 'absensiStaffHariIni',
            'palingAwal', 'palingAkhir', 'pengaturanArr',
            'tahunAkademikAktif',
            'kehadiranPerKelas', 'monthlyStats', 'metodeAbsensi', 'recentLogs',
            'top5Pelanggaran',
            'belumAbsenPerKelas', 'listBelumAbsen',
            'isWeekend'
        );
    }

    private function operatorData(): array
    {
        $today = Carbon::today();
        $tahunId = session('tahun_akademik_id');

        $top5Pelanggaran = Siswa::where('tahun_akademik_id', $tahunId)
            ->where('status', 'aktif')
            ->withSum(['pelanggaranSiswa' => function($q) use ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            }], 'poin_saat_itu')
            ->get()
            ->filter(function($item) {
                return $item->pelanggaran_siswa_sum_poin_saat_itu > 0;
            })
            ->sortByDesc('pelanggaran_siswa_sum_poin_saat_itu')
            ->take(5);

        return [
            'totalSiswa' => Siswa::where('tahun_akademik_id', $tahunId)->count(),
            'totalGuru'  => Guru::count(),
            'totalStaff' => StaffTataUsaha::count(),
            'totalIzinPending' => IzinSakit::where('status', 'pending')->count(),
            'totalAbsensiHariIni' => AbsensiSiswa::whereDate('tanggal', $today)->count(),
            'kegiatanAktif' => \App\Models\Kegiatan::whereDate('tanggal_pelaksanaan', $today)->count(),
            'top5Pelanggaran' => $top5Pelanggaran,
        ];
    }

    private function waliKelasData($user): array
    {
        $today = Carbon::today();
        
        // Cari data guru berdasarkan user_id
        $guru = Guru::where('user_id', $user->id)->first();
        if (!$guru) {
            return ['has_class' => false];
        }

        // Cari kelas yang wali kelasnya adalah guru ini
        $kelas = Kelas::where('wali_kelas_id', $guru->id)->first();
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
                'rekapBulanan' => ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0],
                'rawAbsensiBulan' => collect(),
                'holidays' => [],
                'month' => (int)now()->month,
                'year' => (int)now()->year,
            ];
        }

        // Hitung total jam mengajar bulan ini
        $jamMengajar = AbsensiGuru::where('guru_id', $guru->id)
                                  ->whereMonth('tanggal', now()->month)
                                  ->whereIn('status', ['hadir', 'terlambat'])
                                  ->count() * 8;

        // Hitung Attendance Streak
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
            } else {
                break;
            }
        }

        // ── Data Kalender & Riwayat ─────────────────────────────────────
        $month = (int) request()->query('month', now()->month);
        $year  = (int) request()->query('year', now()->year);

        // Rekap bulanan
        $statsRaw = AbsensiGuru::where('guru_id', $guru->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $rekapBulanan = [
            'hadir'     => $statsRaw['hadir'] ?? 0,
            'terlambat' => $statsRaw['terlambat'] ?? 0,
            'sakit'     => $statsRaw['sakit'] ?? 0,
            'izin'      => $statsRaw['izin'] ?? 0,
            'alpha'     => $statsRaw['alpha'] ?? 0,
        ];

        // Raw absensi bulanan untuk kalender
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $rawAbsensiBulan = AbsensiGuru::where('guru_id', $guru->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal->toDateString();
            });

        // Hari libur
        $holidaysCollection = Holiday::whereBetween('tanggal', [$startOfMonth, $endOfMonth])->get();
        $holidays = [];
        foreach ($holidaysCollection as $h) {
            $dateStr = $h->tanggal->toDateString();
            if (isset($holidays[$dateStr])) {
                $holidays[$dateStr] .= ' | ' . $h->nama;
            } else {
                $holidays[$dateStr] = $h->nama;
            }
        }

        return [
            'hadir_saya' => AbsensiGuru::where('guru_id', $guru->id)->whereDate('tanggal', $today)->first(),
            'total_absen_bulan_ini' => AbsensiGuru::where('guru_id', $guru->id)->whereMonth('tanggal', now()->month)->count(),
            'total_izin_bulan_ini' => AbsensiGuru::where('guru_id', $guru->id)->whereIn('status', ['sakit', 'izin'])->whereMonth('tanggal', now()->month)->count(),
            'total_jam_mengajar' => $jamMengajar,
            'attendance_streak' => $streakGuru,
            'rekapBulanan' => $rekapBulanan,
            'rawAbsensiBulan' => $rawAbsensiBulan,
            'holidays' => $holidays,
            'month' => $month,
            'year' => $year,
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

    public function switchAnak(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id'
        ]);

        $user = $request->user();
        
        // Verifikasi bahwa anak tersebut memang milik user/ortu ini
        // Kita dukung baik yang langsung ortu_user_id di tabel siswa maupun relasi pivot siswa_ortu
        $isOwnChild = \App\Models\Siswa::where('id', $request->siswa_id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })->exists();

        if ($isOwnChild) {
            session(['active_siswa_id' => $request->siswa_id]);
        }

        return redirect()->back();
    }

    private function orangTuaData($user): array
    {
        $today = Carbon::today();
        
        // Ambil semua anak yang terhubung dengan ortu ini
        $children = Siswa::with(['kelas'])
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->get();

        if ($children->isEmpty()) {
            return [
                'anakList' => collect(),
                'activeAnak' => null,
                'absensiHariIni' => null,
                'rekapBulanan' => [],
                'kalenderAbsensi' => [],
                'month' => now()->month,
                'year' => now()->year
            ];
        }

        // Tentukan anak aktif
        $activeSiswaId = session('active_siswa_id');
        $activeAnak = null;
        
        if ($activeSiswaId) {
            $activeAnak = $children->firstWhere('id', $activeSiswaId);
        }
        
        if (!$activeAnak) {
            $activeAnak = $children->first();
            session(['active_siswa_id' => $activeAnak->id]);
        }

        // Ambil filter bulan/tahun (dari request jika ada, tapi karena dipanggil dari Dashboard index, kita handle request query parameter juga)
        $month = request()->query('month', now()->month);
        $year = request()->query('year', now()->year);

        // Ringkasan Hari Ini untuk Anak Aktif
        $absensiHariIni = AbsensiSiswa::where('siswa_id', $activeAnak->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Rekapitulasi bulanan berdasarkan filter bulan dan tahun untuk anak aktif
        $statsRaw = AbsensiSiswa::where('siswa_id', $activeAnak->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $rekapBulanan = [
            'hadir' => $statsRaw['hadir'] ?? 0,
            'terlambat' => $statsRaw['terlambat'] ?? 0,
            'sakit' => $statsRaw['sakit'] ?? 0,
            'izin' => $statsRaw['izin'] ?? 0,
            'alpha' => $statsRaw['alpha'] ?? 0,
        ];

        // Dapatkan data kalender bulanan untuk anak aktif (agar widget kalender terisi)
        $startOfMonth = Carbon::create($year, $month, 1)->startOfDay();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $rawAbsensiBulan = AbsensiSiswa::where('siswa_id', $activeAnak->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($item) {
                return $item->tanggal->toDateString();
            });

        // Dapatkan daftar hari libur di bulan/tahun terpilih untuk anak aktif
        $holidaysCollection = Holiday::whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->where(function ($query) use ($activeAnak) {
                $query->where(function ($q) {
                    $q->whereNull('tingkat')
                      ->whereNull('kelas_id');
                });
                if ($activeAnak->kelas) {
                    if ($activeAnak->kelas->tingkat) {
                        $query->orWhere('tingkat', $activeAnak->kelas->tingkat);
                    }
                    $query->orWhere('kelas_id', $activeAnak->kelas_id);
                }
            })
            ->get();

        $holidays = [];
        foreach ($holidaysCollection as $h) {
            $dateStr = $h->tanggal->toDateString();
            if (isset($holidays[$dateStr])) {
                $holidays[$dateStr] .= ' | ' . $h->nama;
            } else {
                $holidays[$dateStr] = $h->nama;
            }
        }

        return [
            'anakList' => $children,
            'activeAnak' => $activeAnak,
            'absensiHariIni' => $absensiHariIni,
            'rekapBulanan' => $rekapBulanan,
            'rawAbsensiBulan' => $rawAbsensiBulan,
            'holidays' => $holidays,
            'month' => (int)$month,
            'year' => (int)$year,
        ];
    }

    private function piketData(): array
    {
        $today = Carbon::today();
        $tahunId = session('tahun_akademik_id');

        // Ambil data statistik dasar untuk guru piket hari ini
        $totalSiswa = Siswa::where('tahun_akademik_id', $tahunId)->count();
        
        $totalAbsensiSiswa = AbsensiSiswa::whereDate('tanggal', $today)->count();
        $totalIzinPending = IzinSakit::where('status', 'pending')->count();
        
        $statusHariIni = AbsensiSiswa::whereDate('tanggal', $today)
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $hadirCount = $statusHariIni['hadir'] ?? 0;
        $sakitCount = $statusHariIni['sakit'] ?? 0;
        $izinCount = $statusHariIni['izin'] ?? 0;
        $alphaCount = $statusHariIni['alpha'] ?? 0;
        $terlambatCount = $statusHariIni['terlambat'] ?? 0;
        
        // Log aktivitas piket terakhir
        $recentLogs = ActivityLog::where('action', 'scan')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return compact(
            'totalSiswa',
            'totalAbsensiSiswa',
            'totalIzinPending',
            'hadirCount',
            'sakitCount',
            'izinCount',
            'alphaCount',
            'terlambatCount',
            'recentLogs'
        );
    }

    private function siswaData($user): array
    {
        $siswa = Siswa::where('user_id', $user->id)->first();
        if (!$siswa) {
            return [
                'attendance_streak' => 0,
                'greeting_message' => '',
                'statsHadir' => 0,
                'statsSakit' => 0,
                'statsIzin' => 0,
                'statsAlpha' => 0,
                'totalAbsenBulanIni' => 0,
                'persentaseKehadiran' => 0,
                'riwayatAbsensi' => collect([]),
                'chartDays' => [],
                'chartHadir' => [],
                'chartSakit' => [],
                'chartIzin' => [],
                'chartAlpha' => [],
            ];
        }

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

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $statsHadir = AbsensiSiswa::where('siswa_id', $siswa->id)->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->whereIn('status', ['hadir', 'terlambat'])->count();
        $statsSakit = AbsensiSiswa::where('siswa_id', $siswa->id)->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->where('status', 'sakit')->count();
        $statsIzin = AbsensiSiswa::where('siswa_id', $siswa->id)->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->where('status', 'izin')->count();
        $statsAlpha = AbsensiSiswa::where('siswa_id', $siswa->id)->whereBetween('tanggal', [$startOfMonth, $endOfMonth])->where('status', 'alpha')->count();
        $totalAbsenBulanIni = $statsHadir + $statsSakit + $statsIzin + $statsAlpha;
        $persentaseKehadiran = $totalAbsenBulanIni > 0 ? round(($statsHadir / $totalAbsenBulanIni) * 100) : 0;

        $riwayatAbsensi = AbsensiSiswa::where('siswa_id', $siswa->id)->orderBy('tanggal', 'desc')->orderBy('jam_masuk', 'desc')->limit(5)->get();

        $chartDays = [];
        $chartHadir = [];
        $chartSakit = [];
        $chartIzin = [];
        $chartAlpha = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $label = now()->subDays($i)->locale('id')->translatedFormat('d M');
            $chartDays[] = $label;
            
            $chartHadir[] = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $date)->whereIn('status', ['hadir', 'terlambat'])->count();
            $chartSakit[] = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $date)->where('status', 'sakit')->count();
            $chartIzin[] = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $date)->where('status', 'izin')->count();
            $chartAlpha[] = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $date)->where('status', 'alpha')->count();
        }

        return [
            'attendance_streak' => $streak,
            'greeting_message' => $greeting,
            'statsHadir' => $statsHadir,
            'statsSakit' => $statsSakit,
            'statsIzin' => $statsIzin,
            'statsAlpha' => $statsAlpha,
            'totalAbsenBulanIni' => $totalAbsenBulanIni,
            'persentaseKehadiran' => $persentaseKehadiran,
            'riwayatAbsensi' => $riwayatAbsensi,
            'chartDays' => $chartDays,
            'chartHadir' => $chartHadir,
            'chartSakit' => $chartSakit,
            'chartIzin' => $chartIzin,
            'chartAlpha' => $chartAlpha,
        ];
    }

    public function analytics(Request $request)
    {
        return view('admin.analytics.index');
    }

    public function gamifikasi(Request $request)
    {
        $tahunAkademikList  = TahunAkademik::orderByDesc('is_aktif')->orderByDesc('id')->get();
        $tahunId            = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kelasList          = Kelas::where('tahun_akademik_id', $tahunId)->orderBy('nama')->get(['id', 'nama', 'jurusan_id', 'tahun_akademik_id']);
        $tahunAkademikAktif = TahunAkademik::where('is_aktif', true)->first();

        return view('admin.gamifikasi.index', compact(
            'tahunAkademikList',
            'kelasList',
            'tahunAkademikAktif'
        ));
    }

    /**
     * AJAX: Rekap Gamifikasi — mengembalikan data JSON untuk semua tab rekap.
     */
    public function gamifikasiRekap(Request $request): JsonResponse
    {
        try {
            $kelasId = $request->query('kelas_id');
            $periode = $request->query('periode', 'semua');
            $bulan = $request->query('bulan', now()->format('Y-m'));
            $tahunAkademikId = $request->query('tahun_akademik_id') ?? session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');

            // Format dynamic cache key
            $cacheKey = sprintf(
                'gamifikasi_rekap_%s_%s_%s_%s',
                $kelasId ?? 'all',
                $periode,
                $bulan ?? 'current',
                $tahunAkademikId ?? 'active'
            );

            $data = Cache::remember($cacheKey, 600, function () use ($kelasId, $periode, $bulan, $tahunAkademikId) {
                $filters = array_filter([
                    'kelas_id'          => $kelasId,
                    'periode'           => $periode,
                    'bulan'             => $bulan,
                    'tahun_akademik_id' => $tahunAkademikId,
                ], fn ($v) => $v !== null && $v !== '');

                /** @var GamifikasiRekapService $service */
                $service = app(GamifikasiRekapService::class);

                return [
                    'summary' => $service->getSummaryStats($tahunAkademikId),
                    'siswa'   => $service->getRekapSiswa($filters)->toArray(),
                    'kelas'   => $service->getRekapKelas($filters)->toArray(),
                    'badge'   => $service->getRekapBadge($filters)->toArray(),
                ];
            });

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('GamifikasiRekap error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data rekap gamifikasi.',
            ], 500);
        }
    }

    /**
     * Export CSV Rekap Gamifikasi.
     *
     * Query param: type = siswa | kelas | badge
     */
    public function gamifikasiRekapExport(Request $request)
    {
        try {
            $type    = $request->query('type', 'siswa');
            $filters = array_filter([
                'kelas_id'          => $request->query('kelas_id'),
                'periode'           => $request->query('periode', 'bulan'),
                'bulan'             => $request->query('bulan'),
                'tahun_akademik_id' => $request->query('tahun_akademik_id'),
            ], fn ($v) => $v !== null && $v !== '');

            /** @var GamifikasiRekapService $service */
            $service = app(GamifikasiRekapService::class);

            [$headers, $rows, $filename] = match ($type) {
                'kelas' => $this->buildCsvKelas($service, $filters),
                'badge' => $this->buildCsvBadge($service, $filters),
                default => $this->buildCsvSiswa($service, $filters),
            };

            $callback = function () use ($headers, $rows) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM agar Excel bisa membaca karakter Indonesia
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($handle, $headers);
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            ]);
        } catch (\Exception $e) {
            Log::error('GamifikasiRekapExport error: ' . $e->getMessage());
            abort(500, 'Gagal mengekspor data rekap gamifikasi.');
        }
    }

    // ── CSV Builder Helpers ──────────────────────────────────────────────────

    private function buildCsvSiswa(GamifikasiRekapService $service, array $filters): array
    {
        $data = $service->getRekapSiswa($filters);

        $headers = [
            'No', 'Rank', 'Nama Lengkap', 'NIS', 'Kelas', 'Jurusan',
            'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha',
            'Total Absensi', 'Skor', 'Jumlah Badge', 'Daftar Badge',
        ];

        $rows = $data->map(function ($item, $index) {
            $badgeNames = collect($item['badge_list'])->pluck('name')->implode(', ');
            $kelasNama = is_array($item['kelas']) ? ($item['kelas']['nama'] ?? '-') : $item['kelas'];
            return [
                $index + 1,
                $item['rank'] ?? '-',
                $item['nama_lengkap'],
                $item['nis'],
                $kelasNama,
                $item['jurusan'],
                $item['total_hadir'],
                $item['total_terlambat'],
                $item['total_sakit'],
                $item['total_izin'],
                $item['total_alpha'],
                $item['total_absensi'],
                $item['skor'] ?? '-',
                $item['jumlah_badge'],
                $badgeNames,
            ];
        })->toArray();

        $filename = 'rekap-gamifikasi-siswa-' . now()->format('Ymd-His') . '.csv';
        return [$headers, $rows, $filename];
    }

    private function buildCsvKelas(GamifikasiRekapService $service, array $filters): array
    {
        $data = $service->getRekapKelas($filters);

        $headers = [
            'No', 'Rank', 'Nama Kelas', 'Jurusan', 'Total Siswa',
            'Total Kehadiran', 'Total Hadir', 'Persentase (%)', 'Jumlah Badge Diraih',
        ];

        $rows = $data->map(function ($item, $index) {
            return [
                $index + 1,
                $item['rank'] ?? '-',
                $item['nama'],
                $item['jurusan'],
                $item['total_siswa'],
                $item['total_kehadiran'],
                $item['total_present'],
                $item['percentage'],
                $item['jumlah_badge_diraih'],
            ];
        })->toArray();

        $filename = 'rekap-gamifikasi-kelas-' . now()->format('Ymd-His') . '.csv';
        return [$headers, $rows, $filename];
    }

    private function buildCsvBadge(GamifikasiRekapService $service, array $filters): array
    {
        $data = $service->getRekapBadge($filters);

        $headers = [
            'No', 'Nama Badge', 'Tipe', 'Total Penerima',
            'Nama Siswa', 'Kelas', 'Tanggal Diterima',
        ];

        $rows = [];
        $no   = 1;
        foreach ($data as $badge) {
            if (empty($badge['penerima'])) {
                $rows[] = [
                    $no++,
                    $badge['name'],
                    $badge['badge_type'] ?? '-',
                    $badge['total_penerima'],
                    '-', '-', '-',
                ];
            } else {
                foreach ($badge['penerima'] as $i => $penerima) {
                    $kelasNama = is_array($penerima['kelas']) ? ($penerima['kelas']['nama'] ?? '-') : $penerima['kelas'];
                    $rows[] = [
                        $i === 0 ? $no++ : '',
                        $i === 0 ? $badge['name'] : '',
                        $i === 0 ? ($badge['badge_type'] ?? '-') : '',
                        $i === 0 ? $badge['total_penerima'] : '',
                        $penerima['nama'],
                        $kelasNama,
                        $penerima['earned_at'] ?? '-',
                    ];
                }
            }
        }

        $filename = 'rekap-gamifikasi-badge-' . now()->format('Ymd-His') . '.csv';
        return [$headers, $rows, $filename];
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
            $url = 'https://api.fonnte.com/send';
            $data = [
                'target' => $headmasterNumber,
                'message' => $message,
                'countryCode' => '62',
            ];
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $apiKey,
                ])
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

    /**
     * AJAX: Mendapatkan daftar siswa yang belum absen hari ini.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function siswaBelumAbsen(Request $request): JsonResponse
    {
        try {
            $tahunAkademikId = session('tahun_akademik_id');
            if (!$tahunAkademikId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tahun akademik aktif tidak ditemukan di sesi.'
                ], 400);
            }

            $today = Carbon::today()->toDateString();
            $search = $request->query('search');

            // Subquery untuk mencocokkan absensi hari ini
            $query = Siswa::where('siswa.status', 'aktif')
                ->where('siswa.tahun_akademik_id', $tahunAkademikId)
                ->whereHas('kelas', function ($q) {
                    $q->where('is_aktif_absensi', true);
                })
                ->whereDoesntHave('absensi', function ($q) use ($today) {
                    $q->whereDate('tanggal', $today);
                });

            // Mendukung pencarian berdasarkan nama_lengkap atau nama kelas
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('siswa.nama_lengkap', 'like', "%{$search}%")
                      ->orWhereHas('kelas', function ($qk) use ($search) {
                          $qk->where('nama', 'like', "%{$search}%");
                      });
                });
            }

            // Urutkan berdasarkan nama kelas kemudian nama siswa
            $query->join('kelas', 'siswa.kelas_id', '=', 'kelas.id')
                ->orderBy('kelas.nama', 'asc')
                ->orderBy('siswa.nama_lengkap', 'asc')
                ->select('siswa.*'); // Mencegah nama kolom bentrok saat join

            // Eager load relasi yang diperlukan untuk performa N+1
            $paginator = $query->with([
                'kelas.waliKelas',
                'ortuUser'
            ])->paginate($request->query('per_page', 10));

            $items = collect($paginator->items())->map(function ($siswa) {
                // Tentukan nomor HP wali murid
                // no_hp dari siswa (apabila diisi) atau no_hp_ortu dari siswa, atau dari relasi wali murid user (ortuUser)
                $noHp = $siswa->no_hp_ortu ?: ($siswa->ortuUser?->no_hp ?: $siswa->no_hp);

                // Format nomor HP ke format internasional WhatsApp (misal: 628xxx)
                $formattedPhone = '';
                if (!empty($noHp)) {
                    // Bersihkan karakter non-numerik
                    $cleanPhone = preg_replace('/[^0-9]/', '', $noHp);
                    if (str_starts_with($cleanPhone, '08')) {
                        $formattedPhone = '62' . substr($cleanPhone, 1);
                    } elseif (str_starts_with($cleanPhone, '62')) {
                        $formattedPhone = $cleanPhone;
                    } else {
                        $formattedPhone = $cleanPhone;
                    }
                }

                // Buat pesan template
                $pesan = "Halo Bapak/Ibu, menginfokan bahwa putra/putri Anda {$siswa->nama_lengkap} belum melakukan absensi masuk sekolah hari ini. Terima kasih.";
                $waUrl = !empty($formattedPhone) 
                    ? 'https://wa.me/' . $formattedPhone . '?text=' . rawurlencode($pesan)
                    : '#';

                return [
                    'id' => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'kelas' => $siswa->kelas?->nama ?? '-',
                    'wali_kelas' => $siswa->kelas?->waliKelas?->nama_lengkap ?? '-',
                    'no_hp_ortu' => $noHp ?: '-',
                    'wa_url' => $waUrl
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('siswaBelumAbsen error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data siswa belum absen.'
            ], 500);
        }
    }
}

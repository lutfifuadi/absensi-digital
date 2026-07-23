<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\StaffTataUsaha;
use App\Models\AbsensiStaff;
use App\Support\QrScanLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class PublicQrScanController extends Controller
{
    /**
     * Helper: Ambil pengaturan ter-cache untuk efisiensi.
     */
    private function getCachedSettings()
    {
        return Cache::remember('absensi_settings', now()->addDay(), function () {
            return Pengaturan::whereIn('key', [
                'jam_mulai_absensi',
                'jam_masuk', 
                'jam_batas_masuk', 
                'jam_pulang', 
                'jam_mulai_pulang', 
                'jam_akhir_pulang', 
                'toleransi_terlambat',
                'nama_sekolah',
                'announcement_text'
            ])->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Halaman login password scan QR (publik).
     */
    public function index()
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin_sekolah'])) {
            session(['qr_scan_authenticated' => true]);
            return redirect()->route('public.scan-qr.scan');
        }

        // Jika sudah terautentikasi, langsung ke halaman scan
        if (session('qr_scan_authenticated')) {
            return redirect()->route('public.scan-qr.scan');
        }

        return view('public.scan-qr-login');
    }

    /**
     * Proses verifikasi password scan QR.
     */
    public function auth(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $ip = $request->ip();

        $storedHash = Pengaturan::where('key', 'password_unlock_scan_qr')->value('value');

        if (! $storedHash) {
            QrScanLogger::warning('LOGIN_NO_PASSWORD', [
                'ip'  => $ip,
                'ket' => 'Password scan QR belum diatur oleh admin',
            ]);

            return back()->withErrors(['password' => 'Password scan QR belum diatur oleh admin. Hubungi admin sekolah.']);
        }

        if (! Hash::check($request->password, $storedHash)) {
            QrScanLogger::error('LOGIN_FAILED', [
                'ip'  => $ip,
                'ket' => 'Password salah',
            ]);

            return back()->withErrors(['password' => 'Password salah. Coba lagi.']);
        }

        QrScanLogger::info('LOGIN_SUCCESS', [
            'ip'  => $ip,
            'ket' => 'Sesi scan QR publik berhasil dibuka',
        ]);

        session(['qr_scan_authenticated' => true]);

        return redirect()->route('public.scan-qr.scan');
    }

    /**
     * Halaman scanner kamera QR (butuh sesi authenticated).
     */
    public function scan()
    {
        return view('public.scan-qr-scan');
    }

    /**
     * Proses scan QR — catat absensi siswa (AJAX JSON).
     */
    public function process(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string|max:255',
        ]);

        $ip             = $request->ip();
        $qrCode         = $data['qr_code'];
        
        $settings       = $this->getCachedSettings();

        $jamMulaiAbsensi = !empty($settings['jam_mulai_absensi']) ? $settings['jam_mulai_absensi'] : '06:00';
        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamPulang      = $settings['jam_pulang']      ?? '15:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $currentTime    = now()->format('H:i:s');
        $tanggal        = now()->toDateString();

        // Bandingkan currentTime dengan jamMulaiAbsensi (substring 5 karakter pertama currentTime dengan jamMulaiAbsensi)
        if (substr($currentTime, 0, 5) < $jamMulaiAbsensi) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi belum dibuka. Sesi scan dimulai pukul ' . substr($jamMulaiAbsensi, 0, 5) . ' WIB.',
            ]);
        }

        // 1. Cek apakah ini Siswa
        $siswa = Siswa::with('kelas')->where('qr_code', $qrCode)->first();

        if ($siswa) {
            $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            // --- LOGIKA PULANG ---
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                // Cek Batas Akhir Pulang
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi scan pulang sudah ditutup (Batas: ' . $jamAkhirPulang . ').',
                    ]);
                }

                if ($absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => $siswa->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang . '.',
                        'siswa'   => [
                            'nama'  => $siswa->nama_lengkap,
                            'kelas' => $siswa->kelas?->nama ?? '-',
                            'jam'   => $absensi->jam_pulang,
                        ],
                    ]);
                }

                $absensi->update(['jam_pulang' => $currentTime]);
                Cache::forget('live_board_leaderboard_data');

                QrScanLogger::info('QR_SCAN_PULANG_SUCCESS', [
                    'ip'      => $ip,
                    'siswa'   => $siswa->nama_lengkap,
                    'kelas'   => $siswa->kelas?->nama ?? '-',
                    'jam'     => $currentTime,
                    'tanggal' => $tanggal,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Selamat beristirahat! Jam pulang ' . $siswa->nama_lengkap . ' tercatat.',
                    'siswa'   => [
                        'nama'  => $siswa->nama_lengkap,
                        'kelas' => $siswa->kelas?->nama ?? '-',
                        'jam'   => $currentTime,
                    ],
                ]);
            }

            // --- LOGIKA MASUK ---
            if ($absensi) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'message' => $siswa->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    'siswa'   => [
                        'nama'  => $siswa->nama_lengkap,
                        'kelas' => $siswa->kelas?->nama ?? '-',
                        'jam'   => $absensi->jam_masuk,
                    ],
                ]);
            }

            // Cek Batas Akhir Masuk
            if ($currentTime > $jamBatasMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi scan masuk sudah ditutup (Batas: ' . $jamBatasMasuk . '). Silakan lapor ke Guru Piket.',
                ]);
            }

            // Tentukan status (hadir vs terlambat)
            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
            if (in_array($activeJenjang, ['SD/MI', 'SMP/MTs']) && $status === 'terlambat') {
                $status = 'hadir';
            }

            try {
                AbsensiSiswa::create([
                    'siswa_id'    => $siswa->id,
                    'kelas_id'    => $siswa->kelas_id,
                    'tanggal'     => $tanggal,
                    'jam_masuk'   => $currentTime,
                    'status'      => $status,
                    'keterangan'  => 'Scan QR publik oleh guru piket',
                    'guru_id'     => null,
                    'metode'      => 'qr',
                ]);
                Cache::forget('live_board_leaderboard_data');
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => $siswa->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    ]);
                }
                throw $e;
            }

            QrScanLogger::info('QR_SCAN_SUCCESS', [
                'ip'      => $ip,
                'siswa'   => $siswa->nama_lengkap,
                'kelas'   => $siswa->kelas?->nama ?? '-',
                'jam'     => $currentTime,
                'tanggal' => $tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => $status === 'terlambat' ? 'Absensi tercatat (TERLAMBAT).' : 'Berhasil! Absensi siswa tercatat.',
                'siswa'   => [
                    'nama'  => $siswa->nama_lengkap,
                    'kelas' => $siswa->kelas?->nama ?? '-',
                    'jam'   => $currentTime,
                ],
            ]);
        }

        // 2. Jika bukan siswa, cek apakah Guru (bisa scan QR Unik, QR NIP, atau NIP mentah)
        $guru = Guru::where('qr_code', $qrCode)->orWhere('qr_code_nip', $qrCode)->orWhere('nip', $qrCode)->first();
        if ($guru) {
            $absensi = AbsensiGuru::where('guru_id', $guru->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            // LOGIKA PULANG GURU
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }
                
                if ($absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Guru: ' . $guru->nama_lengkap . ' sudah melakukan scan pulang.',
                    ]);
                }

                $absensi->update(['jam_pulang' => $currentTime]);
                Cache::forget('live_board_leaderboard_data');

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil! Jam pulang Guru ' . $guru->nama_lengkap . ' tercatat.',
                    'siswa'   => ['nama' => $guru->nama_lengkap, 'kelas' => 'GURU', 'jam' => $currentTime],
                ]);
            }

            if ($absensi) {
                return response()->json(['success' => false, 'already' => true, 'message' => 'Guru sudah tercatat hadir.',]);
            }

            if ($currentTime > $jamBatasMasuk) {
                return response()->json(['success' => false, 'message' => 'Sesi scan masuk guru sudah ditutup.']);
            }

            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            try {
                AbsensiGuru::create([
                    'guru_id'    => $guru->id,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => $currentTime,
                    'status'     => $status,
                    'keterangan' => 'Scan QR publik oleh guru piket',
                    'metode'     => 'qr',
                ]);
                Cache::forget('live_board_leaderboard_data');
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Guru ' . $guru->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    ]);
                }
                throw $e;
            }

            QrScanLogger::info('QR_SCAN_GURU_SUCCESS', [
                'ip'    => $ip,
                'guru'  => $guru->nama_lengkap,
                'jam'   => $currentTime,
                'tanggal' => $tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil! Absensi guru tercatat.',
                'siswa'   => [
                    'nama'   => $guru->nama_lengkap,
                    'kelas'  => 'GURU',
                    'jam'    => $currentTime,
                    'status' => $status,
                ],
            ]);
        }

        // 3. Jika bukan siswa & guru, cek apakah Staff Tata Usaha
        $staff = StaffTataUsaha::where('qr_code', $qrCode)->orWhere('qr_code_nip', $qrCode)->orWhere('nip', $qrCode)->first();
        if ($staff) {
            $absensi = AbsensiStaff::where('staff_id', $staff->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            // LOGIKA PULANG STAFF
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }
                
                if ($absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Staff: ' . $staff->nama_lengkap . ' sudah melakukan scan pulang.',
                    ]);
                }

                $absensi->update(['jam_pulang' => $currentTime]);
                Cache::forget('live_board_leaderboard_data');

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil! Jam pulang Staff ' . $staff->nama_lengkap . ' tercatat.',
                    'siswa'   => ['nama' => $staff->nama_lengkap, 'kelas' => 'STAFF TU', 'jam' => $currentTime],
                ]);
            }

            if ($absensi) {
                return response()->json(['success' => false, 'already' => true, 'message' => 'Staff sudah tercatat hadir.',]);
            }

            if ($currentTime > $jamBatasMasuk) {
                return response()->json(['success' => false, 'message' => 'Sesi scan masuk staff sudah ditutup.']);
            }

            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            try {
                AbsensiStaff::create([
                    'staff_id'   => $staff->id,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => $currentTime,
                    'status'     => $status,
                    'keterangan' => 'Scan QR publik oleh guru piket',
                    'metode'     => 'qr',
                ]);
                Cache::forget('live_board_leaderboard_data');
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Staff ' . $staff->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    ]);
                }
                throw $e;
            }

            QrScanLogger::info('QR_SCAN_STAFF_SUCCESS', [
                'ip'    => $ip,
                'staff' => $staff->nama_lengkap,
                'jam'   => $currentTime,
                'tanggal' => $tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil! Absensi staff tercatat.',
                'siswa'   => [
                    'nama'   => $staff->nama_lengkap,
                    'kelas'  => 'STAFF TU',
                    'jam'    => $currentTime,
                    'status' => $status,
                ],
            ]);
        }

        // 4. Tidak ditemukan
        QrScanLogger::error('QR_NOT_FOUND', [
            'ip'      => $ip,
            'qr_code' => substr($qrCode, 0, 20) . (strlen($qrCode) > 20 ? '...' : ''),
            'ket'     => 'QR code tidak dikenal (Siswa/Guru/Staff)',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'QR code tidak dikenal. Pastikan QR code siswa, guru, atau staff valid.',
        ]);
    }

    /**
     * Endpoint statistik real-time untuk halaman scan QR.
     * Data dari DB — bukan client-side counter.
     */
    public function scanStats()
    {
        $today = today()->toDateString();

        // 1. Statistik Siswa
        $siswaStats = AbsensiSiswa::whereDate('tanggal', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('hadir','terlambat') THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat
            ")
            ->first();

        // 2. Statistik Guru
        $guruStats = AbsensiGuru::whereDate('tanggal', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('hadir','terlambat') THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat
            ")
            ->first();

        // 3. Recent logs — Siswa
        $siswaLogs = AbsensiSiswa::with('siswa.kelas')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'nama'   => $a->siswa->nama_lengkap ?? '-',
                'kelas'  => $a->siswa->kelas->nama ?? '-',
                'jam'    => $a->jam_masuk,
                'status' => $a->status,
                'tipe'   => 'siswa',
            ]);

        // 4. Recent logs — Guru
        $guruLogs = AbsensiGuru::with('guru')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(fn($a) => [
                'nama'   => $a->guru->nama_lengkap ?? '-',
                'kelas'  => 'GURU',
                'jam'    => $a->jam_masuk,
                'status' => $a->status,
                'tipe'   => 'guru',
            ]);

        // 5. Gabung & sort
        $recentLogs = collect($siswaLogs)->concat($guruLogs)
            ->sortByDesc('jam')
            ->take(10)
            ->values();

        return response()->json([
            'stats' => [
                'siswa_hadir'     => (int) ($siswaStats->hadir ?? 0),
                'siswa_terlambat' => (int) ($siswaStats->terlambat ?? 0),
                'siswa_total'     => (int) ($siswaStats->total ?? 0),
                'guru_hadir'      => (int) ($guruStats->hadir ?? 0),
                'guru_terlambat'  => (int) ($guruStats->terlambat ?? 0),
                'guru_total'      => (int) ($guruStats->total ?? 0),
            ],
            'recent_logs' => $recentLogs,
        ]);
    }

    /**
     * Cari siswa/guru berdasarkan NIS/NIP/nama — untuk input manual di scan QR.
     */
    public function searchSiswaGuru(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|min:2|max:50',
        ]);

        $q = $data['q'];

        $siswa = Siswa::where('nis', 'like', "%{$q}%")
            ->orWhere('nama_lengkap', 'like', "%{$q}%")
            ->with('kelas')
            ->take(10)
            ->get()
            ->map(fn($s) => [
                'id'    => $s->id,
                'nis'   => $s->nis,
                'nama'  => $s->nama_lengkap,
                'kelas' => $s->kelas->nama ?? '-',
                'foto'  => $s->foto,
                'tipe'  => 'siswa',
            ]);

        $guru = Guru::where('nip', 'like', "%{$q}%")
            ->orWhere('nama_lengkap', 'like', "%{$q}%")
            ->take(10)
            ->get()
            ->map(fn($g) => [
                'id'    => $g->id,
                'nis'   => $g->nip,
                'nama'  => $g->nama_lengkap,
                'kelas' => 'GURU',
                'foto'  => null,
                'tipe'  => 'guru',
            ]);

        $results = collect($siswa)->concat($guru)->take(10)->values();

        return response()->json(['results' => $results]);
    }

    /**
     * Logout dari sesi scan QR publik.
     */
    public function logout(Request $request)
    {
        QrScanLogger::info('LOGOUT', [
            'ip'  => $request->ip(),
            'ket' => 'Sesi scan QR publik ditutup',
        ]);

        $request->session()->forget('qr_scan_authenticated');

        return redirect()->route('public.scan-qr.index')
            ->with('success', 'Berhasil keluar dari sesi scan QR.');
    }

    /**
     * Halaman Live Board Publik — Leaderboard + QR Scanner (tanpa login).
     */
    public function liveBoard(Request $request)
    {
        $mode = $request->query('mode', 'otomatis');
        if (!in_array($mode, ['masuk', 'pulang', 'otomatis'])) {
            $mode = 'otomatis';
        }

        $settings     = $this->getCachedSettings();
        $namaSekolah  = $settings['nama_sekolah']        ?? 'Madrasah Aliyah';
        $jamMasukCfg  = $settings['jam_masuk']          ?? '07:00';
        $toleransi    = (int)($settings['toleransi_terlambat'] ?? 15);
        $announcement = $settings['announcement_text']   ?? null;

        [$leaderboardAwal, $leaderboardTerbaru, $stats] = $this->getLeaderboardData($mode);
        $totalKapasitasSiswa = Cache::remember('total_civitas_count', now()->addMinutes(10), function () {
            return Siswa::count() + Guru::count() + StaffTataUsaha::count();
        });

        return view('public.live-board', compact(
            'namaSekolah', 'jamMasukCfg', 'toleransi', 'announcement',
            'leaderboardAwal', 'leaderboardTerbaru', 'stats', 'totalKapasitasSiswa', 'mode'
        ));
    }

    /**
     * AJAX endpoint — proses scan QR dari halaman Live Board (publik).
     */
    public function liveBoardScan(Request $request)
    {
        // Validate/read request parameter 'mode'
        $mode = $request->input('mode', 'otomatis');
        if (!in_array($mode, ['masuk', 'pulang', 'otomatis'])) {
            $mode = 'otomatis';
        }

        $data   = $request->validate(['qr_code' => 'required|string|max:255']);
        $qrCode = $data['qr_code'];
        $ip     = $request->ip();

        $settings = $this->getCachedSettings();

        $jamMulaiAbsensi = !empty($settings['jam_mulai_absensi']) ? $settings['jam_mulai_absensi'] : '06:00';
        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamPulang      = $settings['jam_pulang']      ?? '15:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $currentTime    = now()->format('H:i:s');
        $tanggal        = now()->toDateString();

        // Bandingkan currentTime dengan jamMulaiAbsensi (substring 5 karakter pertama currentTime dengan jamMulaiAbsensi)
        if (substr($currentTime, 0, 5) < $jamMulaiAbsensi) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi belum dibuka. Sesi scan dimulai pukul ' . substr($jamMulaiAbsensi, 0, 5) . ' WIB.',
            ]);
        }

        // Helper untuk invalidate leaderboard cache
        $forgetCache = function() {
            Cache::forget('live_board_leaderboard_data_otomatis');
            Cache::forget('live_board_leaderboard_data_masuk');
            Cache::forget('live_board_leaderboard_data_pulang');
        };

        // 1. Cek Siswa
        $siswa = Siswa::with('kelas')->where('qr_code', $qrCode)->first();
        if ($siswa) {
            $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $tanggal)->first();

            // PULANG MODE
            if ($mode === 'pulang') {
                if ($absensi && $absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => $siswa->nama_lengkap . ' sudah scan pulang pada jam ' . $absensi->jam_pulang . '.',
                    ]);
                }

                if ($absensi) {
                    $absensi->update(['jam_pulang' => $currentTime]);
                } else {
                    AbsensiSiswa::create([
                        'siswa_id'   => $siswa->id,
                        'kelas_id'   => $siswa->kelas_id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => null,
                        'jam_pulang' => $currentTime,
                        'status'     => 'hadir',
                        'keterangan' => 'Scan QR Live Board Pulang',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Hati-hati di jalan! Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $siswa->nama_lengkap, 'kelas' => $siswa->kelas?->nama ?? '-', 'jam' => $currentTime],
                ]);
            }

            // MASUK MODE
            if ($mode === 'masuk') {
                if ($absensi && $absensi->jam_masuk) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => $siswa->nama_lengkap . ' sudah tercatat hadir pada jam ' . $absensi->jam_masuk . '.',
                    ]);
                }

                $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
                $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

                if ($absensi) {
                    $absensi->update([
                        'jam_masuk' => $currentTime,
                        'status'    => $status,
                    ]);
                } else {
                    AbsensiSiswa::create([
                        'siswa_id'   => $siswa->id,
                        'kelas_id'   => $siswa->kelas_id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => $currentTime,
                        'status'     => $status,
                        'keterangan' => 'Scan QR Live Board Masuk',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Absensi berhasil dicatat!',
                    'siswa'   => ['nama' => $siswa->nama_lengkap, 'kelas' => $siswa->kelas?->nama ?? '-', 'jam' => $currentTime, 'status' => $status],
                ]);
            }

            // OTOMATIS MODE (Original Logic)
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }

                if ($absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => $siswa->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang . '.',
                    ]);
                }

                $absensi->update(['jam_pulang' => $currentTime]);
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Hati-hati di jalan! Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $siswa->nama_lengkap, 'kelas' => $siswa->kelas?->nama ?? '-', 'jam' => $currentTime],
                ]);
            }

            if ($absensi) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'message' => $siswa->nama_lengkap . ' sudah tercatat hadir pada jam ' . $absensi->jam_masuk . '.',
                ]);
            }

            if ($currentTime > $jamBatasMasuk) {
                return response()->json(['success' => false, 'message' => 'Sesi scan masuk guru sudah ditutup.']);
            }

            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            try {
                AbsensiSiswa::create([
                    'siswa_id'   => $siswa->id,
                    'kelas_id'   => $siswa->kelas_id,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => $currentTime,
                    'status'     => $status,
                    'keterangan' => 'Scan QR Live Board',
                    'metode'     => 'qr',
                ]);
                $forgetCache();
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return response()->json(['success' => false, 'already' => true, 'message' => 'Sudah tercatat hadir.']);
                }
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat!',
                'siswa'   => ['nama' => $siswa->nama_lengkap, 'kelas' => $siswa->kelas?->nama ?? '-', 'jam' => $currentTime, 'status' => $status],
            ]);
        }

        // 2. Cek Guru (bisa scan QR Unik, QR NIP, atau NIP mentah)
        $guru = Guru::where('qr_code', $qrCode)->orWhere('qr_code_nip', $qrCode)->orWhere('nip', $qrCode)->first();
        if ($guru) {
            $absensi = AbsensiGuru::where('guru_id', $guru->id)->whereDate('tanggal', $tanggal)->first();

            // PULANG MODE GURU
            if ($mode === 'pulang') {
                if ($absensi && $absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Guru: ' . $guru->nama_lengkap . ' sudah scan pulang.',
                    ]);
                }

                if ($absensi) {
                    $absensi->update(['jam_pulang' => $currentTime]);
                } else {
                    AbsensiGuru::create([
                        'guru_id'    => $guru->id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => null,
                        'jam_pulang' => $currentTime,
                        'status'     => 'hadir',
                        'keterangan' => 'Scan QR Live Board Pulang',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Selamat beristirahat! Jam pulang Guru ' . $guru->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $guru->nama_lengkap, 'kelas' => 'GURU', 'jam' => $currentTime],
                ]);
            }

            // MASUK MODE GURU
            if ($mode === 'masuk') {
                if ($absensi && $absensi->jam_masuk) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Guru ' . $guru->nama_lengkap . ' sudah tercatat hadir.',
                    ]);
                }

                $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
                $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

                if ($absensi) {
                    $absensi->update([
                        'jam_masuk' => $currentTime,
                        'status'    => $status,
                    ]);
                } else {
                    AbsensiGuru::create([
                        'guru_id'    => $guru->id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => $currentTime,
                        'status'     => $status,
                        'keterangan' => 'Scan QR Live Board Masuk',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Absensi Guru berhasil dicatat!',
                    'siswa'   => ['nama' => $guru->nama_lengkap, 'kelas' => 'GURU', 'jam' => $currentTime],
                ]);
            }

            // OTOMATIS GURU MODE
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }
                if ($absensi->jam_pulang) {
                    return response()->json(['success' => false, 'already' => true, 'message' => 'Sudah scan pulang.',]);
                }
                $absensi->update(['jam_pulang' => $currentTime]);
                $forgetCache();
                return response()->json([
                    'success' => true,
                    'message' => 'Selamat beristirahat! Jam pulang Guru ' . $guru->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $guru->nama_lengkap, 'kelas' => 'GURU', 'jam' => $currentTime],
                ]);
            }

            if ($absensi) {
                return response()->json(['success' => false, 'already' => true, 'message' => 'Guru sudah tercatat hadir.']);
            }

            if ($currentTime > $jamBatasMasuk) {
                return response()->json(['success' => false, 'message' => 'Sesi scan masuk guru sudah ditutup.']);
            }

            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            try {
                AbsensiGuru::create([
                    'guru_id'    => $guru->id,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => $currentTime,
                    'status'     => $status,
                    'keterangan' => 'Scan QR Live Board',
                    'metode'     => 'qr',
                ]);
                $forgetCache();
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    QrScanLogger::warning('QR_SCAN_GURU_DUPLICATE', [
                        'ip'    => $ip,
                        'guru'  => $guru->nama_lengkap,
                        'jam'   => $currentTime,
                        'tanggal' => $tanggal,
                    ]);

                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Guru ' . $guru->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    ]);
                }
                throw $e;
            }

            QrScanLogger::info('QR_SCAN_GURU_SUCCESS', [
                'ip'    => $ip,
                'guru'  => $guru->nama_lengkap,
                'jam'   => $currentTime,
                'tanggal' => $tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absensi Guru berhasil dicatat!',
                'siswa'   => ['nama' => $guru->nama_lengkap, 'kelas' => 'GURU', 'jam' => $currentTime],
            ]);
        }

        // 3. Cek Staff Tata Usaha (bisa scan QR Unik, QR NIP, atau NIP mentah)
        $staff = StaffTataUsaha::where('qr_code', $qrCode)->orWhere('qr_code_nip', $qrCode)->orWhere('nip', $qrCode)->first();
        if ($staff) {
            $absensi = AbsensiStaff::where('staff_id', $staff->id)->whereDate('tanggal', $tanggal)->first();

            // PULANG MODE STAFF
            if ($mode === 'pulang') {
                if ($absensi && $absensi->jam_pulang) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Staff: ' . $staff->nama_lengkap . ' sudah scan pulang.',
                    ]);
                }

                if ($absensi) {
                    $absensi->update(['jam_pulang' => $currentTime]);
                } else {
                    AbsensiStaff::create([
                        'staff_id'   => $staff->id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => null,
                        'jam_pulang' => $currentTime,
                        'status'     => 'hadir',
                        'keterangan' => 'Scan QR Live Board Pulang',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Selamat beristirahat! Jam pulang Staff ' . $staff->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $staff->nama_lengkap, 'kelas' => 'STAFF TU', 'jam' => $currentTime],
                ]);
            }

            // MASUK MODE STAFF
            if ($mode === 'masuk') {
                if ($absensi && $absensi->jam_masuk) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Staff ' . $staff->nama_lengkap . ' sudah tercatat hadir.',
                    ]);
                }

                $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
                $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

                if ($absensi) {
                    $absensi->update([
                        'jam_masuk' => $currentTime,
                        'status'    => $status,
                    ]);
                } else {
                    AbsensiStaff::create([
                        'staff_id'   => $staff->id,
                        'tanggal'    => $tanggal,
                        'jam_masuk'  => $currentTime,
                        'status'     => $status,
                        'keterangan' => 'Scan QR Live Board Masuk',
                        'metode'     => 'qr',
                    ]);
                }
                $forgetCache();

                return response()->json([
                    'success' => true,
                    'message' => 'Absensi Staff berhasil dicatat!',
                    'siswa'   => ['nama' => $staff->nama_lengkap, 'kelas' => 'STAFF TU', 'jam' => $currentTime],
                ]);
            }

            // OTOMATIS STAFF MODE
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }
                if ($absensi->jam_pulang) {
                    return response()->json(['success' => false, 'already' => true, 'message' => 'Sudah scan pulang.',]);
                }
                $absensi->update(['jam_pulang' => $currentTime]);
                $forgetCache();
                return response()->json([
                    'success' => true,
                    'message' => 'Selamat beristirahat! Jam pulang Staff ' . $staff->nama_lengkap . ' berhasil dicatat.',
                    'siswa'   => ['nama' => $staff->nama_lengkap, 'kelas' => 'STAFF TU', 'jam' => $currentTime],
                ]);
            }

            if ($absensi) {
                return response()->json(['success' => false, 'already' => true, 'message' => 'Staff sudah tercatat hadir.']);
            }

            if ($currentTime > $jamBatasMasuk) {
                return response()->json(['success' => false, 'message' => 'Sesi scan masuk staff sudah ditutup.']);
            }

            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i:s');
            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

            try {
                AbsensiStaff::create([
                    'staff_id'   => $staff->id,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => $currentTime,
                    'status'     => $status,
                    'keterangan' => 'Scan QR Live Board',
                    'metode'     => 'qr',
                ]);
                $forgetCache();
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    return response()->json([
                        'success' => false,
                        'already' => true,
                        'message' => 'Staff ' . $staff->nama_lengkap . ' sudah tercatat hadir hari ini.',
                    ]);
                }
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Absensi Staff berhasil dicatat!',
                'siswa'   => ['nama' => $staff->nama_lengkap, 'kelas' => 'STAFF TU', 'jam' => $currentTime],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'QR code tidak dikenal.']);
    }


    /**
     * AJAX endpoint — kembalikan data leaderboard terbaru (JSON).
     */
    public function liveBoardLeaderboard(Request $request)
    {
        $mode = $request->input('mode', 'otomatis');
        if (!in_array($mode, ['masuk', 'pulang', 'otomatis'])) {
            $mode = 'otomatis';
        }

        [$awal, $terbaru, $stats] = $this->getLeaderboardData($mode);

        $mapRow = fn($obj, $rank) => [
            'rank'  => $rank,
            'nama'  => $obj->nama,
            'kelas' => $obj->kelas,
            'jam'   => $obj->jam,
            'status'=> $obj->status,
        ];

        return response()->json([
            'awal'    => collect($awal)->values()->map(fn($r, $i) => $mapRow($r, $i + 1)),
            'terbaru' => collect($terbaru)->values()->map(fn($r, $i) => $mapRow($r, $i + 1)),
            'stats'   => $stats,
        ]);
    }

    /** Helper: ambil data leaderboard + stats hari ini. */
    private function getLeaderboardData(string $mode = 'otomatis'): array
    {
        return Cache::remember('live_board_leaderboard_data_' . $mode, 10, function () use ($mode) {
            $today = today()->toDateString();

            // Tentukan field jam berdasarkan mode
            // Jika mode = pulang, kita filter whereNotNull('jam_pulang')
            // Jika mode = masuk, kita filter whereNotNull('jam_masuk')
            // Jika mode = otomatis, default ke whereNotNull('jam_masuk')
            $siswaQuery = AbsensiSiswa::with('siswa.kelas')->whereDate('tanggal', $today);
            $guruQuery = AbsensiGuru::with('guru')->whereDate('tanggal', $today);
            $staffQuery = AbsensiStaff::with('staff')->whereDate('tanggal', $today);

            if ($mode === 'pulang') {
                $siswaQuery->whereNotNull('jam_pulang');
                $guruQuery->whereNotNull('jam_pulang');
                $staffQuery->whereNotNull('jam_pulang');
            } else {
                $siswaQuery->whereNotNull('jam_masuk')->whereIn('status', ['hadir', 'terlambat']);
                $guruQuery->whereNotNull('jam_masuk')->whereIn('status', ['hadir', 'terlambat']);
                $staffQuery->whereNotNull('jam_masuk')->whereIn('status', ['hadir', 'terlambat']);
            }

            $absensiSiswa = $siswaQuery->get();
            $absensiGuru = $guruQuery->get();
            $absensiStaff = $staffQuery->get();

            // 3. Gabungkan dan Map ke struktur seragam
            $all = collect();

            foreach ($absensiSiswa as $as) {
                $jamVal = $mode === 'pulang' ? $as->jam_pulang : $as->jam_masuk;
                $all->push((object)[
                    'nama'   => $as->siswa->nama_lengkap ?? '-',
                    'kelas'  => $as->siswa->kelas->nama  ?? '-',
                    'jam'    => $jamVal,
                    'status' => $as->status,
                    'type'   => 'siswa',
                    'original' => $as
                ]);
            }

            foreach ($absensiGuru as $ag) {
                $jamVal = $mode === 'pulang' ? $ag->jam_pulang : $ag->jam_masuk;
                $all->push((object)[
                    'nama'   => $ag->guru->nama_lengkap ?? '-',
                    'kelas'  => 'GURU',
                    'jam'    => $jamVal,
                    'status' => $ag->status,
                    'type'   => 'guru',
                    'original' => $ag
                ]);
            }

            foreach ($absensiStaff as $ast) {
                $jamVal = $mode === 'pulang' ? $ast->jam_pulang : $ast->jam_masuk;
                $all->push((object)[
                    'nama'   => $ast->staff->nama_lengkap ?? '-',
                    'kelas'  => 'STAFF TU',
                    'jam'    => $jamVal,
                    'status' => $ast->status,
                    'type'   => 'staff',
                    'original' => $ast
                ]);
            }

            // 4. Sortir berdasarkan Jam ASC untuk awal, DESC untuk terbaru
            $sortedAwal = $all->sortBy('jam')->values();
            $sortedTerbaru = $all->sortByDesc('jam')->values();

            $awal    = $sortedAwal->slice(0, 10);
            $terbaru = $sortedTerbaru->slice(0, 10);

            $totalCivitas = Cache::remember('total_civitas_count', now()->addMinutes(10), function () {
                return Siswa::count() + Guru::count() + StaffTataUsaha::count();
            });

            if ($mode === 'pulang') {
                $checkedOutSiswa = AbsensiSiswa::whereDate('tanggal', $today)->whereNotNull('jam_pulang')->count();
                $checkedOutGuru  = AbsensiGuru::whereDate('tanggal', $today)->whereNotNull('jam_pulang')->count();
                $checkedOutStaff = AbsensiStaff::whereDate('tanggal', $today)->whereNotNull('jam_pulang')->count();

                $checkedOutTotal = $checkedOutSiswa + $checkedOutGuru + $checkedOutStaff;
                $remaining = max(0, $totalCivitas - $checkedOutTotal);

                $stats = [
                    'hadir'     => $checkedOutTotal,
                    'sakit'     => 0,
                    'izin'      => 0,
                    'alpha'     => 0,
                    'terlambat' => 0,
                    'total'     => $totalCivitas,
                    'pulang'    => $checkedOutTotal,
                    'remaining' => $remaining,
                ];
            } else {
                $siswaStats = AbsensiSiswa::whereDate('tanggal', $today)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $guruStats = AbsensiGuru::whereDate('tanggal', $today)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $staffStats = AbsensiStaff::whereDate('tanggal', $today)
                    ->selectRaw('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $hadirCount = ($siswaStats['hadir'] ?? 0) + ($siswaStats['terlambat'] ?? 0)
                            + ($guruStats['hadir'] ?? 0)  + ($guruStats['terlambat'] ?? 0)
                            + ($staffStats['hadir'] ?? 0) + ($staffStats['terlambat'] ?? 0);

                $sakitCount = ($siswaStats['sakit'] ?? 0) + ($guruStats['sakit'] ?? 0) + ($staffStats['sakit'] ?? 0);
                $izinCount  = ($siswaStats['izin'] ?? 0)  + ($guruStats['izin'] ?? 0)  + ($staffStats['izin'] ?? 0);
                $alphaCount = ($siswaStats['alpha'] ?? 0) + ($guruStats['alpha'] ?? 0) + ($staffStats['alpha'] ?? 0);
                $terlambatCount = ($siswaStats['terlambat'] ?? 0) + ($guruStats['terlambat'] ?? 0) + ($staffStats['terlambat'] ?? 0);

                $stats = [
                    'hadir'    => $hadirCount,
                    'sakit'    => $sakitCount,
                    'izin'     => $izinCount,
                    'alpha'    => $alphaCount,
                    'terlambat'=> $terlambatCount,
                    'total'    => $totalCivitas,
                ];
            }

            return [$awal, $terbaru, $stats];
        });
    }
}

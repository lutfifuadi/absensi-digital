<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\Guru;
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

        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamPulang      = $settings['jam_pulang']      ?? '15:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $currentTime    = now()->format('H:i:s');
        $tanggal        = now()->toDateString();

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

        // 2. Jika bukan siswa, cek apakah Guru
        $guru = Guru::where('qr_code', $qrCode)->first();
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

        // 3. Tidak ditemukan
        QrScanLogger::error('QR_NOT_FOUND', [
            'ip'      => $ip,
            'qr_code' => substr($qrCode, 0, 20) . (strlen($qrCode) > 20 ? '...' : ''),
            'ket'     => 'QR code tidak dikenal (Siswa/Guru)',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'QR code tidak dikenal. Pastikan QR code siswa atau guru valid.',
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
    public function liveBoard()
    {
        $settings     = $this->getCachedSettings();
        $namaSekolah  = $settings['nama_sekolah']        ?? 'Madrasah Aliyah';
        $jamMasukCfg  = $settings['jam_masuk']          ?? '07:00';
        $toleransi    = (int)($settings['toleransi_terlambat'] ?? 15);
        $announcement = $settings['announcement_text']   ?? null;

        [$leaderboardAwal, $leaderboardTerbaru, $stats] = $this->getLeaderboardData();
        $totalKapasitasSiswa = Siswa::count();

        return view('public.live-board', compact(
            'namaSekolah', 'jamMasukCfg', 'toleransi', 'announcement',
            'leaderboardAwal', 'leaderboardTerbaru', 'stats', 'totalKapasitasSiswa'
        ));
    }

    /**
     * AJAX endpoint — proses scan QR dari halaman Live Board (publik).
     */
    public function liveBoardScan(Request $request)
    {
        // Reuse logic yang sama dengan process() tapi tanpa middleware qr.scan.auth
        $data   = $request->validate(['qr_code' => 'required|string|max:255']);
        $qrCode = $data['qr_code'];
        $ip     = $request->ip();

        $settings = $this->getCachedSettings();

        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamPulang      = $settings['jam_pulang']      ?? '15:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $currentTime    = now()->format('H:i:s');
        $tanggal        = now()->toDateString();

        // 1. Cek Siswa
        $siswa = Siswa::with('kelas')->where('qr_code', $qrCode)->first();
        if ($siswa) {
            $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)->whereDate('tanggal', $tanggal)->first();

            // --- LOGIKA PULANG ---
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

                return response()->json([
                    'success' => true,
                    'message' => 'Sudah waktunya pulang! Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.',
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

            // Cek Batas Akhir Masuk
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

        // 2. Cek Guru
        $guru = Guru::where('qr_code', $qrCode)->first();
        if ($guru) {
            $absensi = AbsensiGuru::where('guru_id', $guru->id)->whereDate('tanggal', $tanggal)->first();

            // LOGIKA PULANG GURU
            if ($absensi && $currentTime >= $jamMulaiPulang) {
                if ($currentTime > $jamAkhirPulang) {
                    return response()->json(['success' => false, 'message' => 'Sesi scan pulang sudah ditutup.']);
                }
                if ($absensi->jam_pulang) {
                    return response()->json(['success' => false, 'already' => true, 'message' => 'Sudah scan pulang.',]);
                }
                $absensi->update(['jam_pulang' => $currentTime]);
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
        return response()->json(['success' => false, 'message' => 'QR code tidak dikenal.']);
    }


    /**
     * AJAX endpoint — kembalikan data leaderboard terbaru (JSON).
     */
    public function liveBoardLeaderboard()
    {
        [$awal, $terbaru, $stats] = $this->getLeaderboardData();

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
    private function getLeaderboardData(): array
    {
        $today = today()->toDateString();

        // 1. Ambil Absensi Siswa
        $absensiSiswa = AbsensiSiswa::with('siswa.kelas')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->get();

        // 2. Ambil Absensi Guru
        $absensiGuru = AbsensiGuru::with('guru')
            ->whereDate('tanggal', $today)
            ->whereNotNull('jam_masuk')
            ->whereIn('status', ['hadir', 'terlambat'])
            ->get();

        // 3. Gabungkan dan Map ke struktur seragam
        $all = collect();

        foreach ($absensiSiswa as $as) {
            $all->push((object)[
                'nama'   => $as->siswa->nama_lengkap ?? '-',
                'kelas'  => $as->siswa->kelas->nama  ?? '-',
                'jam'    => $as->jam_masuk,
                'status' => $as->status,
                'type'   => 'siswa',
                'original' => $as
            ]);
        }

        foreach ($absensiGuru as $ag) {
            $all->push((object)[
                'nama'   => $ag->guru->nama_lengkap ?? '-',
                'kelas'  => 'GURU',
                'jam'    => $ag->jam_masuk,
                'status' => $ag->status,
                'type'   => 'guru',
                'original' => $ag
            ]);
        }

        // 4. Sortir berdasarkan Jam Masuk ASC untuk awal, DESC untuk terbaru
        $sortedAwal = $all->sortBy('jam')->values();
        $sortedTerbaru = $all->sortByDesc('jam')->values();

        $awal    = $sortedAwal->slice(0, 10);
        $terbaru = $sortedTerbaru->slice(0, 10);



        $rawStats = AbsensiSiswa::whereDate('tanggal', $today)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'hadir'    => ($rawStats['hadir']    ?? 0) + ($rawStats['terlambat'] ?? 0),
            'sakit'    => $rawStats['sakit']    ?? 0,
            'izin'     => $rawStats['izin']     ?? 0,
            'alpha'    => $rawStats['alpha']    ?? 0,
            'terlambat'=> $rawStats['terlambat']?? 0,
            'total'    => array_sum($rawStats),
        ];

        return [$awal, $terbaru, $stats];
    }
}

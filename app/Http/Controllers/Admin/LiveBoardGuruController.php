<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Helpers\JadwalAbsensiHelper;
use App\Services\KehadiranGuruService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LiveBoardGuruController extends Controller
{
    public function __construct(
        private KehadiranGuruService $kehadiranGuruService
    ) {
    }
    /**
     * Tampilkan halaman Live Board Absensi Guru (TV Display Ready).
     */
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $mode  = $request->query('mode', 'otomatis');
        if (!in_array($mode, ['masuk', 'pulang', 'otomatis'])) {
            $mode = 'otomatis';
        }

        $liveData = $this->getLiveData($today, $mode);

        return view('admin.absensi-guru.live-board', $liveData);
    }

    /**
     * Response JSON untuk AJAX Polling Realtime di Live Board.
     */
    public function data(Request $request)
    {
        $today = now()->toDateString();
        $mode  = $request->query('mode', 'otomatis');
        if (!in_array($mode, ['masuk', 'pulang', 'otomatis'])) {
            $mode = 'otomatis';
        }

        $liveData = $this->getLiveData($today, $mode);

        return response()->json([
            'success'     => true,
            'server_time' => now()->format('H:i:s'),
            'data'        => $liveData,
            'awal'        => $liveData['leaderboardAwal'],
            'terbaru'     => $liveData['leaderboardTerbaru'],
            'stats'       => $liveData['stats'],
        ]);
    }

    /**
     * AJAX endpoint — proses scan QR / NIP dari halaman Live Board Guru.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);
        $qrCode = trim($request->qr_code);
        $mode   = $request->input('mode', 'otomatis');

        $guru = Guru::where('qr_code', $qrCode)
            ->orWhere('nip', $qrCode)
            ->orWhere('nuptk', $qrCode)
            ->first();

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code / NIP Guru tidak dikenal.'
            ], 404);
        }

        $tanggal        = now()->toDateString();
        $currentTime    = now()->format('H:i:s');
        $jamMulaiPulang = Pengaturan::where('key', 'jam_mulai_pulang')->value('value') ?? '14:00';
        $jamMasukCfg    = Pengaturan::where('key', 'jam_masuk')->value('value') ?? '07:00';
        $toleransi      = (int) (Pengaturan::where('key', 'toleransi_terlambat')->value('value') ?? 15);

        $isPulang = false;
        if ($mode === 'pulang') {
            $isPulang = true;
        } elseif ($mode === 'masuk') {
            $isPulang = false;
        } else {
            $isPulang = (now()->format('H:i') >= $jamMulaiPulang);
        }

        $absensi = AbsensiGuru::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $namaGuru = $guru->nama_lengkap ?? $guru->nama;

        if ($isPulang) {
            if (!$absensi) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'guru'    => ['nama' => $namaGuru, 'jabatan' => $guru->jabatan ?? 'Guru', 'jam' => substr($currentTime, 0, 5)],
                    'message' => 'Bapak/Ibu ' . $namaGuru . ' belum melakukan scan masuk hari ini.'
                ], 422);
            }

            if ($absensi->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'guru'    => ['nama' => $namaGuru, 'jabatan' => $guru->jabatan ?? 'Guru', 'jam' => substr($absensi->jam_pulang, 0, 5)],
                    'message' => 'Bapak/Ibu ' . $namaGuru . ' sudah presensi pulang pada jam ' . substr($absensi->jam_pulang, 0, 5)
                ]);
            }

            $absensi->update([
                'jam_pulang' => $currentTime,
                'metode'     => 'Live Board QR/NIP Scanner',
            ]);

            return response()->json([
                'success' => true,
                'guru'    => ['nama' => $namaGuru, 'jabatan' => $guru->jabatan ?? 'Guru', 'jam' => substr($currentTime, 0, 5)],
                'message' => 'Berhasil! Scan pulang Bapak/Ibu ' . $namaGuru . ' tercatat (' . substr($currentTime, 0, 5) . ').'
            ]);
        } else {
            if ($absensi && $absensi->jam_masuk) {
                return response()->json([
                    'success' => false,
                    'already' => true,
                    'guru'    => ['nama' => $namaGuru, 'jabatan' => $guru->jabatan ?? 'Guru', 'jam' => substr($absensi->jam_masuk, 0, 5)],
                    'message' => 'Bapak/Ibu ' . $namaGuru . ' sudah presensi masuk pada jam ' . substr($absensi->jam_masuk, 0, 5)
                ]);
            }

            $jamMasukSetting = Carbon::createFromTimeString($jamMasukCfg);
            $jamGuru         = Carbon::createFromTimeString($currentTime);
            $selisih         = (int) $jamMasukSetting->diffInMinutes($jamGuru, false);
            $status          = $selisih > $toleransi ? 'terlambat' : 'hadir';

            if (!$absensi) {
                AbsensiGuru::create([
                    'guru_id'   => $guru->id,
                    'tanggal'   => $tanggal,
                    'jam_masuk' => $currentTime,
                    'status'    => $status,
                    'metode'    => 'Live Board QR/NIP Scanner',
                ]);
            } else {
                $absensi->update([
                    'jam_masuk' => $currentTime,
                    'status'    => $status,
                    'metode'    => 'Live Board QR/NIP Scanner',
                ]);
            }

            return response()->json([
                'success' => true,
                'guru'    => ['nama' => $namaGuru, 'jabatan' => $guru->jabatan ?? 'Guru', 'jam' => substr($currentTime, 0, 5)],
                'message' => 'Berhasil! Presensi masuk Bapak/Ibu ' . $namaGuru . ' tercatat (' . substr($currentTime, 0, 5) . ').'
            ]);
        }
    }

    /**
     * Helper privat untuk mengkalkulasi data realtime absensi guru hari ini.
     */
    private function getLiveData(string $today, string $mode = 'otomatis'): array
    {
        $allGuru = Guru::orderBy('nama_lengkap', 'asc')->get();
        $totalGuru = $allGuru->count();

        $absensiToday = AbsensiGuru::with('guru')
            ->whereDate('tanggal', $today)
            ->get();

        $absensiMap = $absensiToday->keyBy('guru_id');

        $hadirCount     = 0;
        $terlambatCount = 0;
        $izinSakitCount = 0;
        $sakitCount     = 0;
        $izinCount      = 0;
        $alphaCount     = 0;
        $pulangCount    = 0;
        $belumAbsenCount = 0;

        // ── PRD-007: counter part time (informasi tambahan untuk UI) ──
        $pakaiStatusKepegawaian = feature('fitur_status_kepegawaian');
        $partTimeCount            = 0;
        $partTimeBelumScanCount   = 0;
        $partTimeSesuaiJadwalCount = 0;
        $partTimeTidakHadirCount  = 0;
        $partTimeTanpaSlotCount   = 0;
        $belumMonitorPartTimeCount = 0;

        $guruListFormatted = [];

        foreach ($allGuru as $guru) {
            $absensi  = $absensiMap->get($guru->id);
            $status   = 'belum_absen';
            $jamMasuk = null;
            $jamPulang = null;
            $metode   = null;

            $tipeKepegawaian = $guru->tipe_kepegawaian ?? 'full_time';
            $isPartTime = $pakaiStatusKepegawaian && $tipeKepegawaian === 'part_time';

            if ($isPartTime) {
                $partTimeCount++;
            }

            // PRD-007 (BUG-1): status part time SELALU ditentukan dari monitoring slot
            // agar konsisten dengan Dashboard. Info scan (jam masuk/pulang) tetap
            // ditampilkan sebagai info tambahan, bukan penentu status.
            $statusSlot = null;
            if ($isPartTime) {
                $statusSlot = $this->kehadiranGuruService
                    ->isGuruDianggapHadirHariIni($guru->id, $today);
            }

            if ($absensi) {
                $jamMasuk  = $absensi->jam_masuk;
                $jamPulang = $absensi->jam_pulang;
                $metode    = $absensi->metode;

                if ($isPartTime) {
                    // Status mengikuti slot; bila tanpa slot hari ini → status ikut scan
                    // (bukti hadir/izin resmi), tidak pernah alpha.
                    $status = $this->applyPartTimeSlotStatus(
                        $statusSlot,
                        $hadirCount,
                        $terlambatCount,
                        $partTimeTidakHadirCount,
                        $partTimeSesuaiJadwalCount,
                        $belumMonitorPartTimeCount,
                        $belumAbsenCount
                    );

                    if ($status === 'part_time') {
                        $rawStatus = strtolower($absensi->status);
                        if ($rawStatus === 'hadir') {
                            $hadirCount++;
                            $status = 'hadir';
                        } elseif ($rawStatus === 'terlambat') {
                            $terlambatCount++;
                            $status = 'terlambat';
                        } elseif (in_array($rawStatus, ['izin', 'sakit', 'dinas', 'cuti'])) {
                            $izinSakitCount++;
                            if ($rawStatus === 'sakit') {
                                $sakitCount++;
                            } else {
                                $izinCount++;
                            }
                            $status = $rawStatus;
                        } else {
                            // Record alpha lama pada part time: jangan tampil alpha,
                            // scan tetap dianggap hadir (BR-06).
                            $hadirCount++;
                            $status = 'hadir';
                        }
                    }
                } else {
                    // Full time: perilaku lama persis (status dari absensi_guru).
                    $rawStatus = strtolower($absensi->status);
                    if ($rawStatus === 'hadir') {
                        $hadirCount++;
                        $status = 'hadir';
                    } elseif ($rawStatus === 'terlambat') {
                        $terlambatCount++;
                        $status = 'terlambat';
                    } elseif (in_array($rawStatus, ['izin', 'sakit', 'dinas', 'cuti'])) {
                        $izinSakitCount++;
                        if ($rawStatus === 'sakit') {
                            $sakitCount++;
                        } else {
                            $izinCount++;
                        }
                        $status = $rawStatus;
                    } else {
                        $alphaCount++;
                        $status = 'alpha';
                    }
                }

                if ($jamPulang) {
                    $pulangCount++;
                }
            } else {
                // Tanpa scan
                if ($isPartTime) {
                    // PRD-007 (F-4): part time tanpa absensi_guru TIDAK dihitung alpha.
                    $partTimeBelumScanCount++;
                    $status = $this->applyPartTimeSlotStatus(
                        $statusSlot,
                        $hadirCount,
                        $terlambatCount,
                        $partTimeTidakHadirCount,
                        $partTimeSesuaiJadwalCount,
                        $belumMonitorPartTimeCount,
                        $belumAbsenCount
                    );

                    if ($status === 'part_time') {
                        // BR-05: tanpa slot terjadwal hari ini → tidak dihitung hadir/alpha
                        $partTimeTanpaSlotCount++;
                    }
                } else {
                    $alphaCount++;
                    $belumAbsenCount++;
                }
            }

            $fotoUrl = asset('assets/img/avatars/1.png');
            if ($guru->foto) {
                if (filter_var($guru->foto, FILTER_VALIDATE_URL)) {
                    $fotoUrl = $guru->foto;
                } elseif (file_exists(public_path('storage/' . $guru->foto))) {
                    $fotoUrl = asset('storage/' . $guru->foto);
                } elseif (file_exists(public_path('uploads/guru/' . $guru->foto))) {
                    $fotoUrl = asset('uploads/guru/' . $guru->foto);
                }
            }

            $guruListFormatted[] = [
                'id'             => $guru->id,
                'nip'            => $guru->nip ?? '—',
                'nama'           => $guru->nama_lengkap ?? $guru->nama,
                'jabatan'        => $guru->jabatan ?? 'Guru Pengajar',
                'mata_pelajaran' => $guru->mata_pelajaran ?? '—',
                'foto'           => $fotoUrl,
                'tipe_kepegawaian' => $tipeKepegawaian,
                'status'         => $status,
                'jam_masuk'      => $jamMasuk ? substr($jamMasuk, 0, 5) : null,
                'jam_pulang'     => $jamPulang ? substr($jamPulang, 0, 5) : null,
                'metode'         => $metode,
            ];
        }

        // Leaderboard Awal Guru (10 Tercepat)
        if ($mode === 'pulang') {
            $leaderboardAwal = $absensiToday->whereNotNull('jam_pulang')
                ->sortBy('jam_pulang')
                ->take(10)
                ->values()
                ->map(function ($rec, $idx) {
                    $guru = $rec->guru;
                    return [
                        'rank'    => $idx + 1,
                        'nama'    => $guru?->nama_lengkap ?? $guru?->nama ?? 'Guru',
                        'jabatan' => $guru?->jabatan ?? 'Guru Pengajar',
                        'jam'     => $rec->jam_pulang,
                        'status'  => 'pulang',
                        'tipe_kepegawaian' => $guru?->tipe_kepegawaian ?? 'full_time',
                    ];
                })->all();
        } else {
            $leaderboardAwal = $absensiToday->whereNotNull('jam_masuk')
                ->sortBy('jam_masuk')
                ->take(10)
                ->values()
                ->map(function ($rec, $idx) {
                    $guru = $rec->guru;
                    return [
                        'rank'    => $idx + 1,
                        'nama'    => $guru?->nama_lengkap ?? $guru?->nama ?? 'Guru',
                        'jabatan' => $guru?->jabatan ?? 'Guru Pengajar',
                        'jam'     => $rec->jam_masuk,
                        'status'  => strtolower($rec->status),
                        'tipe_kepegawaian' => $guru?->tipe_kepegawaian ?? 'full_time',
                    ];
                })->all();
        }

        // Leaderboard Terbaru / Recent Scans
        $leaderboardTerbaru = $absensiToday->sortByDesc('updated_at')
            ->take(10)
            ->values()
            ->map(function ($rec, $idx) {
                $guru = $rec->guru;
                $jamVal = $rec->jam_pulang ?? $rec->jam_masuk ?? $rec->updated_at->format('H:i:s');
                return [
                    'rank'    => $idx + 1,
                    'nama'    => $guru?->nama_lengkap ?? $guru?->nama ?? 'Guru',
                    'jabatan' => $guru?->jabatan ?? 'Guru Pengajar',
                    'jam'     => $jamVal,
                    'status'  => $rec->jam_pulang ? 'pulang' : strtolower($rec->status),
                    'tipe_kepegawaian' => $guru?->tipe_kepegawaian ?? 'full_time',
                ];
            })->all();

        // Recent Scans formatted for Feed
        $recentScans = $absensiToday->sortByDesc('updated_at')
            ->take(10)
            ->values()
            ->map(function ($rec) {
                $guru = $rec->guru;
                $fotoUrl = asset('assets/img/avatars/1.png');
                if ($guru && $guru->foto) {
                    if (filter_var($guru->foto, FILTER_VALIDATE_URL)) {
                        $fotoUrl = $guru->foto;
                    } elseif (file_exists(public_path('storage/' . $guru->foto))) {
                        $fotoUrl = asset('storage/' . $guru->foto);
                    } elseif (file_exists(public_path('uploads/guru/' . $guru->foto))) {
                        $fotoUrl = asset('uploads/guru/' . $guru->foto);
                    }
                }

                $waktuDisplay = $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : ($rec->jam_masuk ? substr($rec->jam_masuk, 0, 5) : $rec->updated_at->format('H:i'));
                $tipeScan = $rec->jam_pulang ? 'Pulang' : 'Masuk';

                return [
                    'id'         => $rec->id,
                    'guru_id'    => $rec->guru_id,
                    'nama'       => $guru?->nama_lengkap ?? $guru?->nama ?? 'Guru',
                    'jabatan'    => $guru?->jabatan ?? 'Guru Pengajar',
                    'foto'       => $fotoUrl,
                    'status'     => strtolower($rec->status),
                    'tipe_kepegawaian' => $guru?->tipe_kepegawaian ?? 'full_time',
                    'waktu'      => $waktuDisplay,
                    'tipe_scan'  => $tipeScan,
                    'metode'     => $rec->metode ?? 'QR Code',
                    'updated_at' => $rec->updated_at->timestamp,
                ];
            })->all();

        $globalJadwal = JadwalAbsensiHelper::getJadwalForKelas(0);

        $announcement = Pengaturan::where('key', 'running_text_guru')->value('value') 
            ?? Pengaturan::where('key', 'announcement_text')->value('value') 
            ?? 'Selamat Datang di Live Board Absensi Guru — Mohon untuk selalu melakukan presensi tepat waktu. Utamakan kedisiplinan demi mewujudkan keteladanan bagi para siswa.';

        $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
        $logoUrl     = Pengaturan::where('key', 'logo_url')->value('value');
        if (!$logoUrl) {
            $logoLocal = Pengaturan::where('key', 'logo_sekolah')->value('value');
            if ($logoLocal) {
                if (filter_var($logoLocal, FILTER_VALIDATE_URL)) {
                    $logoUrl = $logoLocal;
                } elseif (file_exists(public_path('uploads/logo/' . $logoLocal))) {
                    $logoUrl = asset('uploads/logo/' . $logoLocal);
                } elseif (file_exists(public_path('storage/' . $logoLocal))) {
                    $logoUrl = asset('storage/' . $logoLocal);
                } elseif (file_exists(public_path($logoLocal))) {
                    $logoUrl = asset($logoLocal);
                } else {
                    $logoUrl = asset('uploads/logo/' . $logoLocal);
                }
            }
        }
        $logoSekolah = $logoUrl;

        $jamMasukCfg    = Pengaturan::where('key', 'jam_masuk')->value('value') ?? '07:00';
        $jamMulaiAbsensi = Pengaturan::where('key', 'jam_mulai_absensi')->value('value') ?? '06:00';
        $toleransi      = (int) (Pengaturan::where('key', 'toleransi_terlambat')->value('value') ?? 15);

        $tahunAktif = \App\Models\TahunAkademik::where('is_aktif', true)->first();
        $sloganSekolah = Pengaturan::where('key', 'slogan_sekolah')->value('value') 
            ?? Pengaturan::where('key', 'motto_sekolah')->value('value') 
            ?? Pengaturan::where('key', 'sub_judul_sekolah')->value('value') 
            ?? 'Berakhlak Mulia, Disiplin, & Berprestasi';

        $kotaRaw = Pengaturan::where('key', 'kota_penerbitan')->value('value') 
            ?? Pengaturan::where('key', 'kota')->value('value') 
            ?? Pengaturan::where('key', 'kabupaten')->value('value') 
            ?? 'Bandung';
        $kotaSekolah = strtoupper(str_replace(['Kota ', 'Kabupaten ', 'KOTA ', 'KABUPATEN '], '', $kotaRaw));
        
        $zonaWaktu = Pengaturan::where('key', 'zona_waktu')->value('value') ?? 'Asia/Jakarta (WIB)';
        $zoneAbbr = 'WIB';
        $utcOffset = 'UTC+7';
        $ianaTimezone = 'Asia/Jakarta';

        if (str_contains($zonaWaktu, 'WITA') || str_contains($zonaWaktu, 'Makassar')) {
            $zoneAbbr = 'WITA';
            $utcOffset = 'UTC+8';
            $ianaTimezone = 'Asia/Makassar';
        } elseif (str_contains($zonaWaktu, 'WIT') || str_contains($zonaWaktu, 'Jayapura')) {
            $zoneAbbr = 'WIT';
            $utcOffset = 'UTC+9';
            $ianaTimezone = 'Asia/Jayapura';
        }

        // PRD-007 (F-4): 'remaining' = guru yang masih diharapkan absen/monitoring.
        // Part time tanpa slot terjadwal hari ini tidak dihitung (BR-05).
        $remainingCount = ($mode === 'pulang')
            ? max(0, $totalGuru - $pulangCount)
            : $belumAbsenCount;

        return [
            'mode'               => $mode,
            'namaSekolah'        => $namaSekolah,
            'logoSekolah'        => $logoSekolah,
            'tahunAktif'         => $tahunAktif,
            'sloganSekolah'      => $sloganSekolah,
            'kotaSekolah'        => $kotaSekolah,
            'zoneAbbr'           => $zoneAbbr,
            'utcOffset'          => $utcOffset,
            'ianaTimezone'       => $ianaTimezone,
            'totalKapasitasGuru' => $totalGuru,
            'jamMasukCfg'        => $jamMasukCfg,
            'jamMulaiAbsensi'    => $jamMulaiAbsensi,
            'toleransi'          => $toleransi,
            'stats' => [
                'total'      => $totalGuru,
                'hadir'      => $hadirCount,
                'terlambat'  => $terlambatCount,
                'izin_sakit' => $izinSakitCount,
                'sakit'      => $sakitCount,
                'izin'       => $izinCount,
                'alpha'      => $alphaCount,
                'belum_absen'=> $belumAbsenCount,
                'pulang'     => $pulangCount,
                'remaining'  => $remainingCount,
                // ── PRD-007: statistik part time (informasi tambahan untuk UI) ──
                'part_time'                  => $partTimeCount,
                'part_time_belum_scan'       => $partTimeBelumScanCount,
                'part_time_sesuai_jadwal'    => $partTimeSesuaiJadwalCount,
                'part_time_tidak_hadir'      => $partTimeTidakHadirCount,
                'part_time_tanpa_slot'       => $partTimeTanpaSlotCount,
                'part_time_belum_dimonitor'  => $belumMonitorPartTimeCount,
            ],
            'leaderboardAwal'    => $leaderboardAwal,
            'leaderboardTerbaru' => $leaderboardTerbaru,
            'recentScans'        => $recentScans,
            'guruList'           => $guruListFormatted,
            'globalJadwal'       => $globalJadwal,
            'announcement'       => $announcement,
            'namaSekolah'        => $namaSekolah,
            'logoSekolah'        => $logoSekolah,
            'tanggalIndo'        => now()->locale('id')->isoFormat('D MMMM YYYY'),
            'hariIndo'           => ucfirst(now()->locale('id')->isoFormat('dddd')),
        ];
    }

    /**
     * Terapkan status part time berbasis monitoring slot ke counter live board.
     *
     * PRD-007 (BUG-1): status part time SELALU mengikuti slot (konsisten dengan
     * Dashboard). Mengembalikan label status:
     *   - 'hadir'           — seluruh slot hadir
     *   - 'terlambat'       — seluruh slot terlambat
     *   - 'tidak_hadir'     — seluruh slot tidak_hadir (bukan alpha)
     *   - 'sesuai_jadwal'   — sebagian slot hadir/terlambat
     *   - 'belum_dimonitor' — ada slot, belum dicatat piket (masuk counter belum_absen)
     *   - 'part_time'       — tanpa slot terjadwal hari ini (tidak dihitung, BR-05)
     *
     * @param string|null $statusSlot hasil isGuruDianggapHadirHariIni()
     */
    private function applyPartTimeSlotStatus(
        ?string $statusSlot,
        int &$hadirCount,
        int &$terlambatCount,
        int &$partTimeTidakHadirCount,
        int &$partTimeSesuaiJadwalCount,
        int &$belumMonitorPartTimeCount,
        int &$belumAbsenCount
    ): string {
        if ($statusSlot === null) {
            return 'part_time';
        }

        if ($statusSlot === 'hadir') {
            $hadirCount++;
            return 'hadir';
        }

        if ($statusSlot === 'terlambat') {
            $terlambatCount++;
            return 'terlambat';
        }

        if ($statusSlot === 'tidak_hadir') {
            $partTimeTidakHadirCount++;
            return 'tidak_hadir';
        }

        if ($statusSlot === 'sesuai_jadwal') {
            $partTimeSesuaiJadwalCount++;
            return 'sesuai_jadwal';
        }

        // belum_dimonitor
        $belumMonitorPartTimeCount++;
        $belumAbsenCount++;
        return 'belum_dimonitor';
    }
}

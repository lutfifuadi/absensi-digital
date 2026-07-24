<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\IzinSakit;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\TahunAkademik;
use App\Models\AttendanceAnalytics;
use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\ClassLeaderboard;
use App\Models\StudentLeaderboard;
use App\Models\OfflineQueue;
use App\Models\AuthorizedDevice;
use App\Models\AbsensiKegiatan;
use App\Models\ActivityNotificationQueue;
use App\Models\ReminderSettings;
use App\Models\Pengaturan;
use App\Services\WhatsAppService;
use App\Services\EkskulAbsensiService;
use App\Models\EkskulAbsensi;
use App\Models\EkskulAnggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class InnovationController extends Controller
{
    public function getNotificationTemplates()
    {
        $templates = NotificationTemplate::all();
        return response()->json(['data' => $templates]);
    }

    public function updateNotificationTemplate(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'content' => 'required|string',
        ]);

        $template = NotificationTemplate::findOrFail($id);
        $template->update($request->only(['type', 'content']));

        return response()->json(['success' => true, 'data' => $template]);
    }

    public function getAttendanceAnalytics(Request $request)
    {
        $date = $request->get('date', now()->toDateString());
        $kelasId = $request->get('kelas_id');

        $query = AttendanceAnalytics::with(['kelas', 'tahunAkademik'])
            ->where('date', $date);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $analytics = $query->get();
        return response()->json(['data' => $analytics]);
    }

    public function analyzeAttendance(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'date' => 'required|date',
        ]);

        $kelasId = $request->kelas_id;
        $date = $request->date;
        $taId = TahunAkademik::where('is_aktif', true)->first()?->id;

        $absensi = AbsensiSiswa::where('kelas_id', $kelasId)
            ->where('tanggal', $date)
            ->get();

        // LOGIKA PENILAIAN BARU DAN UPDATE KE DATABASE
        // Urutkan siswa berdasarkan waktu masuk untuk Early Bird (Filter yang punya jam masuk)
        // Kita butuh mengambil ulang absensi dengan collection methods agar benar-benar iterasi record yg sama
        $absensiHadir = $absensi->whereIn('status', ['Hadir', 'hadir', 'Terlambat', 'terlambat'])
            ->filter(fn($item) => !empty($item->jam_masuk))
            ->sortBy('jam_masuk')
            ->values();
        
        $earlyBirdThreshold = '06:00';

        foreach ($absensi as $absen) {
            $poin = 0;
            $isEarlyBird = false;

            // 1. Standarisasi Poin Dasar
            $statusLower = strtolower($absen->status);
            if ($statusLower === 'hadir') {
                $poin += 10;
            } elseif ($statusLower === 'terlambat') {
                $poin += 5;
            } elseif ($statusLower === 'sakit' || $statusLower === 'izin') {
                $poin += 2;
            } elseif ($statusLower === 'alpha') {
                $poin -= 10;
            }

            // 2. Bonus Early Bird
            if (in_array($statusLower, ['hadir', 'terlambat'])) {
                $index = false;
                foreach ($absensiHadir as $i => $item) {
                    if ($item->id === $absen->id) {
                        $index = $i;
                        break;
                    }
                }
                
                // Tambah jika index 0-4 (5 pertama) ATAU jika jam masuk <= 06:00
                $jamMasuk = $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : null;
                
                // Strict comparison with boolean return for Early Bird conditions
                $isTop5 = ($index !== false && $index < 5);
                $isBeforeTime = ($jamMasuk && $jamMasuk <= $earlyBirdThreshold);
                
                if ($isTop5 || $isBeforeTime) {
                    $poin += 5;
                    $isEarlyBird = true;
                }
            }

            // 3. Bonus Konsistensi (Streak)
            $gamificationStat = \App\Models\StudentGamificationStat::firstOrCreate(
                ['siswa_id' => $absen->siswa_id]
            );

            if ($statusLower === 'hadir' || $statusLower === 'terlambat') {
                // Asumsi hadir = tepat waktu
                $gamificationStat->current_streak += 1;
                
                if ($gamificationStat->current_streak > $gamificationStat->longest_streak) {
                    $gamificationStat->longest_streak = $gamificationStat->current_streak;
                }
                
                // Jika streak >= 5, beri bonus (misal multiplier, atau bonus poin fixed)
                if ($gamificationStat->current_streak >= 5) {
                    $poin += 5; // Extra 5 point for streak
                }
            } else {
                // Streak putus jika tidak hadir tepat waktu
                $gamificationStat->current_streak = 0;
            }

            $gamificationStat->last_attendance_date = clone now();
            $gamificationStat->save();

            // Update record absensi dengan poin yang dihitung akhir
            $absen->points_earned = $poin;
            $absen->is_early_bird = $isEarlyBird;
            
            \App\Models\AbsensiSiswa::where('id', $absen->id)
                ->update([
                    'points_earned' => $poin,
                    'is_early_bird' => $isEarlyBird
                ]);
        }

        // Hitung ulang statistik kelas setelah poin ditambahkan
        // Panggil ulang dari DB untuk mengambil nilai poin yang baru terupdate
        $absensi = AbsensiSiswa::where('kelas_id', $kelasId)
            ->where('tanggal', $date)
            ->get();
        $total = $absensi->count();
        $hadir = $absensi->whereIn('status', ['Hadir', 'hadir'])->count();
        $terlambat = $absensi->whereIn('status', ['Terlambat', 'terlambat'])->count();
        $sakit = $absensi->whereIn('status', ['Sakit', 'sakit'])->count();
        $izin = $absensi->whereIn('status', ['Izin', 'izin'])->count();
        $alpha = $total - $hadir - $terlambat - $sakit - $izin;

        $percentageKehadiran = $total > 0 ? ($hadir / $total) * 100 : 0;
        $percentageKeterlambatan = $total > 0 ? ($terlambat / $total) * 100 : 0;

        $alertTriggered = false;
        if ($percentageKehadiran < 75) {
            $alertTriggered = true;
        }

        $analytics = AttendanceAnalytics::updateOrCreate(
            ['kelas_id' => $kelasId, 'tahun_akademik_id' => $taId, 'date' => $date],
            [
                'total_students' => $total,
                'hadir_tepat_waktu' => $hadir,
                'terlambat' => $terlambat,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'persentase_kehadiran' => $percentageKehadiran,
                'persentase_keterlambatan' => $percentageKeterlambatan,
                'alert_triggered' => $alertTriggered,
                'alert_note' => $alertTriggered ? 'Tingkat kehadiran di bawah 75%' : null,
            ]
        );

        return response()->json(['success' => true, 'data' => $analytics]);
    }

    public function getBadges()
    {
        $badges = Badge::where('is_active', true)->get();
        return response()->json(['data' => $badges]);
    }

    public function storeBadge(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'description' => 'required|string',
            'badge_type' => 'required|in:individual,class',
            'requirement_days' => 'required|integer|min:1',
            'requirement_type' => 'required|in:consecutive,total',
        ]);

        $badge = Badge::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'description' => $request->description,
            'badge_type' => $request->badge_type,
            'requirement_days' => $request->requirement_days,
            'requirement_type' => $request->requirement_type,
            'is_active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $badge]);
    }

    public function getStudentBadgesHistory()
    {
        $history = StudentBadge::with(['siswa.kelas', 'badge'])
            ->latest('earned_at')
            ->get();

        $totalEarnedStudents = StudentBadge::distinct('siswa_id')->count('siswa_id');

        return response()->json([
            'success' => true,
            'data' => $history,
            'total_earned_students' => $totalEarnedStudents
        ]);
    }

    public function assignBadge(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'badge_id' => 'required|exists:badges,id',
        ]);

        $studentBadge = StudentBadge::firstOrCreate([
            'siswa_id' => $request->siswa_id,
            'badge_id' => $request->badge_id,
        ], ['earned_at' => now()]);

        return response()->json(['success' => true, 'data' => $studentBadge]);
    }

    public function getLeaderboard(Request $request)
    {
        $taId = $request->get('tahun_akademik_id', 
            TahunAkademik::where('is_aktif', true)->first()?->id);

        $leaderboard = ClassLeaderboard::with('kelas')
            ->where('tahun_akademik_id', $taId)
            ->orderBy('rank')
            ->get();

        return response()->json(['data' => $leaderboard]);
    }

    public function calculateLeaderboard(Request $request)
    {
        $taId = $request->get('tahun_akademik_id',
            TahunAkademik::where('is_aktif', true)->first()?->id);

        $kelasList = Kelas::all();

        $results = [];
        foreach ($kelasList as $kelas) {
            $absensi = AbsensiSiswa::where('kelas_id', $kelas->id)
                ->where('tahun_akademik_id', $taId)
                ->get();

            $total = $absensi->count();
            $present = $absensi->whereIn('status', ['Hadir', 'Terlambat'])->count();
            $percentage = $total > 0 ? ($present / $total) * 100 : 0;

            $results[] = [
                'kelas_id' => $kelas->id,
                'tahun_akademik_id' => $taId,
                'total_attendance' => $total,
                'total_present' => $present,
                'percentage' => $percentage,
            ];
        }

        usort($results, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        ClassLeaderboard::where('tahun_akademik_id', $taId)->delete();
        foreach ($results as $index => $result) {
            ClassLeaderboard::create([
                'kelas_id' => $result['kelas_id'],
                'tahun_akademik_id' => $result['tahun_akademik_id'],
                'rank' => $index + 1,
                'total_attendance' => $result['total_attendance'],
                'total_present' => $result['total_present'],
                'percentage' => $result['percentage'],
                'calculated_at' => now(),
            ]);
        }

        // --- PEMBERIAN BADGE OTOMATIS (Siswa Terajin / Kehadiran) ---
        $badges = Badge::where('is_active', true)->where('badge_type', 'individual')->get();
        $siswas = Siswa::all();

        foreach ($badges as $badge) {
            $reqDays = $badge->requirement_days;
            $reqType = $badge->requirement_type;

            foreach ($siswas as $siswa) {
                $qualified = false;

                if ($reqType === 'total') {
                    // Berdasarkan total akumulasi kehadiran
                    $totalHadir = AbsensiSiswa::where('siswa_id', $siswa->id)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->count();

                    if ($totalHadir >= $reqDays) {
                        $qualified = true;
                    }
                } else {
                    // Berdasarkan streak kehadiran beruntun
                    // Ambil riwayat absen urut berdasarkan tanggal
                    $absensis = AbsensiSiswa::where('siswa_id', $siswa->id)
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->orderBy('tanggal', 'asc')
                        ->pluck('tanggal')
                        ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
                        ->toArray();

                    if (count($absensis) >= $reqDays) {
                        $currentStreak = 0;
                        $maxStreak = 0;
                        $lastDate = null;

                        foreach ($absensis as $dateStr) {
                            $date = \Carbon\Carbon::parse($dateStr);
                            if ($lastDate === null) {
                                $currentStreak = 1;
                            } else {
                                $diff = $date->diffInDays($lastDate);
                                if ($diff === 1) {
                                    $currentStreak++;
                                } elseif ($diff > 1) {
                                    $maxStreak = max($maxStreak, $currentStreak);
                                    $currentStreak = 1;
                                }
                            }
                            $lastDate = $date;
                        }
                        $maxStreak = max($maxStreak, $currentStreak);

                        if ($maxStreak >= $reqDays) {
                            $qualified = true;
                        }
                    }
                }

                if ($qualified) {
                    $studentBadge = StudentBadge::firstOrCreate([
                        'siswa_id' => $siswa->id,
                        'badge_id' => $badge->id,
                    ], [
                        'earned_at' => now(),
                    ]);

                    // Kirim WA ucapan selamat ke orang tua jika badge baru diraih
                    if ($studentBadge->wasRecentlyCreated && $siswa->no_hp_ortu) {
                        $badgeTemplate = NotificationTemplate::where('type', 'badge_baru')->first();
                        $lembaga = Pengaturan::where('key', 'lembaga')->value('value') ?? 'Sekolah';
                        $pesan = str_replace(
                            ['{nama}', '{badge}', '{kelas}', '{lembaga}'],
                            [$siswa->nama_lengkap, $badge->name, $siswa->kelas?->nama ?? '-', $lembaga],
                            $badgeTemplate?->content ?? "Alhamdulillah! {nama} meraih badge {badge} di {lembaga}. Selamat!"
                        );
                        SendWhatsAppMessage::dispatch(
                            $siswa->no_hp_ortu,
                            $pesan,
                            'Prestasi Ananda - ' . $lembaga,
                            true,
                            $siswa->id
                        );
                    }
                }
            }
        }

        // --- KALKULASI LEADERBOARD SISWA INDIVIDUAL (Skor Keaktifan) ---
        StudentLeaderboard::where('tahun_akademik_id', $taId)->delete();

        $allSiswas = Siswa::with('kelas')->get();
        $studentScores = [];

        foreach ($allSiswas as $siswa) {
            $absensis = AbsensiSiswa::where('siswa_id', $siswa->id)
                ->where('tahun_akademik_id', $taId)
                ->get();

            $totalAttendance = $absensis->count();
            $totalHadir = $absensis->whereIn('status', ['Hadir', 'hadir', 'Terlambat', 'terlambat'])->count();
            
            // Ambil skor dari field points_earned (Gamifikasi Baru)
            // Jika kosong, hitung manual seperti base point untuk kompatibilitas data lama
            $score = 0;
            foreach ($absensis as $absen) {
                if (isset($absen->points_earned) && $absen->points_earned != 0) {
                    $score += $absen->points_earned;
                } else {
                    // Fallback hitung manual
                    $statusLower = strtolower($absen->status);
                    if ($statusLower === 'hadir') {
                        $score += 10;
                    } elseif ($statusLower === 'terlambat') {
                        $score += 5;
                    } elseif ($statusLower === 'sakit' || $statusLower === 'izin') {
                        $score += 2;
                    } elseif ($statusLower === 'alpha') {
                        $score -= 10;
                    }
                }
            }

            if ($totalAttendance > 0) {
                $studentScores[] = [
                    'siswa_id' => $siswa->id,
                    'tahun_akademik_id' => $taId,
                    'score' => $score,
                    'total_attendance' => $totalAttendance,
                    'total_present' => $totalHadir,
                ];
            }
        }

        // Urutkan berdasarkan skor tertinggi
        usort($studentScores, fn($a, $b) => $b['score'] <=> $a['score']);

        // Simpan ranking
        foreach ($studentScores as $index => $data) {
            StudentLeaderboard::create([
                'siswa_id' => $data['siswa_id'],
                'tahun_akademik_id' => $data['tahun_akademik_id'],
                'rank' => $index + 1,
                'score' => $data['score'],
                'total_attendance' => $data['total_attendance'],
                'total_present' => $data['total_present'],
                'calculated_at' => now(),
            ]);
        }

        // Kirim WA ucapan selamat ke Top 3 siswa
        $top3Template = NotificationTemplate::where('type', 'leaderboard_top3')->first();
        $lembaga = Pengaturan::where('key', 'lembaga')->value('value') ?? 'Sekolah';
        foreach ($studentScores as $index => $data) {
            if ($index >= 3) break; // Hanya Top 3

            $siswaTop = Siswa::with('kelas')->find($data['siswa_id']);
            if ($siswaTop && $siswaTop->no_hp_ortu) {
                $pesan = str_replace(
                    ['{nama}', '{kelas}', '{rank}', '{score}', '{lembaga}'],
                    [$siswaTop->nama_lengkap, $siswaTop->kelas?->nama ?? '-', $index + 1, $data['score'], $lembaga],
                    $top3Template?->content ?? "Alhamdulillah! {nama} meraih peringkat #{rank} sebagai siswa terajin di {lembaga}!"
                );
                SendWhatsAppMessage::dispatch(
                    $siswaTop->no_hp_ortu,
                    $pesan,
                    'Prestasi Ananda - ' . $lembaga,
                    true,
                    $siswaTop->id
                );
            }
        }

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function getStudentLeaderboard(Request $request)
    {
        $taId = $request->get('tahun_akademik_id',
            TahunAkademik::where('is_aktif', true)->first()?->id);

        $limit = (int) $request->get('limit', 20);

        $leaderboard = StudentLeaderboard::with(['siswa.kelas', 'siswa.studentBadges.badge'])
            ->where('tahun_akademik_id', $taId)
            ->orderBy('rank')
            ->limit($limit)
            ->get();

        return response()->json(['success' => true, 'data' => $leaderboard]);
    }

    public function queueOfflineEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'payload' => 'required|array',
            'device_uuid' => 'required|string',
        ]);

        $queue = OfflineQueue::create([
            'event_type' => $request->event_type,
            'payload' => $request->payload,
            'device_uuid' => $request->device_uuid,
            'status' => 'pending',
        ]);

        return response()->json(['success' => true, 'data' => $queue]);
    }

    public function syncOfflineEvents(Request $request)
    {
        $deviceUuid = $request->get('device_uuid');
        
        $queues = OfflineQueue::where('device_uuid', $deviceUuid)
            ->where('status', 'pending')
            ->where('retry_count', '<', 3)
            ->get();

        $synced = [];
        foreach ($queues as $queue) {
            try {
                DB::beginTransaction();
                
                switch ($queue->event_type) {
                    case 'absensi':
                        $payload = $queue->payload;
                        $qrCode = $payload['qr_code'] ?? null;
                        $scannedAt = $payload['scanned_at'] ?? null;

                        if (!$qrCode) {
                            throw new \Exception('Data absensi tidak lengkap: qr_code diperlukan.');
                        }

                        // Tentukan waktu scan (gunakan scanned_at atau waktu sekarang)
                        $scanTime = $scannedAt ? \Carbon\Carbon::parse($scannedAt) : now();
                        $tanggal = $scanTime->toDateString();
                        $currentTime = $scanTime->format('H:i');

                        // Load settings (cache dari DB atau default)
                        $jamMasuk       = '07:00';
                        $jamBatasMasuk  = '08:00';
                        $jamMulaiPulang = '14:00';
                        $jamAkhirPulang = '17:00';
                        $toleransi      = 15;

                        // 1. Cek apakah ini Siswa
                        $siswa = \App\Models\Siswa::with('kelas')->where('qr_code', $qrCode)->first();

                        if ($siswa) {
                            $absensi = \App\Models\AbsensiSiswa::where('siswa_id', $siswa->id)
                                ->whereDate('tanggal', $tanggal)
                                ->first();

                            // --- LOGIKA PULANG ---
                            if ($absensi && $currentTime >= $jamMulaiPulang) {
                                if ($currentTime > $jamAkhirPulang) {
                                    throw new \Exception('Sesi scan pulang sudah ditutup (Batas: ' . $jamAkhirPulang . ').');
                                }

                                if ($absensi->jam_pulang) {
                                    // Sudah scan pulang, skip (anggap sukses)
                                    break;
                                }

                                $absensi->update(['jam_pulang' => $currentTime]);
                                break;
                            }

                            // --- LOGIKA MASUK ---
                            if ($absensi) {
                                // Sudah tercatat hadir, skip
                                break;
                            }

                            if ($currentTime > $jamBatasMasuk) {
                                throw new \Exception('Sesi scan masuk sudah ditutup (Batas: ' . $jamBatasMasuk . ').');
                            }

                            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
                            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

                            \App\Models\AbsensiSiswa::create([
                                'siswa_id'   => $siswa->id,
                                'kelas_id'   => $siswa->kelas_id,
                                'tanggal'    => $tanggal,
                                'jam_masuk'  => $currentTime,
                                'status'     => $status,
                                'keterangan' => 'Offline sync',
                                'metode'     => 'qr',
                            ]);
                            break;
                        }

                        // 2. Jika bukan siswa, cek Guru
                        $guru = \App\Models\Guru::where('qr_code', $qrCode)->first();
                        if ($guru) {
                            $absensi = \App\Models\AbsensiGuru::where('guru_id', $guru->id)
                                ->whereDate('tanggal', $tanggal)
                                ->first();

                            // --- LOGIKA PULANG GURU ---
                            if ($absensi && $currentTime >= $jamMulaiPulang) {
                                if ($currentTime > $jamAkhirPulang) {
                                    throw new \Exception('Sesi scan pulang sudah ditutup.');
                                }
                                if ($absensi->jam_pulang) {
                                    break; // sudah pulang, skip
                                }
                                $absensi->update(['jam_pulang' => $currentTime]);
                                break;
                            }

                            if ($absensi) {
                                break; // sudah hadir, skip
                            }

                            if ($currentTime > $jamBatasMasuk) {
                                throw new \Exception('Sesi scan masuk guru sudah ditutup.');
                            }

                            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
                            $status = ($currentTime > $limitHadir) ? 'terlambat' : 'hadir';

                            \App\Models\AbsensiGuru::create([
                                'guru_id'    => $guru->id,
                                'tanggal'    => $tanggal,
                                'jam_masuk'  => $currentTime,
                                'status'     => $status,
                                'keterangan' => 'Offline sync',
                                'metode'     => 'qr',
                            ]);
                            break;
                        }

                        // 3. Tidak ditemukan
                        throw new \Exception('QR code tidak dikenal (Siswa/Guru).');

                    case 'absensi_ekskul':
                        $payload = $queue->payload;
                        $nis = $payload['nis'] ?? null;
                        $token = $payload['token'] ?? null;
                        $scannedAt = $payload['scanned_at'] ?? null;

                        if (!$nis || !$token) {
                            throw new \Exception('Data absensi ekskul tidak lengkap: NIS dan token diperlukan.');
                        }

                        // Cari siswa berdasarkan NIS
                        $siswa = Siswa::where('nis', $nis)->first();
                        if (!$siswa) {
                            throw new \Exception("Siswa dengan NIS {$nis} tidak ditemukan.");
                        }

                        // Validasi token QR menggunakan service
                        $ekskulAbsensiService = new EkskulAbsensiService();
                        $tokenData = $ekskulAbsensiService->verifyQRToken($token);
                        if (!$tokenData) {
                            throw new \Exception('Token QR tidak valid atau sudah kedaluwarsa.');
                        }

                        $ekskulId = $tokenData['ekskul_id'];
                        $tanggal = $tokenData['tanggal'];

                        // Validasi membership siswa sebagai anggota aktif ekskul
                        $anggota = EkskulAnggota::where('ekskul_id', $ekskulId)
                            ->where('siswa_id', $siswa->id)
                            ->where('status', 'aktif')
                            ->first();

                        if (!$anggota) {
                            throw new \Exception('Siswa bukan anggota aktif ekskul ini.');
                        }

                        // Cek duplikasi — sudah absen hari ini untuk ekskul yang sama
                        $existing = EkskulAbsensi::where('ekskul_id', $ekskulId)
                            ->where('siswa_id', $siswa->id)
                            ->whereDate('tanggal', $tanggal)
                            ->first();

                        if ($existing) {
                            // Sudah pernah absen, lewati saja (anggap sukses)
                            break;
                        }

                        // Hitung jam absen dari scanned_at
                        $jamAbsen = null;
                        if ($scannedAt) {
                            try {
                                $jamAbsen = \Carbon\Carbon::parse($scannedAt)->format('H:i');
                            } catch (\Exception $e) {
                                $jamAbsen = now()->format('H:i');
                            }
                        }

                        // Buat record absensi ekskul
                        EkskulAbsensi::create([
                            'ekskul_id' => $ekskulId,
                            'siswa_id'  => $siswa->id,
                            'tanggal'   => $tanggal,
                            'status'    => 'hadir',
                            'jam_absen' => $jamAbsen,
                            'keterangan' => 'Offline sync',
                        ]);
                        break;
                }

                $queue->update(['status' => 'synced', 'synced_at' => now()]);
                $synced[] = $queue->id;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $queue->increment('retry_count');
                $queue->update(['error_message' => $e->getMessage()]);
                Log::error('Sync offline event gagal', [
                    'event_type' => $queue->event_type,
                    'queue_id'   => $queue->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return response()->json(['success' => true, 'synced' => $synced]);
    }

    public function getReminderSettings()
    {
        $settings = ReminderSettings::all();
        return response()->json(['data' => $settings]);
    }

    public function updateReminderSettings(Request $request, $id)
    {
        $request->validate([
            'is_enabled' => 'boolean',
            'channel' => 'in:whatsapp,sms,both',
            'send_before_minutes' => 'integer|min:0',
            'custom_message' => 'nullable|string',
            'notify_parent' => 'boolean',
        ]);

        $settings = ReminderSettings::findOrFail($id);
        $settings->update($request->only([
            'is_enabled', 'channel', 'send_before_minutes', 
            'custom_message', 'notify_parent'
        ]));

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function sendReminder(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'reminder_type' => 'required|string',
        ]);

        $siswa = Siswa::findOrFail($request->siswa_id);
        $waService = new WhatsAppService();

        $template = NotificationTemplate::where('type', $request->reminder_type)->first();
        $message = $template?->content ?? 'Reminder absensi';

        $number = $siswa->no_hp_ortu ?? $siswa->user?->no_hp;
        
        if ($number) {
            $waService->sendMessage($number, $message);
        }

        return response()->json(['success' => true]);
    }

    public function getActivityAttendance(Request $request)
    {
        $kegiatanId = $request->get('kegiatan_id');
        $jurusan = $request->get('jurusan');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 10);

        $kegiatan = \App\Models\Kegiatan::find($kegiatanId);

        if (!$kegiatan) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'total' => 0,
                'from' => null,
                'to' => null,
            ]);
        }

        // Ambil data yang sudah ada di tabel absensi_kegiatan
        $attendanceQuery = AbsensiKegiatan::with(['siswa.kelas', 'kegiatan'])
            ->where('kegiatan_id', $kegiatanId);

        if ($jurusan) {
            $attendanceQuery->whereHas('siswa.kelas.jurusan', function($q) use ($jurusan) {
                $q->where('nama', $jurusan);
            });
        }

        $attendance = $attendanceQuery->get();

        $existingSiswaIds = $attendance->pluck('siswa_id')->toArray();

        // Cari siswa yang seharusnya ikut kegiatan ini tapi belum ada datanya di absensi_kegiatan
        $querySiswa = Siswa::with('kelas');

        if ($jurusan) {
            $querySiswa->whereHas('kelas.jurusan', function($q) use ($jurusan) {
                $q->where('nama', $jurusan);
            });
        }

        // Filter berdasarkan target_tingkat
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            $targetTingkats = $kegiatan->target_tingkat;
            $querySiswa->whereHas('kelas', function($q) use ($targetTingkats) {
                $q->whereIn('tingkat', $targetTingkats);
            });
        }

        // Filter berdasarkan target_jurusan
        if ($kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            $targetJurusan = $kegiatan->target_jurusan;
            if (($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) || 
                ($kegiatan->target_peserta && count($kegiatan->target_peserta) > 0)) {
                $querySiswa->orWhereHas('kelas.jurusan', function($q) use ($targetJurusan) {
                    $q->whereIn('nama', $targetJurusan);
                });
            } else {
                $querySiswa->whereHas('kelas.jurusan', function($q) use ($targetJurusan) {
                    $q->whereIn('nama', $targetJurusan);
                });
            }
        }

        // Filter berdasarkan target_peserta (ID Kelas)
        if ($kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            $targetKelasIds = $kegiatan->target_peserta;
            // Jika target_tingkat juga diisi, gunakan OR
            if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
                $querySiswa->orWhereIn('kelas_id', $targetKelasIds);
            } else {
                $querySiswa->whereIn('kelas_id', $targetKelasIds);
            }
        }

        $siswaLainnya = $querySiswa->whereNotIn('id', $existingSiswaIds)->get();

        // Gabungkan data
        $resultData = $attendance->toArray();
        foreach ($siswaLainnya as $siswa) {
            $resultData[] = [
                'id' => null,
                'kegiatan_id' => $kegiatanId,
                'siswa_id' => $siswa->id,
                'status' => null,
                'keterangan' => null,
                'jam_absen' => null,
                'siswa' => $siswa->toArray(),
                'kegiatan' => $kegiatan->toArray(),
            ];
        }

        // Manual pagination pada merged collection
        $total = count($resultData);
        $lastPage = max(1, (int) ceil($total / $perPage));

        // Pastikan page tidak melebihi lastPage
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $offset = ($page - 1) * $perPage;
        $items = array_slice($resultData, $offset, $perPage);

        $from = $total > 0 ? $offset + 1 : null;
        $to = $total > 0 ? min($offset + $perPage, $total) : null;

        return response()->json([
            'data' => $items,
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function recordActivityAttendance(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatan,id',
            'siswa_id' => 'required|exists:siswa,id',
            'status' => 'required|in:hadir,tidak_hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|string',
        ]);

        $kegiatan = \App\Models\Kegiatan::findOrFail($request->kegiatan_id);
        $siswa = Siswa::findOrFail($request->siswa_id);

        // Validasi target peserta
        $isTarget = false;
        if (!$kegiatan->target_tingkat && !$kegiatan->target_jurusan && !$kegiatan->target_peserta) {
            $isTarget = true;
        } else {
            if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
                if ($siswa->kelas && in_array($siswa->kelas->tingkat, $kegiatan->target_tingkat)) {
                    $isTarget = true;
                }
            }
            // Check Jurusan
            if (!$isTarget && $kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
                if ($siswa->kelas && in_array($siswa->kelas->jurusan?->nama, $kegiatan->target_jurusan)) {
                    $isTarget = true;
                }
            }
            if (!$isTarget && $kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
                if (in_array($siswa->kelas_id, $kegiatan->target_peserta)) {
                    $isTarget = true;
                }
            }
        }

        if (!$isTarget) {
            return response()->json([
                'success' => false, 
                'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.'
            ], 403);
        }

        $status = $request->status;
        if ($status === 'tidak_hadir') {
            $status = 'alpha';
        }

        $jamAbsen = $status === 'hadir' ? now() : null;

        $attendance = AbsensiKegiatan::updateOrCreate(
            ['kegiatan_id' => $request->kegiatan_id, 'siswa_id' => $request->siswa_id],
            [
                'status' => $status,
                'keterangan' => $request->keterangan,
                'jam_absen' => $jamAbsen,
            ]
        );

        return response()->json(['success' => true, 'data' => $attendance, 'message' => 'Absensi berhasil dicatat']);
    }

    public function updateDeviceOfflineMode(Request $request, $id)
    {
        $request->validate([
            'offline_mode_enabled' => 'required|boolean',
            'max_retry_attempts' => 'integer|min:1|max:10',
        ]);

        $device = AuthorizedDevice::findOrFail($id);
        $device->update($request->only(['offline_mode_enabled', 'max_retry_attempts']));

        return response()->json(['success' => true, 'data' => $device]);
    }
}
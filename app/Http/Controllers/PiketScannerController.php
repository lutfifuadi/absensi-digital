<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Support\QrScanLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PiketScannerController extends Controller
{
    /**
     * Helper: Ambil pengaturan ter-cache untuk efisiensi.
     */
    private function getCachedSettings()
    {
        return Cache::remember('absensi_settings_piket', now()->addMinutes(10), function () {
            return Pengaturan::whereIn('key', [
                'jam_masuk', 
                'jam_batas_masuk', 
                'jam_pulang', 
                'jam_mulai_pulang', 
                'jam_akhir_pulang', 
                'toleransi_terlambat',
                'nama_sekolah'
            ])->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Halaman Scanner QR Internal Guru Piket.
     */
    public function index()
    {
        return view('piket.scanner');
    }

    /**
     * Proses scan QR piket secara internal.
     */
    public function process(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string|max:255',
        ]);

        $ip             = $request->ip();
        $qrCode         = $data['qr_code'];
        $user           = $request->user();
        $username       = $user ? $user->username : 'piket';
        
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

                QrScanLogger::info('QR_SCAN_PIKET_PULANG_SUCCESS', [
                    'ip'      => $ip,
                    'user'    => $username,
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
                    'message' => 'Sesi scan masuk sudah ditutup (Batas: ' . $jamBatasMasuk . ').',
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
                    'keterangan'  => 'Scan QR internal oleh Guru Piket (User: ' . $username . ')',
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

            QrScanLogger::info('QR_SCAN_PIKET_SUCCESS', [
                'ip'      => $ip,
                'user'    => $username,
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
                    'keterangan' => 'Scan QR internal oleh Guru Piket (User: ' . $username . ')',
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

            QrScanLogger::info('QR_SCAN_PIKET_GURU_SUCCESS', [
                'ip'    => $ip,
                'user'  => $username,
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
        QrScanLogger::error('QR_PIKET_NOT_FOUND', [
            'ip'      => $ip,
            'user'    => $username,
            'qr_code' => substr($qrCode, 0, 20) . (strlen($qrCode) > 20 ? '...' : ''),
            'ket'     => 'QR code tidak dikenal oleh piket',
        ]);

        return response()->json([
            'success' => false,
            'message' => 'QR code tidak dikenal. Pastikan QR code siswa atau guru valid.',
        ]);
    }

    /**
     * Endpoint statistik real-time sesi ini / hari ini untuk piket.
     */
    public function stats()
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
     * Halaman Rekap Kehadiran Harian Siswa khusus Guru Piket.
     */
    public function rekap(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->toDateString());
        $kelasId = $request->get('kelas_id');
        $status  = $request->get('status');

        $kelas = Kelas::orderBy('nama', 'asc')->get();

        // Tampilkan rekap siswa pada tanggal yang dipilih
        // Kita ingin menampilkan seluruh siswa di kelas terpilih (jika ada filter kelas)
        // beserta status absensinya pada tanggal tersebut.
        $query = Siswa::with(['kelas', 'absensi' => function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        }]);

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($status) {
            if ($status === 'belum_presensi') {
                $query->whereDoesntHave('absensi', function ($q) use ($tanggal) {
                    $q->whereDate('tanggal', $tanggal);
                });
            } else {
                $query->whereHas('absensi', function ($q) use ($tanggal, $status) {
                    $q->whereDate('tanggal', $tanggal)->where('status', $status);
                });
            }
        }

        $siswaList = $query->orderBy('nama_lengkap', 'asc')->paginate(50)->withQueryString();

        return view('piket.rekap', compact('siswaList', 'kelas', 'tanggal', 'kelasId', 'status'));
    }

    /**
     * Update status rekap kehadiran harian siswa.
     */
    public function updateRekap(Request $request)
    {
        $request->validate([
            'siswa_id'   => 'required|exists:siswa,id',
            'tanggal'    => 'required|date',
            'status'     => 'required|in:hadir,terlambat,sakit,izin,alpha,belum_presensi',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $siswaId    = $request->siswa_id;
        $tanggal    = $request->tanggal;
        $status     = $request->status;
        $keterangan = $request->keterangan;

        // Batasi agar petugas piket HANYA BISA mengedit data kehadiran hari ini (tanggal berjalan)
        $today = now()->toDateString();
        if ($tanggal !== $today) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya diperbolehkan mengedit data kehadiran hari ini (tanggal berjalan: ' . $today . ').',
            ], 403);
        }

        $siswa = Siswa::findOrFail($siswaId);

        if ($status === 'belum_presensi') {
            // Hapus data absensi jika ada
            AbsensiSiswa::where('siswa_id', $siswaId)
                ->whereDate('tanggal', $tanggal)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data kehadiran berhasil dihapus (belum presensi).',
            ]);
        }

        $absensi = AbsensiSiswa::where('siswa_id', $siswaId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $currentTime = now()->format('H:i:s');
        $username    = request()->user() ? request()->user()->username : 'piket';

        if ($absensi) {
            $absensi->update([
                'status'     => $status,
                'keterangan' => $keterangan ?: 'Diupdate oleh piket (' . $username . ')',
            ]);
        } else {
            AbsensiSiswa::create([
                'siswa_id'    => $siswaId,
                'kelas_id'    => $siswa->kelas_id,
                'tanggal'     => $tanggal,
                'jam_masuk'   => $currentTime,
                'status'      => $status,
                'keterangan'  => $keterangan ?: 'Dicatat manual oleh piket (' . $username . ')',
                'metode'      => 'manual',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kehadiran ' . $siswa->nama_lengkap . ' berhasil diperbarui.',
        ]);
    }
}

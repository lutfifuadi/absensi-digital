<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AbsensiKegiatanController extends Controller
{
    public function liveBoard(Kegiatan $kegiatan)
    {
        $today = Carbon::today();

        // 1. Ambil target siswa berstatus aktif
        $querySiswa = Siswa::where('status', 'aktif');

        $isTargetFiltered = false;

        // Check target_tingkat
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            $querySiswa->whereHas('kelas', function ($q) use ($kegiatan) {
                $q->whereIn('tingkat', $kegiatan->target_tingkat);
            });
            $isTargetFiltered = true;
        }

        // Check target_jurusan
        if ($kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            $querySiswa->whereHas('kelas.jurusan', function ($q) use ($kegiatan) {
                $q->whereIn('nama', $kegiatan->target_jurusan);
            });
            $isTargetFiltered = true;
        }

        // Check target_peserta (ID Kelas)
        if ($kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            $querySiswa->whereIn('kelas_id', $kegiatan->target_peserta);
            $isTargetFiltered = true;
        }

        $totalTarget = $querySiswa->count();

        // 2. Hitung totalHadir (siswa unik yang absen hari ini di kegiatan ini)
        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->whereDate('tanggal_absen', $today)
            ->distinct('siswa_id')
            ->count();

        $totalAlpha = max(0, $totalTarget - $totalHadir);

        // Log absensi 10 log terbaru hari ini
        $logs = AbsensiKegiatan::with('siswa.kelas')
            ->where('kegiatan_id', $kegiatan->id)
            ->whereDate('tanggal_absen', $today)
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.kegiatan.live-board', compact('kegiatan', 'totalTarget', 'totalHadir', 'totalAlpha', 'logs'));
    }

    public function liveBoardScan(Request $request, Kegiatan $kegiatan)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $today = Carbon::today();

        // Cek apakah hari ini masuk rentang tanggal kegiatan
        if ($kegiatan->tanggal_pelaksanaan) {
            $start = $kegiatan->tanggal_pelaksanaan->startOfDay();
            $end = $kegiatan->tanggal_selesai ? $kegiatan->tanggal_selesai->endOfDay() : $kegiatan->tanggal_pelaksanaan->endOfDay();

            if (!Carbon::now()->between($start, $end)) {
                return response()->json(['success' => false, 'message' => 'Kegiatan tidak berlangsung hari ini!'], 422);
            }
        }

        $siswa = Siswa::with('kelas.jurusan')->where('qr_code', $request->qr_code)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Kartu Siswa tidak terdaftar!'], 404);
        }

        // Cek target
        $isTarget = false;
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            if ($siswa->kelas && in_array($siswa->kelas->tingkat, $kegiatan->target_tingkat)) {
                $isTarget = true;
            }
        }

        if (!$isTarget && $kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            if ($siswa->kelas && $siswa->kelas->jurusan && in_array($siswa->kelas->jurusan->nama, $kegiatan->target_jurusan)) {
                $isTarget = true;
            }
        }

        if (!$isTarget && $kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            if (in_array($siswa->kelas_id, $kegiatan->target_peserta)) {
                $isTarget = true;
            }
        }

        if (!$kegiatan->target_tingkat && !$kegiatan->target_jurusan && !$kegiatan->target_peserta) {
            $isTarget = true;
        }

        if (!$isTarget) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.'], 403);
        }

        // Cek duplikasi absensi siswa HARI INI
        $already = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $siswa->id)
            ->whereDate('tanggal_absen', $today)
            ->exists();

        if ($already) {
            return response()->json(['success' => false, 'message' => 'Siswa sudah melakukan absensi hari ini.'], 422);
        }

        // Simpan
        AbsensiKegiatan::create([
            'kegiatan_id' => $kegiatan->id,
            'siswa_id' => $siswa->id,
            'tanggal_absen' => $today,
            'jam_absen' => now()->format('H:i:s'),
            'status' => 'hadir',
        ]);

        // Hitung stats ter-update
        // 1. Ambil target siswa berstatus aktif
        $querySiswa = Siswa::where('status', 'aktif');

        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            $querySiswa->whereHas('kelas', function ($q) use ($kegiatan) {
                $q->whereIn('tingkat', $kegiatan->target_tingkat);
            });
        }

        if ($kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            $querySiswa->whereHas('kelas.jurusan', function ($q) use ($kegiatan) {
                $q->whereIn('nama', $kegiatan->target_jurusan);
            });
        }

        if ($kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            $querySiswa->whereIn('kelas_id', $kegiatan->target_peserta);
        }

        $totalTarget = $querySiswa->count();

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->whereDate('tanggal_absen', $today)
            ->distinct('siswa_id')
            ->count();

        $totalAlpha = max(0, $totalTarget - $totalHadir);

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.',
            'siswa_nama' => $siswa->nama_lengkap,
            'siswa_kelas' => $siswa->kelas?->nama ?? '-',
            'waktu' => now()->format('H:i:s'),
            'stats' => [
                'totalTarget' => $totalTarget,
                'totalHadir' => $totalHadir,
                'totalAlpha' => $totalAlpha,
            ]
        ]);
    }

    public function scan()
    {
        // Tampilkan semua kegiatan (tanpa filter tanggal) agar admin bisa scan kapan saja
        $kegiatans = Kegiatan::latest('tanggal_pelaksanaan')->get();
        return view('admin.kegiatan.scan', compact('kegiatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'kegiatan_id' => 'required|exists:kegiatan,id',
        ]);

        $siswa = Siswa::with('kelas')->where('qr_code', $request->qr_code)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Kartu Siswa tidak terdaftar!'], 404);
        }

        // Check if student is target
        $kegiatan = Kegiatan::findOrFail($request->kegiatan_id);
        $isTarget = false;

        // 1. Check Level (Tingkat)
        if ($kegiatan->target_tingkat && count($kegiatan->target_tingkat) > 0) {
            if ($siswa->kelas && in_array($siswa->kelas->tingkat, $kegiatan->target_tingkat)) {
                $isTarget = true;
            }
        }

        // 2. Check Jurusan
        if (!$isTarget && $kegiatan->target_jurusan && count($kegiatan->target_jurusan) > 0) {
            if ($siswa->kelas && in_array($siswa->kelas->jurusan?->nama, $kegiatan->target_jurusan)) {
                $isTarget = true;
            }
        }

        // 3. Check Specific Class (ID Kelas)
        if (!$isTarget && $kegiatan->target_peserta && count($kegiatan->target_peserta) > 0) {
            if (in_array($siswa->kelas_id, $kegiatan->target_peserta)) {
                $isTarget = true;
            }
        }

        // 4. If no target defined, assume ALL students
        if (!$kegiatan->target_tingkat && !$kegiatan->target_jurusan && !$kegiatan->target_peserta) {
            $isTarget = true;
        }

        if (!$isTarget) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak termasuk dalam target peserta kegiatan ini.'], 403);
        }

        // Check duplicate
        $already = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)
            ->where('siswa_id', $siswa->id)
            ->exists();

        $isNewAttendance = !$already;

        if (!$isNewAttendance) {
            $totalHadir = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)->count();
            return response()->json([
                'success' => false,
                'is_new' => false,
                'message' => 'Siswa sudah melakukan absensi pada kegiatan ini.',
                'siswa_nama' => $siswa->nama_lengkap,
                'siswa_kelas' => $siswa->kelas?->nama ?? '-',
                'total_hadir' => $totalHadir,
            ], 422);
        }

        AbsensiKegiatan::create([
            'kegiatan_id' => $request->kegiatan_id,
            'siswa_id' => $siswa->id,
            'jam_absen' => now(),
            'status' => 'HADIR',
        ]);

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)->count();

        return response()->json([
            'success' => true,
            'is_new' => true,
            'message' => 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.',
            'siswa_nama' => $siswa->nama_lengkap,
            'siswa_nisn' => $siswa->nisn,
            'siswa_kelas' => $siswa->kelas?->nama ?? '-',
            'waktu' => now()->format('H:i:s'),
            'total_hadir' => $totalHadir,
        ]);
    }

    public function rekap(Request $request)
    {
        $kegiatanId = $request->kegiatan_id;
        $jurusan = $request->jurusan;

        $logs = AbsensiKegiatan::with(['siswa.kelas', 'kegiatan'])
            ->when($kegiatanId, function($q) use ($kegiatanId) {
                return $q->where('kegiatan_id', $kegiatanId);
            })
            ->when($jurusan, function($q) use ($jurusan) {
                return $q->whereHas('siswa.kelas.jurusan', function($qj) use ($jurusan) {
                    $qj->where('nama', $jurusan);
                });
            })
            ->latest()
            ->paginate(20);

        $kegiatans = Kegiatan::latest()->get();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();

        return view('admin.kegiatan.rekap', compact('logs', 'kegiatans', 'jurusanList'));
    }
}

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
            if ($siswa->kelas && in_array($siswa->kelas->jurusan, $kegiatan->target_jurusan)) {
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
        $logs = AbsensiKegiatan::with(['siswa.kelas', 'kegiatan'])
            ->when($kegiatanId, function($q) use ($kegiatanId) {
                return $q->where('kegiatan_id', $kegiatanId);
            })
            ->latest()
            ->paginate(20);

        $kegiatans = Kegiatan::latest()->get();
        return view('admin.kegiatan.rekap', compact('logs', 'kegiatans'));
    }
}

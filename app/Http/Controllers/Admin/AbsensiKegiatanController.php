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
        // Only active activities today
        $kegiatans = Kegiatan::where('tanggal_pelaksanaan', date('Y-m-d'))->get();
        return view('admin.kegiatan.scan', compact('kegiatans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'kegiatan_id' => 'required|exists:kegiatan,id',
        ]);

        $siswa = Siswa::where('qr_code', $request->qr_code)->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Kartu Siswa tidak terdaftar!'], 404);
        }

        // Check duplicate today
        $already = AbsensiKegiatan::where('kegiatan_id', $request->kegiatan_id)
            ->where('siswa_id', $siswa->id)
            ->whereDate('jam_absen', Carbon::today())
            ->exists();

        if ($already) {
            return response()->json(['success' => false, 'message' => 'Siswa sudah melakukan absensi pada kegiatan ini.'], 422);
        }

        AbsensiKegiatan::create([
            'kegiatan_id' => $request->kegiatan_id,
            'siswa_id' => $siswa->id,
            'jam_absen' => now(),
            'status' => 'HADIR',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . $siswa->nama . ' berhasil dicatat.',
            'siswa_nama' => $siswa->nama,
            'waktu' => now()->format('H:i:s')
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

<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GuruAbsensiSelfController extends Controller
{
    /**
     * Tampilkan Rekap Kehadiran Harian milik Guru YBS
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil data Guru berdasarkan user yang login
        $guru = $user->guru ?: Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            return redirect()->back()->with('error', 'Profil data Guru Anda tidak ditemukan dalam sistem.');
        }

        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        // Query AbsensiGuru milik guru ini
        $absensiList = AbsensiGuru::query()
            ->where('guru_id', $guru->id)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung Statistik KPI
        $countHadir     = $absensiList->where('status', 'hadir')->count();
        $countTerlambat = $absensiList->where('status', 'terlambat')->count();
        $countIzin      = $absensiList->where('status', 'izin')->count();
        $countSakit     = $absensiList->where('status', 'sakit')->count();
        $countAlpha     = $absensiList->where('status', 'alpha')->count();
        $totalPresensi  = $absensiList->count();

        $totalHadirTerlambat = $countHadir + $countTerlambat;
        $persentaseKehadiran = $totalPresensi > 0
            ? round(($totalHadirTerlambat / $totalPresensi) * 100, 1)
            : 0;

        $persentaseTepatWaktu = $totalHadirTerlambat > 0
            ? round(($countHadir / $totalHadirTerlambat) * 100, 1)
            : 0;

        $stats = [
            'total_presensi'         => $totalPresensi,
            'count_hadir'            => $countHadir,
            'count_terlambat'        => $countTerlambat,
            'count_izin_sakit'       => $countIzin + $countSakit,
            'count_alpha'            => $countAlpha,
            'persentase_kehadiran'   => $persentaseKehadiran,
            'persentase_tepat_waktu' => $persentaseTepatWaktu,
        ];

        return view('guru.rekap-absensi-saya', compact(
            'guru',
            'absensiList',
            'bulan',
            'tahun',
            'stats'
        ));
    }
}

<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiSiswa;
use Carbon\Carbon;

class HomePage extends Controller
{
  public function index()
  {
    $tampilkanBeranda = \App\Models\Pengaturan::where('key', 'tampilkan_beranda')->value('value') ?? 'Ya';
    if ($tampilkanBeranda === 'Tidak') {
      return redirect()->route('login');
    }

    $today = Carbon::today();

    // Ambil 3 data scan kehadiran siswa terbaru dengan eager loading
    $recentScans = AbsensiSiswa::with(['siswa', 'kelas'])
      ->orderBy('id', 'desc')
      ->limit(3)
      ->get();

    // Rekap kehadiran siswa hari ini
    $recap = [
      'hadir' => AbsensiSiswa::whereDate('tanggal', $today)->where('status', 'hadir')->count(),
      'terlambat' => AbsensiSiswa::whereDate('tanggal', $today)->where('status', 'terlambat')->count(),
      'alpha' => AbsensiSiswa::whereDate('tanggal', $today)->where('status', 'alpha')->count(),
    ];

    $data = [
      'siswaCount' => \App\Models\Siswa::count(),
      'guruCount' => \App\Models\Guru::count(),
      'staffCount' => \App\Models\StaffTataUsaha::count(),
      'namaSekolah' => \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
        ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
        ?? 'Sistem Absensi',
      'logoSekolah' => \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value') ?? null,
      'recentScans' => $recentScans,
      'recap' => $recap,
    ];
    return view('content.pages.pages-home', $data);
  }
}

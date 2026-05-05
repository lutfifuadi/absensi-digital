<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomePage extends Controller
{
  public function index()
  {
    $tampilkanBeranda = \App\Models\Pengaturan::where('key', 'tampilkan_beranda')->value('value') ?? 'Ya';
    if ($tampilkanBeranda === 'Tidak') {
      return redirect()->route('login');
    }

    $data = [
      'siswaCount' => \App\Models\Siswa::count(),
      'guruCount' => \App\Models\Guru::count(),
      'staffCount' => \App\Models\StaffTataUsaha::count(),
      'namaSekolah' => \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
        ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
        ?? 'Sistem Absensi',
      'logoSekolah' => \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value') ?? null,
    ];
    return view('content.pages.pages-home', $data);
  }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\IzinSakit;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Display real-time metrics for Master Data.
     */
    public function index()
    {
        $totalSiswa = Siswa::where('status', 'aktif')->count();
        $totalGuru = Guru::where('status', 'aktif')->count();
        $totalKelas = Kelas::count();
        $totalMapel = Mapel::where('status', 1)->count();
        $pendingIzinCount = IzinSakit::where('status', 'pending')->count();

        return view('admin.master-data', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMapel',
            'pendingIzinCount'
        ));
    }

    /**
     * Search global API to search within Siswa, Guru, Kelas, and Mapel.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (trim($query) === '') {
            return response()->json([
                'siswa' => [],
                'guru' => [],
                'kelas' => [],
                'mapel' => [],
            ]);
        }

        $siswa = Siswa::where('nama_lengkap', 'like', "%{$query}%")
            ->orWhere('nis', 'like', "%{$query}%")
            ->orWhere('nisn', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $guru = Guru::where('nama_lengkap', 'like', "%{$query}%")
            ->orWhere('nip', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $kelas = Kelas::where('nama', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        $mapel = Mapel::where('nama_mapel', 'like', "%{$query}%")
            ->orWhere('kode_mapel', 'like', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json([
            'siswa' => $siswa,
            'guru' => $guru,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }
}

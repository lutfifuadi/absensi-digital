<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\IzinSakit;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalOrangTuaController extends Controller
{
    /**
     * Detail Profil Anak.
     */
    public function profilAnak($id)
    {
        $user = Auth::user();
        $anak = Siswa::with(['kelas.guru', 'tahunAkademik'])
            ->where('id', $id)
            ->where('ortu_user_id', $user->id)
            ->firstOrFail();

        return view('portal-ortu.profil-anak', compact('anak'));
    }

    /**
     * Riwayat Absensi Anak.
     */
    public function absensiAnak(Request $request, $id)
    {
        $user = Auth::user();
        $anak = Siswa::where('id', $id)
            ->where('ortu_user_id', $user->id)
            ->firstOrFail();

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $absensi = AbsensiSiswa::where('siswa_id', $anak->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('portal-ortu.absensi-anak', compact('anak', 'absensi', 'month', 'year'));
    }

    /**
     * Daftar Izin/Sakit Anak.
     */
    public function izinSakit()
    {
        $user = Auth::user();
        $anakIds = Siswa::where('ortu_user_id', $user->id)->pluck('id');

        $izinSakit = IzinSakit::whereIn('reference_id', $anakIds)
            ->where('tipe', 'siswa')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('portal-ortu.izin-sakit-index', compact('izinSakit'));
    }

    /**
     * Form Ajukan Izin/Sakit Anak.
     */
    public function izinSakitCreate()
    {
        $user = Auth::user();
        $anakList = Siswa::where('ortu_user_id', $user->id)->get();

        return view('portal-ortu.izin-sakit-create', compact('anakList'));
    }

    /**
     * Simpan Pengajuan Izin/Sakit Anak.
     */
    public function izinSakitStore(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jenis' => 'required|in:sakit,izin',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $siswa = Siswa::where('id', $request->siswa_id)
            ->where('ortu_user_id', $user->id)
            ->firstOrFail();

        $data = [
            'user_id' => $siswa->user_id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
        ];

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran_izin', 'public');
        }

        IzinSakit::create($data);

        return redirect()->route('ortu.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim dan menunggu persetujuan.');
    }
}

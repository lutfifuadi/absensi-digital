<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KonfigurasiPelanggaran;
use App\Models\TahunAkademik;
use App\Http\Requests\SaveKonfigurasiSpRequest;
use Illuminate\Http\Request;

class KonfigurasiPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun akademik aktif
        $tahunAkademikAktif = TahunAkademik::where('is_aktif', true)->first();
        
        // Ambil tahun akademik terpilih (jika ada pilihan dari session atau request, default ke aktif)
        $tahunAkademikId = $request->input('tahun_akademik_id', $tahunAkademikAktif?->id);
        
        $tahunAkademiks = TahunAkademik::orderBy('nama', 'desc')->get();
        
        $konfigurasi = null;
        if ($tahunAkademikId) {
            $konfigurasi = KonfigurasiPelanggaran::where('tahun_akademik_id', $tahunAkademikId)->first();
        }

        return view('admin.pelanggaran-konfigurasi.index', compact(
            'konfigurasi',
            'tahunAkademiks',
            'tahunAkademikId',
            'tahunAkademikAktif'
        ));
    }

    public function save(SaveKonfigurasiSpRequest $request)
    {
        $data = $request->validated();
        $data['notif_wa_aktif'] = $request->has('notif_wa_aktif');
        $data['created_by'] = auth()->id();

        $konfigurasi = KonfigurasiPelanggaran::updateOrCreate(
            ['tahun_akademik_id' => $data['tahun_akademik_id']],
            $data
        );

        return redirect()->route('admin.pelanggaran-konfigurasi.index', ['tahun_akademik_id' => $data['tahun_akademik_id']])
            ->with('success', 'Konfigurasi SP berhasil disimpan.');
    }
}

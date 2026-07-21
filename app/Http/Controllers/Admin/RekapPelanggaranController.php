<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class RekapPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $tahunId = session('tahun_akademik_id') ?? session('tahun_ajaran_id');
        if (!$tahunId) {
            $aktif = TahunAkademik::where('is_aktif', true)->first();
            $tahunId = $aktif ? $aktif->id : null;
        }

        $query = Siswa::query()
            ->where('status', 'aktif');

        if ($tahunId) {
            $query->where('tahun_akademik_id', $tahunId);
        }

        // withSum for pelanggaranSiswa on chosen tahun_akademik_id
        $query->withSum(['pelanggaranSiswa' => function ($q) use ($tahunId) {
            if ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            }
        }], 'poin_saat_itu');

        // with pelanggaranSp ordered by latest on chosen tahun_akademik_id
        $query->with(['pelanggaranSp' => function ($q) use ($tahunId) {
            if ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            }
            $q->latest();
        }]);

        // Filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->input('kelas_id'));
        }

        if ($request->filled('level_sp')) {
            $levelSp = $request->input('level_sp');
            $query->whereHas('pelanggaranSp', function ($q) use ($levelSp, $tahunId) {
                if ($tahunId) {
                    $q->where('tahun_akademik_id', $tahunId);
                }
                $q->where('level_sp', $levelSp);
            });
        }

        $siswa = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.pelanggaran-rekap.table', compact('siswa'));
        }

        $kelasOptions = Kelas::when($tahunId, function ($q) use ($tahunId) {
            $q->where('tahun_akademik_id', $tahunId);
        })->get();

        $tahunAkademiks = TahunAkademik::orderBy('nama', 'desc')->get();

        return view('admin.pelanggaran-rekap.index', compact('siswa', 'kelasOptions', 'tahunAkademiks'));
    }

    public function profilSiswa(Siswa $siswa, Request $request)
    {
        $tahunId = session('tahun_akademik_id') ?? session('tahun_ajaran_id');
        if (!$tahunId) {
            $aktif = TahunAkademik::where('is_aktif', true)->first();
            $tahunId = $aktif ? $aktif->id : null;
        }

        $pelanggaranSiswa = $siswa->pelanggaranSiswa()
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            })
            ->with(['jenisPelanggaran', 'pencatat'])
            ->latest()
            ->get();

        $pelanggaranSp = $siswa->pelanggaranSp()
            ->when($tahunId, function ($q) use ($tahunId) {
                $q->where('tahun_akademik_id', $tahunId);
            })
            ->with('penerbit')
            ->latest()
            ->get();

        $totalPoin = $pelanggaranSiswa->sum('poin_saat_itu');
        $spTerbaru = $pelanggaranSp->first();
        $levelSpAktif = $spTerbaru ? $spTerbaru->level_sp : '-';
        $jumlahPelanggaran = $pelanggaranSiswa->count();
        $jumlahSp = $pelanggaranSp->count();

        $stats = [
            'total_poin' => $totalPoin,
            'level_sp_aktif' => $levelSpAktif,
            'jumlah_pelanggaran' => $jumlahPelanggaran,
            'jumlah_sp' => $jumlahSp,
        ];

        return view('admin.pelanggaran-rekap.siswa', compact('siswa', 'pelanggaranSiswa', 'pelanggaranSp', 'stats'));
    }
}

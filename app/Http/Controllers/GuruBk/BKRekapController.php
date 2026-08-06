<?php

namespace App\Http\Controllers\GuruBk;

use App\Http\Controllers\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\Kelas;
use App\Models\PelanggaranSiswa;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BKRekapController extends Controller
{
    /**
     * Display summary/recap of student violations for counseling analysis.
     */
    public function index(Request $request)
    {
        $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
        $taId = $ta ? $ta->id : null;

        $filters = [
            'kelas_id' => $request->get('kelas_id'),
            'kategori_id' => $request->get('kategori_id'),
            'bulan' => $request->get('bulan', date('m')),
            'tahun' => $request->get('tahun', date('Y')),
        ];

        $query = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori'])
            ->when($taId, function ($q) use ($taId) {
                $q->where('tahun_akademik_id', $taId);
            });

        if ($filters['kelas_id']) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if ($filters['kategori_id']) {
            $query->whereHas('jenisPelanggaran', function ($q) use ($filters) {
                $q->where('kategori_id', $filters['kategori_id']);
            });
        }

        if ($filters['bulan']) {
            $query->whereMonth('tanggal_kejadian', $filters['bulan']);
        }

        if ($filters['tahun']) {
            $query->whereYear('tanggal_kejadian', $filters['tahun']);
        }

        $pelanggaranList = $query->latest('tanggal_kejadian')->paginate(20);

        // Rekap per Kelas
        $rekapKelas = DB::table('siswa as s')
            ->join('kelas as k', 's.kelas_id', '=', 'k.id')
            ->join('pelanggaran_siswa as ps', 's.id', '=', 'ps.siswa_id')
            ->when($taId, fn($q) => $q->where('ps.tahun_akademik_id', $taId))
            ->when($filters['bulan'], fn($q) => $q->whereMonth('ps.tanggal_kejadian', $filters['bulan']))
            ->when($filters['tahun'], fn($q) => $q->whereYear('ps.tanggal_kejadian', $filters['tahun']))
            ->select('k.nama as nama_kelas', DB::raw('COUNT(ps.id) as total_pelanggaran'), DB::raw('COALESCE(SUM(ps.poin_saat_itu), 0) as total_poin'))
            ->groupBy('k.id', 'k.nama')
            ->orderByDesc('total_poin')
            ->get();

        $kelases = Kelas::orderBy('nama')->get();
        $kategories = KategoriPelanggaran::orderBy('nama')->get();

        return view('guru-bk.rekap.index', compact('pelanggaranList', 'rekapKelas', 'kelases', 'kategories', 'filters', 'ta'));
    }

    /**
     * Export rekap data to CSV.
     */
    public function export(Request $request)
    {
        $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
        $taId = $ta ? $ta->id : null;

        $kelasId    = $request->get('kelas_id');
        $kategoriId = $request->get('kategori_id');
        $bulan      = $request->get('bulan', date('m'));
        $tahun      = $request->get('tahun', date('Y'));

        $data = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'pencatat'])
            ->when($taId,      fn($q) => $q->where('tahun_akademik_id', $taId))
            ->when($kelasId,   fn($q) => $q->whereHas('siswa',           fn($sq) => $sq->where('kelas_id',   $kelasId)))
            ->when($kategoriId,fn($q) => $q->whereHas('jenisPelanggaran',fn($jq) => $jq->where('kategori_id',$kategoriId)))
            ->when($bulan,     fn($q) => $q->whereMonth('tanggal_kejadian', $bulan))
            ->when($tahun,     fn($q) => $q->whereYear('tanggal_kejadian',  $tahun))
            ->latest('tanggal_kejadian')
            ->get();

        $filename = "rekap-pelanggaran-bk-{$bulan}-{$tahun}.csv";
        $headers  = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Tanggal', 'NIS', 'Nama Siswa', 'Kelas', 'Kategori', 'Jenis Pelanggaran', 'Poin', 'Keterangan', 'Pencatat']);

            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    $row->tanggal_kejadian ? $row->tanggal_kejadian->format('Y-m-d') : '',
                    $row->siswa->nis         ?? '',
                    $row->siswa->nama_lengkap ?? '',
                    $row->siswa->kelas->nama  ?? '',
                    $row->jenisPelanggaran->kategori->nama ?? '',
                    $row->jenisPelanggaran->nama           ?? '',
                    $row->poin_saat_itu,
                    $row->keterangan  ?? '',
                    $row->pencatat->name ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export rekap data to PDF (download).
     */
    public function exportPdf(Request $request)
    {
        $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
        $taId = $ta ? $ta->id : null;

        $kelasId    = $request->get('kelas_id');
        $kategoriId = $request->get('kategori_id');
        $bulan      = $request->get('bulan', date('m'));
        $tahun      = $request->get('tahun', date('Y'));

        // Ambil semua data tanpa paginasi untuk PDF
        $pelanggaranList = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'pencatat'])
            ->when($taId,      fn($q) => $q->where('tahun_akademik_id', $taId))
            ->when($kelasId,   fn($q) => $q->whereHas('siswa',           fn($sq) => $sq->where('kelas_id',   $kelasId)))
            ->when($kategoriId,fn($q) => $q->whereHas('jenisPelanggaran',fn($jq) => $jq->where('kategori_id',$kategoriId)))
            ->when($bulan,     fn($q) => $q->whereMonth('tanggal_kejadian', $bulan))
            ->when($tahun,     fn($q) => $q->whereYear('tanggal_kejadian',  $tahun))
            ->latest('tanggal_kejadian')
            ->get();

        // Rekap per Kelas
        $rekapKelas = DB::table('siswa as s')
            ->join('kelas as k', 's.kelas_id', '=', 'k.id')
            ->join('pelanggaran_siswa as ps', 's.id', '=', 'ps.siswa_id')
            ->when($taId,  fn($q) => $q->where('ps.tahun_akademik_id', $taId))
            ->when($bulan, fn($q) => $q->whereMonth('ps.tanggal_kejadian', $bulan))
            ->when($tahun, fn($q) => $q->whereYear('ps.tanggal_kejadian',  $tahun))
            ->select('k.nama as nama_kelas', DB::raw('COUNT(ps.id) as total_pelanggaran'), DB::raw('COALESCE(SUM(ps.poin_saat_itu), 0) as total_poin'))
            ->groupBy('k.id', 'k.nama')
            ->orderByDesc('total_poin')
            ->get();

        $namaBulan = Carbon::createFromDate($tahun, (int)$bulan, 1)->locale('id')->translatedFormat('F');

        $kelas     = $kelasId     ? Kelas::find($kelasId)             : null;
        $kategori  = $kategoriId  ? KategoriPelanggaran::find($kategoriId) : null;

        $pdf = Pdf::loadView('guru-bk.rekap.pdf', compact(
            'pelanggaranList', 'rekapKelas', 'ta',
            'bulan', 'tahun', 'namaBulan',
            'kelas', 'kategori'
        ))->setPaper('a4', 'landscape');

        $filename = sprintf('rekap-pelanggaran-bk-%s-%d.pdf', $namaBulan, $tahun);

        return $pdf->download($filename);
    }

    /**
     * Display summary/recap of student violations for Wali Kelas.
     */
    public function rekapWaliKelas(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;

        $assignedClass = null;
        if ($guru) {
            $assignedClass = Kelas::where('wali_kelas_id', $guru->id)->first();
        }

        if (!$assignedClass) {
            return redirect()->route('wali-kelas.dashboard')->with('error', 'Anda belum terdaftar sebagai wali kelas untuk kelas manapun.');
        }

        $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
        $taId = $ta ? $ta->id : null;

        $filters = [
            'kelas_id' => $assignedClass->id,
            'kategori_id' => $request->get('kategori_id'),
            'bulan' => $request->get('bulan', date('m')),
            'tahun' => $request->get('tahun', date('Y')),
        ];

        $query = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori'])
            ->when($taId, function ($q) use ($taId) {
                $q->where('tahun_akademik_id', $taId);
            })
            ->whereHas('siswa', function ($q) use ($assignedClass) {
                $q->where('kelas_id', $assignedClass->id);
            });

        if ($filters['kategori_id']) {
            $query->whereHas('jenisPelanggaran', function ($q) use ($filters) {
                $q->where('kategori_id', $filters['kategori_id']);
            });
        }

        if ($filters['bulan']) {
            $query->whereMonth('tanggal_kejadian', $filters['bulan']);
        }

        if ($filters['tahun']) {
            $query->whereYear('tanggal_kejadian', $filters['tahun']);
        }

        $pelanggaranList = $query->latest('tanggal_kejadian')->paginate(20)->withQueryString();

        // Rekap khusus kelas wali kelas
        $rekapKelas = DB::table('siswa as s')
            ->join('kelas as k', 's.kelas_id', '=', 'k.id')
            ->join('pelanggaran_siswa as ps', 's.id', '=', 'ps.siswa_id')
            ->where('k.id', $assignedClass->id)
            ->when($taId, fn($q) => $q->where('ps.tahun_akademik_id', $taId))
            ->when($filters['bulan'], fn($q) => $q->whereMonth('ps.tanggal_kejadian', $filters['bulan']))
            ->when($filters['tahun'], fn($q) => $q->whereYear('ps.tanggal_kejadian', $filters['tahun']))
            ->select('k.nama as nama_kelas', DB::raw('COUNT(ps.id) as total_pelanggaran'), DB::raw('COALESCE(SUM(ps.poin_saat_itu), 0) as total_poin'))
            ->groupBy('k.id', 'k.nama')
            ->orderByDesc('total_poin')
            ->get();

        $kelases = collect([$assignedClass]);
        $kategories = KategoriPelanggaran::orderBy('nama')->get();
        $isWaliKelasView = true;

        return view('guru-bk.rekap.index', compact('pelanggaranList', 'rekapKelas', 'kelases', 'kategories', 'filters', 'ta', 'assignedClass', 'isWaliKelasView'));
    }
}

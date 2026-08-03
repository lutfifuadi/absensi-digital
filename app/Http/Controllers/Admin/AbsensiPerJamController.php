<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbsensiPerJamRequest;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Policies\AbsensiPerJamPolicy;
use App\Services\AbsensiPerJamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * AbsensiPerJamController — pencatatan & rekap absensi siswa per jam pelajaran (PRD-006, P0).
 */
class AbsensiPerJamController extends Controller
{
    public function __construct(
        private AbsensiPerJamService $absensiPerJamService
    ) {}

    /**
     * Daftar jadwal hari ini.
     * - Guru: hanya jadwal miliknya (+ sesi penggantian).
     * - Admin/Operator/Piket: semua jadwal hari tsb.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tanggal = $request->query('tanggal', now()->toDateString());

        $isAdmin = $user->isSuperAdmin()
            || $user->isRole(User::ROLE_ADMIN_SEKOLAH)
            || $user->isRole(User::ROLE_OPERATOR);

        if ($user->isRole(User::ROLE_GURU)) {
            $guruId = $user->guru?->id;
            $jadwalList = $guruId
                ? $this->absensiPerJamService->getJadwalHariIniUntukGuru($guruId, $tanggal)
                : collect();
        } elseif ($user->isPiket() || $isAdmin) {
            $jadwalList = $this->absensiPerJamService->getJadwalHariIniUntukPiket($tanggal);
        } else {
            $jadwalList = collect();
        }

        return view('admin.absensi-per-jam.index', compact('jadwalList', 'tanggal', 'isAdmin'));
    }

    /**
     * Form roster absensi untuk satu sesi (jadwal + tanggal).
     */
    public function show(Request $request, JadwalPelajaran $jadwal)
    {
        $tanggal = $request->query('tanggal', $request->input('tanggal', now()->toDateString()));

        // F-3: otorisasi via policy
        Gate::authorize('isi', [$jadwal, $tanggal]);

        $tahunAkademikId = session('tahun_akademik_id')
            ?? session('tahun_ajaran_id')
            ?? TahunAkademik::where('is_aktif', true)->value('id');

        $user = auth()->user();
        $roster = $this->absensiPerJamService->getRosterSiswa($jadwal->id, $tahunAkademikId);
        $sesiData = $this->absensiPerJamService->getSesiData($jadwal->id, $tanggal);
        $statusOptions = AbsensiPerJamService::STATUS_LIST;
        $isAdmin = $user->isSuperAdmin() || $user->isRole(User::ROLE_ADMIN_SEKOLAH);

        return view('admin.absensi-per-jam.show', compact(
            'jadwal', 'roster', 'sesiData', 'tanggal', 'statusOptions', 'isAdmin'
        ));
    }

    /**
     * Simpan absensi massal untuk satu sesi (bulk upsert via service).
     */
    public function store(StoreAbsensiPerJamRequest $request, JadwalPelajaran $jadwal)
    {
        try {
            $tanggal = $request->input('tanggal');

            Gate::authorize('isi', [$jadwal, $tanggal]);

            $result = $this->absensiPerJamService->simpanAbsensi(
                $jadwal->id,
                $tanggal,
                $request->input('rows', []),
                auth()->id(),
                $request->input('metode', 'manual')
            );

            $msg = "Absensi berhasil disimpan: {$result['berhasil']} siswa.";
            if ($result['gagal'] > 0) {
                $msg .= " {$result['gagal']} baris dilewati (siswa tidak sesuai kelas).";
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    /**
     * Rekap per kelas/mapel — matriks siswa × pertemuan (F-5).
     */
    public function rekapIndex(Request $request)
    {
        $user = auth()->user();
        $tahunAkademikId = session('tahun_akademik_id')
            ?? session('tahun_ajaran_id')
            ?? TahunAkademik::where('is_aktif', true)->value('id');

        $filters = [
            'kelas_id' => $request->input('kelas_id'),
            'mapel_id' => $request->input('mapel_id'),
            'dari'     => $request->input('dari', now()->startOfMonth()->toDateString()),
            'sampai'   => $request->input('sampai', now()->toDateString()),
        ];

        // AC-F5-4: wali_kelas hanya melihat kelas asuhannya saja
        // (kelas.wali_kelas_id == guru->id) — scope dropdown rekap.
        $kelasQuery = Kelas::where('tahun_akademik_id', $tahunAkademikId);

        if ($user->isRole(User::ROLE_WALI_KELAS)) {
            $guruId = $user->guru?->id;
            $kelasOptions = $guruId
                ? $kelasQuery->where('wali_kelas_id', $guruId)->orderBy('nama')->get()
                : collect();
        } else {
            $kelasOptions = $kelasQuery->orderBy('nama')->get();
        }

        $mapelOptions = Mapel::orderBy('nama_mapel')->get();

        $rekap = null;
        if ($filters['kelas_id'] && $filters['dari'] && $filters['sampai']) {
            $kelas = Kelas::find($filters['kelas_id']);

            // F-3: otorisasi lihat rekap (kelas bisa null → policy langsung dipanggil)
            if (! app(AbsensiPerJamPolicy::class)->lihatRekap($user, $kelas)) {
                abort(403, 'Anda tidak berhak melihat rekap kelas ini.');
            }

            $rekap = $this->absensiPerJamService->getRekapPerKelas(
                (int) $filters['kelas_id'],
                $filters['dari'],
                $filters['sampai'],
                $filters['mapel_id'] ? (int) $filters['mapel_id'] : null
            );
        }

        return view('admin.absensi-per-jam.rekap', compact('rekap', 'kelasOptions', 'mapelOptions', 'filters'));
    }

    /**
     * Export Excel rekap per kelas/rentang (F-5, AC-F5-2).
     *
     * Query param: ?kelas_id=&dari=Y-m-d&sampai=Y-m-d
     * Data dibaca via AbsensiPerJamService::getRekapPerKelas (tanpa query langsung).
     *
     * Struktur file (.xlsx):
     *   baris 1-3  : judul "REKAP ABSENSI SISWA PER JAM PELAJARAN" + nama kelas + rentang
     *   baris 4    : header tabel (No | Nama Siswa | kolom per pertemuan | H T S I A D | % Kehadiran)
     *   baris 5+   : data tiap siswa — kode status H/T/S/I/A/D per pertemuan + akumulasi
     */
    public function exportExcel(Request $request)
    {
        // GET tanpa filter → kembali ke halaman rekap dengan flash error
        if (! $request->filled('kelas_id') || ! $request->filled('dari') || ! $request->filled('sampai')) {
            return redirect()->route('admin.absensi-per-jam.rekap')
                ->with('error', 'Pilih kelas dan rentang tanggal dulu.');
        }

        $validated = $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'dari'     => 'required|date',
            'sampai'   => 'required|date|after_or_equal:dari',
        ]);

        $kelasId = (int) $validated['kelas_id'];
        $dari    = $validated['dari'];
        $sampai  = $validated['sampai'];

        $kelas = Kelas::find($kelasId);

        // F-3: otorisasi sama seperti rekapIndex
        if (! app(AbsensiPerJamPolicy::class)->lihatRekap(auth()->user(), $kelas)) {
            abort(403, 'Anda tidak berhak melihat rekap kelas ini.');
        }

        // Baca data via service (F-5, AC-F5-2) — JANGAN query langsung di controller
        $rekap = $this->absensiPerJamService->getRekapPerKelas($kelasId, $dari, $sampai);

        $pertemuan = $rekap['pertemuan']; // Collection: key, tanggal, mata_pelajaran, jam_mulai, jam_selesai, guru
        $lastCol = Coordinate::stringFromColumnIndex(2 + $pertemuan->count() + 7); // No + Nama + pertemuan + H,T,S,I,A,D,%Kehadiran

        $filename = sprintf(
            'rekap-absensi-per-jam_%s_%s_%s.xlsx',
            Str::slug($kelas?->nama ?? 'kelas'),
            $dari,
            $sampai
        );

        return response()->streamDownload(function () use ($rekap, $pertemuan, $lastCol, $kelas, $dari, $sampai) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Rekap Per Jam');

            // ── Baris 1-3: judul (merge selebar tabel) ─────────────────────────
            $sheet->mergeCells("A1:{$lastCol}1");
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->mergeCells("A3:{$lastCol}3");
            $sheet->setCellValue('A1', 'REKAP ABSENSI SISWA PER JAM PELAJARAN');
            $sheet->setCellValue('A2', 'Kelas: ' . ($kelas?->nama ?? '-'));
            $sheet->setCellValue('A3', "Periode: {$dari} s/d {$sampai}");

            // ── Baris 4: header tabel ──────────────────────────────────────────
            $header = ['No', 'Nama Siswa'];
            foreach ($pertemuan as $p) {
                $header[] = sprintf('%s (%s %s-%s)', $p['tanggal'], $p['mata_pelajaran'], $p['jam_mulai'], $p['jam_selesai']);
            }
            $header[] = 'H';
            $header[] = 'T';
            $header[] = 'S';
            $header[] = 'I';
            $header[] = 'A';
            $header[] = 'D';
            $header[] = 'B';
            $header[] = '% Kehadiran';
            // Catatan: fromArray default $strictNullComparison=false → nilai 0 (loose == null)
            // ikut terbuang. Paksa strict=true agar 0 akumulasi tetap tertulis.
            $sheet->fromArray($header, null, 'A4', true);

            // ── Baris 5+: data tiap siswa ──────────────────────────────────────
            $row = 5;
            $no = 1;
            foreach ($rekap['siswa'] as $siswa) {
                $data = [$no++, $siswa->nama_lengkap];

                foreach ($pertemuan as $p) {
                    $data[] = $rekap['pivot'][$siswa->id][$p['key']] ?? '-';
                }

                $acc = $rekap['akumulasi'][$siswa->id] ?? [];
                $data[] = $acc['hadir'] ?? 0;
                $data[] = $acc['terlambat'] ?? 0;
                $data[] = $acc['sakit'] ?? 0;
                $data[] = $acc['izin'] ?? 0;
                $data[] = $acc['alpha'] ?? 0;
                $data[] = $acc['dispen'] ?? 0;
                $data[] = $acc['bolos'] ?? 0;
                $data[] = ($acc['persen'] ?? 0.0) . '%';

                $sheet->fromArray($data, null, "A{$row}", true);
                $row++;
            }

            // ── Style dasar (konsisten dengan EkskulAbsensiExport) ────────────
            $sheet->getStyle('A1:A3')->applyFromArray([
                'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F2937']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $headerRange = "A4:{$lastCol}4";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sheet->getStyle($headerRange)->getAlignment()->setWrapText(true);

            $lastRow = $sheet->getHighestRow();
            if ($lastRow >= 5) {
                $sheet->getStyle("A5:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
                ]);
                $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C5:{$lastCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Auto width semua kolom
            foreach (range(1, Coordinate::columnIndexFromString($lastCol)) as $i) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Rekap per siswa (F-5): riwayat kronologis per jam + akumulasi per mapel.
     */
    public function rekapSiswa(Request $request, Siswa $siswa)
    {
        $user = auth()->user();
        $dari = $request->input('dari', now()->startOfMonth()->toDateString());
        $sampai = $request->input('sampai', now()->toDateString());

        if (! app(AbsensiPerJamPolicy::class)->lihatRekap($user, $siswa->kelas)) {
            abort(403, 'Anda tidak berhak melihat rekap siswa ini.');
        }

        $rekap = $this->absensiPerJamService->getRekapPerSiswa($siswa->id, $dari, $sampai);

        return view('admin.absensi-per-jam.rekap-siswa', compact('rekap', 'siswa', 'dari', 'sampai'));
    }
}

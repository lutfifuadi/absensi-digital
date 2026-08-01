<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\EkskulJadwal;
use App\Models\JadwalKegiatan;
use App\Models\Kegiatan;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\AbsensiKegiatan;
use App\Services\PenjadwalanKegiatanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JadwalKegiatanController extends Controller
{
    public function index(Request $request)
    {
        // Auto sync today's recurring sessions on page view
        app(PenjadwalanKegiatanService::class)->generateSesiForDate(now());

        $query = JadwalKegiatan::with(['ekskul', 'ekskulJadwal', 'tahunAkademik']);

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->has('is_aktif') && $request->is_aktif !== '' && $request->is_aktif !== null) {
            $query->where('is_aktif', (bool) $request->is_aktif);
        }

        if ($request->filled('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $request->tahun_akademik_id);
        }

        $jadwals = $query->latest()->paginate(10)->withQueryString();
        $tahunAkademiks = TahunAkademik::all();
        $jenisList = ['Seminar', 'Ekstrakurikuler', 'Lomba', 'Acara Internal', 'Lainnya'];

        return view('admin.jadwal-kegiatan.index', compact('jadwals', 'tahunAkademiks', 'jenisList'));
    }

    public function sync(Request $request)
    {
        $count = app(PenjadwalanKegiatanService::class)->generateSesiForDate(now());
        return redirect()->back()->with('success', 'Sinkronisasi berhasil! Seluruh sesi kegiatan berulang hari ini telah diperbarui & disinkronkan.');
    }

    public function create()
    {
        return redirect()->route('admin.kegiatan.index')->with('info', 'Untuk membuat jadwal berulang baru, silakan buat kegiatan biasa terlebih dahulu, lalu aktifkan opsi Jadwal Berulang di halaman edit kegiatan tersebut.');
    }

    public function store(Request $request)
    {
        return redirect()->route('admin.kegiatan.index')->with('info', 'Untuk membuat jadwal berulang baru, silakan buat kegiatan biasa terlebih dahulu, lalu aktifkan opsi Jadwal Berulang di halaman edit kegiatan tersebut.');
    }

    public function edit(JadwalKegiatan $jadwalKegiatan)
    {
        $tahunAkademiks = TahunAkademik::all();
        $ekskuls = Ekskul::with('jadwal')->where('status', 'aktif')->get();
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort()->values();
        $jurusanList = Jurusan::pluck('nama')->sort()->values();
        $siswaList = Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('admin.jadwal-kegiatan.edit', compact(
            'jadwalKegiatan',
            'tahunAkademiks',
            'ekskuls',
            'kelas',
            'tingkat',
            'jurusanList',
            'siswaList'
        ));
    }

    public function update(Request $request, JadwalKegiatan $jadwalKegiatan)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'hari' => 'nullable|array',
            'hari.*' => 'string',
            'tanggal_kalender_input' => 'nullable|string',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string|max:500',
            'is_wajib' => 'nullable|boolean',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'target_gender' => 'nullable|in:L,P',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'integer|exists:siswa,id',
            'ekskul_id' => 'nullable|exists:ekskul,id',
            'ekskul_jadwal_id' => 'nullable|exists:ekskul_jadwal,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'qr_code_prefix' => 'nullable|string|max:50',
            'is_aktif' => 'nullable|boolean',
            'tahun_akademik_id' => 'nullable|exists:tahun_akademik,id',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');
        $data['is_aktif'] = $request->has('is_aktif') ? $request->boolean('is_aktif') : false;

        // Process hari / tanggal_kalender (tipe_jadwal cannot be changed)
        if ($jadwalKegiatan->tipe_jadwal === 'tanggal_kalender') {
            $data['hari'] = null;
            $rawInput = $request->input('tanggal_kalender_input', '');
            $dates = array_filter(array_map('intval', explode(',', $rawInput)), fn($val) => $val >= 1 && $val <= 31);
            $data['tanggal_kalender'] = array_values(array_unique($dates));
        } else {
            $data['tanggal_kalender'] = null;
            $hariRaw = $request->input('hari', []);
            $data['hari'] = array_values(array_map([PenjadwalanKegiatanService::class, 'normalizeHari'], (array)$hariRaw));
        }

        if (empty($data['qr_code_prefix'])) {
            $cleanName = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $data['nama_kegiatan']));
            $data['qr_code_prefix'] = substr($cleanName, 0, 4) ?: 'KGT';
        } else {
            $data['qr_code_prefix'] = strtoupper(trim($data['qr_code_prefix']));
        }

        $jadwalKegiatan->update($data);

        // Immediate check & generation for today
        app(PenjadwalanKegiatanService::class)->generateSesiForDate(now());

        return redirect()->route('admin.jadwal-kegiatan.index')->with('success', 'Jadwal kegiatan berulang berhasil diperbarui.');
    }

    public function destroy(JadwalKegiatan $jadwalKegiatan)
    {
        // Delete schedule (historical sessions & absensi are kept intact via nullOnDelete)
        $jadwalKegiatan->delete();

        return redirect()->route('admin.jadwal-kegiatan.index')->with('success', 'Jadwal kegiatan berulang berhasil dihapus. Record absensi historis tetap tersimpan.');
    }

    public function rekap(JadwalKegiatan $jadwalKegiatan, Request $request)
    {
        $bulan = $request->input('bulan', now()->format('m'));
        $tahun = $request->input('tahun', now()->format('Y'));

        $query = $jadwalKegiatan->kegiatans();

        if ($request->filled('bulan') && $request->filled('tahun')) {
            $query->whereYear('tanggal_pelaksanaan', $tahun)
                  ->whereMonth('tanggal_pelaksanaan', $bulan);
        }

        $kegiatans = $query->orderBy('tanggal_pelaksanaan', 'asc')->get();
        $kegiatanIds = $kegiatans->pluck('id')->toArray();
        $totalSesi = $kegiatans->count();

        // Query target siswa
        $querySiswa = Siswa::with('kelas.jurusan')->where('status', 'aktif');

        if ($jadwalKegiatan->target_siswa && count($jadwalKegiatan->target_siswa) > 0) {
            $querySiswa->whereIn('id', $jadwalKegiatan->target_siswa);
        } else {
            if ($jadwalKegiatan->target_tingkat && count($jadwalKegiatan->target_tingkat) > 0) {
                $querySiswa->whereHas('kelas', fn($q) => $q->whereIn('tingkat', $jadwalKegiatan->target_tingkat));
            }

            if ($jadwalKegiatan->target_jurusan && count($jadwalKegiatan->target_jurusan) > 0) {
                $querySiswa->whereHas('kelas.jurusan', fn($q) => $q->whereIn('nama', $jadwalKegiatan->target_jurusan));
            }

            if ($jadwalKegiatan->target_peserta && count($jadwalKegiatan->target_peserta) > 0) {
                $querySiswa->whereIn('kelas_id', $jadwalKegiatan->target_peserta);
            }

            if ($jadwalKegiatan->target_gender) {
                $querySiswa->where('jenis_kelamin', $jadwalKegiatan->target_gender);
            }
        }

        $targetSiswaList = $querySiswa->orderBy('nama_lengkap')->get();

        // Get absensi records for these kegiatans
        $absensiMap = [];
        if (!empty($kegiatanIds)) {
            $absensiRecords = AbsensiKegiatan::whereIn('kegiatan_id', $kegiatanIds)->get();
            foreach ($absensiRecords as $rec) {
                $absensiMap[$rec->siswa_id][$rec->kegiatan_id] = $rec;
            }
        }

        // Build rekap matrix
        $rekapSiswa = [];
        foreach ($targetSiswaList as $siswa) {
            $hadirCount = 0;
            $izinCount = 0;
            $sakitCount = 0;
            $alphaCount = 0;

            $details = [];
            foreach ($kegiatans as $keg) {
                $rec = $absensiMap[$siswa->id][$keg->id] ?? null;
                $status = $rec ? $rec->status : 'alpha';

                if ($status === 'hadir') $hadirCount++;
                elseif ($status === 'izin') $izinCount++;
                elseif ($status === 'sakit') $sakitCount++;
                else $alphaCount++;

                $details[$keg->id] = [
                    'kegiatan_id' => $keg->id,
                    'tanggal' => $keg->tanggal_pelaksanaan ? $keg->tanggal_pelaksanaan->format('d/m/Y') : '-',
                    'status' => $status,
                    'jam_absen' => $rec?->jam_absen ?? '-',
                ];
            }

            $persentase = $totalSesi > 0 ? round(($hadirCount / $totalSesi) * 100, 1) : 0;

            $rekapSiswa[] = [
                'siswa' => $siswa,
                'hadir' => $hadirCount,
                'izin' => $izinCount,
                'sakit' => $sakitCount,
                'alpha' => $alphaCount,
                'total_sesi' => $totalSesi,
                'persentase' => $persentase,
                'details' => $details,
            ];
        }

        return view('admin.jadwal-kegiatan.rekap', compact(
            'jadwalKegiatan',
            'kegiatans',
            'totalSesi',
            'rekapSiswa',
            'bulan',
            'tahun'
        ));
    }
}

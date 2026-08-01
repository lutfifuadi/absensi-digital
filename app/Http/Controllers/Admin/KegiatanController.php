<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\JadwalKegiatan;
use App\Services\PenjadwalanKegiatanService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    public function index()
    {
        // Auto-sync recurring sessions for today
        app(\App\Services\PenjadwalanKegiatanService::class)->generateSesiForDate(now());

        $kegiatans = Kegiatan::with('tahunAkademik')->latest()->paginate(10);
        return view('admin.kegiatan.index', compact('kegiatans'));
    }

    public function searchSiswa(Request $request)
    {
        $search = trim($request->query('q', ''));
        if (strlen($search) < 1) {
            return response()->json(['results' => []]);
        }

        $siswa = \App\Models\Siswa::with('kelas:id,nama')
            ->where('status', 'aktif')
            ->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            })
            ->orderBy('nama_lengkap')
            ->limit(30)
            ->get()
            ->map(function ($s) {
                $kelasNama = $s->kelas->nama ?? '-';
                $nis = $s->nis ?? '-';
                return [
                    'id'   => $s->id,
                    'text' => "{$s->nama_lengkap} — ({$kelasNama} / NIS: {$nis})",
                ];
            });

        return response()->json(['results' => $siswa]);
    }

    public function create()
    {
        $tahunAkademiks = TahunAkademik::all();
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();
        $siswaList = \App\Models\Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama_lengkap')->get();
        return view('admin.kegiatan.create', compact('tahunAkademiks', 'kelas', 'tingkat', 'jurusanList', 'siswaList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_pelaksanaan',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'target_gender' => 'nullable|in:L,P',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'integer|exists:siswa,id',
            'is_wajib' => 'nullable|boolean',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');
        $data['qr_code_kegiatan'] = 'KGT-' . strtoupper(Str::random(10));
        $data['tahun_akademik_id'] = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->first()?->id;

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

        // Ambil tanggal_selesai dari request. Jika kosong, set ke null.
        if (empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = null;
        }

        Kegiatan::create($data);

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dibuat.');
    }

    public function show(Kegiatan $kegiatan)
    {
        return redirect()->route('admin.kegiatan.index');
    }

    public function edit(Kegiatan $kegiatan)
    {
        $tahunAkademiks = TahunAkademik::all();
        $kelas = Kelas::all();
        $tingkat = Kelas::distinct()->pluck('tingkat')->filter()->sort();
        $jurusanList = \App\Models\Jurusan::pluck('nama')->sort()->values();
        $siswaList = \App\Models\Siswa::with('kelas')->where('status', 'aktif')->orderBy('nama_lengkap')->get();

        return view('admin.kegiatan.edit', compact('kegiatan', 'tahunAkademiks', 'kelas', 'tingkat', 'jurusanList', 'siswaList'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'jenis' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_pelaksanaan',
            'waktu_mulai' => 'nullable',
            'waktu_selesai' => 'nullable',
            'lokasi' => 'nullable|string',
            'keterangan' => 'nullable|string|max:500',
            'target_peserta' => 'nullable|array',
            'target_tingkat' => 'nullable|array',
            'target_jurusan' => 'nullable|array',
            'target_jurusan.*' => 'string|max:255',
            'target_gender' => 'nullable|in:L,P',
            'target_siswa' => 'nullable|array',
            'target_siswa.*' => 'integer|exists:siswa,id',
            'is_wajib' => 'nullable|boolean',
            'is_recurring' => 'nullable|boolean',
            'hari_pelaksanaan' => 'required_if:is_recurring,1|array',
            'hari_pelaksanaan.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'jadwal_tanggal_mulai' => 'required_if:is_recurring,1|date',
            'jadwal_tanggal_selesai' => 'nullable|date|after_or_equal:jadwal_tanggal_mulai',
        ], [
            'hari_pelaksanaan.required_if' => 'Hari pelaksanaan wajib dipilih minimal 1 hari jika jadwal berulang diaktifkan.',
            'hari_pelaksanaan.array' => 'Hari pelaksanaan harus berupa pilihan hari yang valid.',
            'hari_pelaksanaan.*.in' => 'Pilihan hari pelaksanaan tidak valid.',
            'jadwal_tanggal_mulai.required_if' => 'Tanggal mulai berlaku wajib diisi jika jadwal berulang diaktifkan.',
            'jadwal_tanggal_mulai.date' => 'Format tanggal mulai berlaku tidak valid.',
            'jadwal_tanggal_selesai.date' => 'Format tanggal selesai berlaku tidak valid.',
            'jadwal_tanggal_selesai.after_or_equal' => 'Tanggal selesai berlaku harus setelah atau sama dengan tanggal mulai.',
        ]);

        $data['is_wajib'] = $request->boolean('is_wajib');

        // Jika tanggal_pelaksanaan dikirim kosong, set ke null
        if (empty($data['tanggal_pelaksanaan'])) {
            $data['tanggal_pelaksanaan'] = null;
        }

        // Ambil tanggal_selesai dari request. Jika kosong, set ke null.
        if (empty($data['tanggal_selesai'])) {
            $data['tanggal_selesai'] = null;
        }

        DB::transaction(function() use ($request, $kegiatan, &$data) {
            $isRecurring = $request->boolean('is_recurring');
            $jadwalId = $kegiatan->jadwal_kegiatan_id;

            if ($isRecurring) {
                $hariInput = $request->input('hari_pelaksanaan', []);
                $hariNormalized = array_values(array_map([PenjadwalanKegiatanService::class, 'normalizeHari'], $hariInput));
                $tipeJadwal = count($hariNormalized) == 1 ? 'mingguan_1_hari' : 'mingguan_multi_hari';

                $jadwalData = [
                    'nama_kegiatan' => $data['nama_kegiatan'],
                    'jenis' => $data['jenis'],
                    'lokasi' => $data['lokasi'] ?? null,
                    'keterangan' => $data['keterangan'] ?? null,
                    'waktu_mulai' => $data['waktu_mulai'] ?? null,
                    'waktu_selesai' => $data['waktu_selesai'] ?? null,
                    'is_wajib' => $data['is_wajib'],
                    'target_peserta' => $data['target_peserta'] ?? null,
                    'target_tingkat' => $data['target_tingkat'] ?? null,
                    'target_jurusan' => $data['target_jurusan'] ?? null,
                    'target_siswa' => $data['target_siswa'] ?? null,
                    'target_gender' => $data['target_gender'] ?? null,
                    'tipe_jadwal' => $tipeJadwal,
                    'hari' => $hariNormalized,
                    'tanggal_mulai' => $request->input('jadwal_tanggal_mulai'),
                    'tanggal_selesai' => $request->input('jadwal_tanggal_selesai') ?: null,
                    'is_aktif' => true,
                ];

                if (is_null($jadwalId)) {
                    // Skenario 1: Buat JadwalKegiatan baru
                    $jadwalData['tahun_akademik_id'] = $kegiatan->tahun_akademik_id ?: (session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->first()?->id);
                    $jadwalData['qr_code_prefix'] = 'KGT-' . strtoupper(Str::random(5));
                    
                    $jadwal = JadwalKegiatan::create($jadwalData);
                    $data['jadwal_kegiatan_id'] = $jadwal->id;
                } else {
                    // Skenario 2: Update JadwalKegiatan existing
                    $jadwal = JadwalKegiatan::find($jadwalId);
                    if ($jadwal) {
                        $jadwal->update($jadwalData);
                    }
                    $data['jadwal_kegiatan_id'] = $jadwalId;
                }
            } else {
                // Skenario 3: is_recurring non-aktif & $kegiatan->jadwal_kegiatan_id tidak null
                if (!is_null($jadwalId)) {
                    $jadwal = JadwalKegiatan::find($jadwalId);
                    if ($jadwal) {
                        $jadwal->update(['is_aktif' => false]);
                    }
                }
                $data['jadwal_kegiatan_id'] = null;
            }

            // Hapus field request khusus yang tidak ada di tabel kegiatan agar tidak error saat update kegiatan
            $updatePayload = collect($data)->except([
                'is_recurring',
                'hari_pelaksanaan',
                'jadwal_tanggal_mulai',
                'jadwal_tanggal_selesai'
            ])->toArray();

            $kegiatan->update($updatePayload);
        });

        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('admin.kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}

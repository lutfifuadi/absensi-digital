<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EkskulRequest;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\ActivityLog;
use App\Services\EkskulService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EkskulController extends Controller
{
    public function __construct(
        private EkskulService $ekskulService
    ) {}

    /**
     * Daftar ekskul.
     */
    public function index()
    {
        $filters = request()->only(['kategori', 'status', 'search']);
        $ekskuls = $this->ekskulService->getAll($filters);

        return view('admin.ekskul.index', compact('ekskuls'));
    }

    /**
     * Form tambah ekskul.
     */
    public function create()
    {
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.ekskul.create', compact('guruOptions'));
    }

    /**
     * Simpan ekskul baru.
     */
    public function store(EkskulRequest $request)
    {
        try {
            $ekskul = $this->ekskulService->create($request->validated());

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('Gagal membuat ekskul', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Form edit ekskul.
     */
    public function edit($id)
    {
        $ekskul = $this->ekskulService->getById($id);
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.ekskul.edit', compact('ekskul', 'guruOptions'));
    }

    /**
     * Update data ekskul.
     */
    public function update(EkskulRequest $request, $id)
    {
        try {
            $ekskul = $this->ekskulService->update($id, $request->validated());

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Gagal memperbarui ekskul', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Hapus ekskul (soft delete).
     */
    public function destroy($id)
    {
        try {
            $this->ekskulService->delete($id);

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', 'Ekskul berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal menghapus ekskul. Silakan coba lagi.');
        }
    }

    /**
     * Toggle status aktif/nonaktif ekskul.
     */
    public function toggleStatus($id)
    {
        try {
            $ekskul = $this->ekskulService->toggleStatus($id);
            $statusText = $ekskul->status ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()
                ->route('admin.ekskul.index')
                ->with('success', "Ekskul \"{$ekskul->nama}\" berhasil {$statusText}.");
        } catch (\Exception $e) {
            Log::error('Gagal mengubah status ekskul', ['error' => $e->getMessage()]);

            return back()->with('error', 'Gagal mengubah status. Silakan coba lagi.');
        }
    }

    /**
     * Export master data ekstrakurikuler, jadwal, pembina, kegiatan, & anggota ke JSON file.
     */
    public function exportData()
    {
        $ekskuls = Ekskul::with(['jadwal', 'pembina.guru', 'kegiatan', 'anggota.siswa'])->get();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'app_name' => config('app.name', 'Aplikasi Presensi & Pelanggaran'),
            'ekskul' => $ekskuls->map(function ($e) {
                return [
                    'nama' => $e->nama,
                    'kategori' => $e->kategori,
                    'deskripsi' => $e->deskripsi,
                    'kuota' => (int) $e->kuota,
                    'status' => (bool) $e->status,
                    'icon' => $e->icon,
                    'jadwal' => $e->jadwal->map(function ($j) {
                        return [
                            'hari' => $j->hari,
                            'jam_mulai' => $j->jam_mulai,
                            'jam_selesai' => $j->jam_selesai,
                            'lokasi' => $j->lokasi,
                        ];
                    })->toArray(),
                    'pembina' => $e->pembina->map(function ($p) {
                        return [
                            'guru_nip' => $p->guru?->nip,
                            'guru_nama' => $p->guru?->nama_lengkap,
                            'jabatan' => $p->jabatan ?? 'Pembina Utama',
                        ];
                    })->toArray(),
                    'kegiatan' => $e->kegiatan->map(function ($k) {
                        return [
                            'nama_kegiatan' => $k->nama_kegiatan,
                            'tanggal' => $k->tanggal ? (is_string($k->tanggal) ? $k->tanggal : $k->tanggal->toDateString()) : null,
                            'deskripsi' => $k->deskripsi,
                        ];
                    })->toArray(),
                    'anggota' => $e->anggota->map(function ($a) {
                        return [
                            'siswa_nis' => $a->siswa?->nis,
                            'siswa_nama' => $a->siswa?->nama_lengkap,
                            'status' => $a->status ?? 'aktif',
                            'tanggal_masuk' => $a->tanggal_masuk ? (is_string($a->tanggal_masuk) ? $a->tanggal_masuk : $a->tanggal_masuk->toDateString()) : null,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        $filename = 'export_ekskul_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import master data ekstrakurikuler dari JSON file.
     */
    public function importData(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:5120',
        ], [
            'json_file.required' => 'File JSON data ekstrakurikuler wajib diunggah.',
            'json_file.mimes' => 'Format file harus berupa JSON.',
            'json_file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $content = file_get_contents($request->file('json_file')->getRealPath());
            $data = json_decode($content, true);

            if (!is_array($data) || (!isset($data['ekskul']) && !isset($data[0]['nama']))) {
                return redirect()->back()->with('error', 'Format file JSON ekstrakurikuler tidak valid.');
            }

            $ekskulItems = isset($data['ekskul']) ? $data['ekskul'] : $data;

            $importedEkskul = 0;
            $importedJadwal = 0;
            $importedPembina = 0;
            $importedKegiatan = 0;
            $importedAnggota = 0;

            DB::transaction(function () use ($ekskulItems, &$importedEkskul, &$importedJadwal, &$importedPembina, &$importedKegiatan, &$importedAnggota) {
                foreach ($ekskulItems as $item) {
                    if (empty($item['nama'])) continue;

                    // Validasi kategori enum
                    $allowedKategori = ['wajib', 'pilihan', 'olahraga', 'seni', 'akademik', 'lainnya'];
                    $kategori = in_array($item['kategori'] ?? '', $allowedKategori) ? $item['kategori'] : 'pilihan';

                    // 1. Create or Update Ekskul
                    $ekskul = Ekskul::updateOrCreate(
                        ['nama' => $item['nama']],
                        [
                            'kategori' => $kategori,
                            'deskripsi' => $item['deskripsi'] ?? null,
                            'kuota' => $item['kuota'] ?? 30,
                            'status' => $item['status'] ?? true,
                            'icon' => $item['icon'] ?? 'star',
                        ]
                    );
                    $importedEkskul++;

                    // 2. Import Jadwal
                    if (isset($item['jadwal']) && is_array($item['jadwal'])) {
                        DB::table('ekskul_jadwal')->where('ekskul_id', $ekskul->id)->delete();
                        foreach ($item['jadwal'] as $j) {
                            if (empty($j['hari'])) continue;
                            DB::table('ekskul_jadwal')->insert([
                                'ekskul_id' => $ekskul->id,
                                'hari' => strtolower($j['hari']),
                                'jam_mulai' => $j['jam_mulai'] ?? '14:00',
                                'jam_selesai' => $j['jam_selesai'] ?? '16:00',
                                'lokasi' => $j['lokasi'] ?? 'Area Sekolah',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $importedJadwal++;
                        }
                    }

                    // 3. Import Pembina (Match Guru by NIP/Nama)
                    if (isset($item['pembina']) && is_array($item['pembina'])) {
                        foreach ($item['pembina'] as $p) {
                            $guru = null;
                            if (!empty($p['guru_nip'])) {
                                $guru = Guru::where('nip', $p['guru_nip'])->first();
                            }
                            if (!$guru && !empty($p['guru_nama'])) {
                                $guru = Guru::where('nama_lengkap', $p['guru_nama'])->first();
                            }

                            if ($guru) {
                                DB::table('ekskul_pembina')->updateOrInsert(
                                    ['ekskul_id' => $ekskul->id, 'guru_id' => $guru->id],
                                    [
                                        'jabatan' => $p['jabatan'] ?? 'Pembina Utama',
                                        'updated_at' => now(),
                                        'created_at' => now(),
                                    ]
                                );
                                $importedPembina++;
                            }
                        }
                    }

                    // 4. Import Kegiatan
                    if (isset($item['kegiatan']) && is_array($item['kegiatan'])) {
                        foreach ($item['kegiatan'] as $k) {
                            if (empty($k['nama_kegiatan'])) continue;
                            DB::table('ekskul_kegiatan')->updateOrInsert(
                                ['ekskul_id' => $ekskul->id, 'nama_kegiatan' => $k['nama_kegiatan']],
                                [
                                    'tanggal' => $k['tanggal'] ?? now()->toDateString(),
                                    'deskripsi' => $k['deskripsi'] ?? null,
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                            $importedKegiatan++;
                        }
                    }

                    // 5. Import Anggota (Match Siswa by NIS/Nama)
                    if (isset($item['anggota']) && is_array($item['anggota'])) {
                        foreach ($item['anggota'] as $a) {
                            $siswa = null;
                            if (!empty($a['siswa_nis'])) {
                                $siswa = Siswa::where('nis', $a['siswa_nis'])->first();
                            }
                            if (!$siswa && !empty($a['siswa_nama'])) {
                                $siswa = Siswa::where('nama_lengkap', $a['siswa_nama'])->first();
                            }

                            if ($siswa) {
                                DB::table('ekskul_anggota')->updateOrInsert(
                                    ['ekskul_id' => $ekskul->id, 'siswa_id' => $siswa->id],
                                    [
                                        'status' => $a['status'] ?? 'aktif',
                                        'tanggal_masuk' => $a['tanggal_masuk'] ?? now()->toDateString(),
                                        'updated_at' => now(),
                                        'created_at' => now(),
                                    ]
                                );
                                $importedAnggota++;
                            }
                        }
                    }
                }
            });

            ActivityLog::record(
                'import',
                'ekskul',
                "Import {$importedEkskul} Ekskul, {$importedJadwal} Jadwal, {$importedPembina} Pembina, dan {$importedAnggota} Anggota Siswa."
            );

            return redirect()->route('admin.ekskul.index')
                ->with('success', "Berhasil mengimpor {$importedEkskul} Ekskul, {$importedJadwal} Jadwal, {$importedPembina} Pembina, dan {$importedAnggota} Anggota.");

        } catch (\Exception $e) {
            Log::error('Import Ekskul error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengimpor data ekstrakurikuler: ' . $e->getMessage());
        }
    }
}

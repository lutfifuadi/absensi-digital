<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranFoto;
use App\Models\PelanggaranSp;
use App\Models\PelanggaranNotifLog;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\ActivityLog;
use App\Services\PoinPelanggaranService;
use App\Jobs\SendPelanggaranWhatsAppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Exception;

class PelanggaranSiswaController extends Controller
{
    use AuthorizesRequests;

    protected $poinService;

    public function __construct(PoinPelanggaranService $poinService)
    {
        $this->poinService = $poinService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PelanggaranSiswa::class);

        // Ambil data penunjang filter
        $tahunAkademiks = TahunAkademik::orderBy('nama', 'desc')->get();
        $taAktif = TahunAkademik::where('is_aktif', true)->first();
        $tahunAkademikId = $request->input('tahun_akademik_id', $taAktif?->id);

        $kelas = Kelas::orderBy('nama', 'asc')->get();
        $kategoris = KategoriPelanggaran::orderBy('nama', 'asc')->get();

        // Query Utama dengan Eager Loading untuk mencegah N+1
        $query = PelanggaranSiswa::with([
            'siswa.kelas', 
            'jenisPelanggaran.kategori', 
            'tahunAkademik', 
            'pencatat',
            'fotos'
        ])->latest('tanggal_kejadian');

        // Filter Tahun Akademik
        if ($tahunAkademikId) {
            $query->where('tahun_akademik_id', $tahunAkademikId);
        }

        // Filter Pencarian (Nama/NIS Siswa)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter Kelas
        if ($request->filled('kelas_id')) {
            $kelasId = $request->input('kelas_id');
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        // Filter Kategori Pelanggaran
        if ($request->filled('kategori_id')) {
            $kategoriId = $request->input('kategori_id');
            $query->whereHas('jenisPelanggaran', function ($q) use ($kategoriId) {
                $q->where('kategori_id', $kategoriId);
            });
        }

        // Filter Bulan Kejadian
        if ($request->filled('bulan')) {
            $bulan = $request->input('bulan'); // Format Y-m
            $query->whereRaw("DATE_FORMAT(tanggal_kejadian, '%Y-%m') = ?", [$bulan]);
        }

        // Filter Level SP (Siswa yang terkena SP tingkat tertentu)
        if ($request->filled('level_sp')) {
            $levelSp = $request->input('level_sp');
            $query->whereHas('siswa.pelanggaranSp', function ($q) use ($levelSp, $tahunAkademikId) {
                $q->where('level_sp', $levelSp);
                if ($tahunAkademikId) {
                    $q->where('tahun_akademik_id', $tahunAkademikId);
                }
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        $pelanggarans = $query->paginate($perPage)->withQueryString();

        // Jika request AJAX, render partial table saja
        if ($request->ajax()) {
            return view('admin.pelanggaran.table', compact('pelanggarans'))->render();
        }

        return view('admin.pelanggaran.index', compact(
            'pelanggarans', 
            'tahunAkademiks', 
            'kelas', 
            'kategoris', 
            'tahunAkademikId'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', PelanggaranSiswa::class);

        $kategoris = KategoriPelanggaran::with('jenisPelanggaran')->orderBy('nama', 'asc')->get();
        $tahunAkademiks = TahunAkademik::orderBy('nama', 'desc')->get();
        $taAktif = TahunAkademik::where('is_aktif', true)->first();

        return view('admin.pelanggaran.create', compact('kategoris', 'tahunAkademiks', 'taAktif'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', PelanggaranSiswa::class);

        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jenis_id' => 'required|exists:pelanggaran_jenis,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'tanggal_kejadian' => 'required|date',
            'keterangan' => 'required|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
        ]);

        $jenis = JenisPelanggaran::findOrFail($request->jenis_id);

        try {
            $pelanggaran = DB::transaction(function () use ($request, $jenis) {
                // 1. Simpan record di tabel pelanggaran_siswa
                $pelanggaran = PelanggaranSiswa::create([
                    'siswa_id' => $request->siswa_id,
                    'jenis_id' => $request->jenis_id,
                    'tahun_akademik_id' => $request->tahun_akademik_id,
                    'tanggal_kejadian' => $request->tanggal_kejadian,
                    'keterangan' => $request->keterangan,
                    'poin_saat_itu' => $jenis->bobot_poin,
                    'dicatat_oleh' => Auth::id(),
                    'is_diarsipkan' => false,
                ]);

                // 2. Simpan foto bukti jika ada
                if ($request->hasFile('foto')) {
                    $file = $request->file('foto');
                    $filename = uniqid('pelanggaran_') . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan di private storage
                    $path = $file->storeAs('private/pelanggaran-foto', $filename);

                    PelanggaranFoto::create([
                        'pelanggaran_id' => $pelanggaran->id,
                        'path_foto' => $path,
                        'nama_file_asli' => $file->getClientOriginalName(),
                        'ukuran_byte' => $file->getSize(),
                        'created_at' => now(),
                    ]);
                }

                // 3. Rekalkulasi poin & trigger SP otomatis
                // Method checkAndTriggerSp() akan mengembalikan PelanggaranSp baru jika diterbitkan
                $spTerbaru = $this->poinService->checkAndTriggerSp(
                    $request->siswa_id, 
                    $request->tahun_akademik_id
                );

                // Taruh SP baru di objek pelanggaran untuk dibaca di luar transaksi
                $pelanggaran->sp_baru = $spTerbaru;

                return $pelanggaran;
            });

            // 4. Log Aktivitas
            $siswaObj = Siswa::find($request->siswa_id);
            ActivityLog::record(
                'create', 
                'pelanggaran_siswa', 
                "Mencatat pelanggaran untuk siswa {$siswaObj->nama_lengkap} (NIS: {$siswaObj->nis}) dengan poin +{$jenis->bobot_poin}",
                null,
                $pelanggaran->toArray()
            );

            // 5. Dispatch job WhatsApp notification untuk pelanggaran baru
            SendPelanggaranWhatsAppNotification::dispatch(
                $request->siswa_id,
                $pelanggaran->id,
                null,
                'pelanggaran_baru'
            );

            // 6. Jika ada SP baru yang diterbitkan, dispatch notifikasi SP
            if ($pelanggaran->sp_baru) {
                SendPelanggaranWhatsAppNotification::dispatch(
                    $request->siswa_id,
                    null,
                    $pelanggaran->sp_baru->id,
                    'sp_terbit'
                );
            }

            return redirect()->route('admin.pelanggaran.index')
                ->with('success', 'Catatan pelanggaran siswa berhasil disimpan dan notifikasi WhatsApp sedang diproses.');

        } catch (Exception $e) {
            Log::error("Gagal menyimpan pelanggaran siswa: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PelanggaranSiswa $pelanggaran)
    {
        $this->authorize('view', $pelanggaran);

        $pelanggaran->load([
            'siswa.kelas', 
            'jenisPelanggaran.kategori', 
            'tahunAkademik', 
            'pencatat',
            'fotos'
        ]);

        return view('admin.pelanggaran.show', compact('pelanggaran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PelanggaranSiswa $pelanggaran)
    {
        $this->authorize('update', $pelanggaran);

        $kategoris = KategoriPelanggaran::with('jenisPelanggaran')->orderBy('nama', 'asc')->get();
        $tahunAkademiks = TahunAkademik::orderBy('nama', 'desc')->get();

        return view('admin.pelanggaran.edit', compact('pelanggaran', 'kategoris', 'tahunAkademiks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PelanggaranSiswa $pelanggaran)
    {
        $this->authorize('update', $pelanggaran);

        $request->validate([
            'jenis_id' => 'required|exists:pelanggaran_jenis,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'tanggal_kejadian' => 'required|date',
            'keterangan' => 'required|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
        ]);

        $oldData = $pelanggaran->toArray();
        $jenis = JenisPelanggaran::findOrFail($request->jenis_id);

        try {
            DB::transaction(function () use ($request, $pelanggaran, $jenis) {
                // Update data pelanggaran
                $pelanggaran->update([
                    'jenis_id' => $request->jenis_id,
                    'tahun_akademik_id' => $request->tahun_akademik_id,
                    'tanggal_kejadian' => $request->tanggal_kejadian,
                    'keterangan' => $request->keterangan,
                    'poin_saat_itu' => $jenis->bobot_poin,
                ]);

                // Simpan foto bukti baru jika diupload
                if ($request->hasFile('foto')) {
                    // Hapus foto lama dari DB & disk
                    $oldFotos = PelanggaranFoto::where('pelanggaran_id', $pelanggaran->id)->get();
                    foreach ($oldFotos as $oldFoto) {
                        Storage::delete($oldFoto->path_foto);
                        $oldFoto->delete();
                    }

                    $file = $request->file('foto');
                    $filename = uniqid('pelanggaran_') . '.' . $file->getClientOriginalExtension();
                    
                    // Simpan di private storage
                    $path = $file->storeAs('private/pelanggaran-foto', $filename);

                    PelanggaranFoto::create([
                        'pelanggaran_id' => $pelanggaran->id,
                        'path_foto' => $path,
                        'nama_file_asli' => $file->getClientOriginalName(),
                        'ukuran_byte' => $file->getSize(),
                        'created_at' => now(),
                    ]);
                }

                // Jalankan rekalkulasi poin & trigger SP
                $this->poinService->recalculatePointsAndSp($pelanggaran->siswa_id, $pelanggaran->tahun_akademik_id);
            });

            // Log Aktivitas
            $siswaObj = Siswa::find($pelanggaran->siswa_id);
            ActivityLog::record(
                'update', 
                'pelanggaran_siswa', 
                "Mengubah data pelanggaran siswa {$siswaObj->nama_lengkap} (NIS: {$siswaObj->nis})",
                $oldData,
                $pelanggaran->fresh()->toArray()
            );

            return redirect()->route('admin.pelanggaran.index')
                ->with('success', 'Catatan pelanggaran siswa berhasil diperbarui.');

        } catch (Exception $e) {
            Log::error("Gagal mengupdate pelanggaran siswa: " . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memperbarui data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, PelanggaranSiswa $pelanggaran)
    {
        $this->authorize('delete', $pelanggaran);

        $request->validate([
            'alasan_penghapusan' => 'required|string|max:500'
        ]);

        $oldData = $pelanggaran->toArray();
        $siswaObj = Siswa::findOrFail($pelanggaran->siswa_id);
        $poinDihapus = $pelanggaran->poin_saat_itu;

        try {
            DB::transaction(function () use ($pelanggaran) {
                // Soft delete record pelanggaran
                $pelanggaran->delete();

                // Sesuai BR-08, jika poin turun setelah penghapusan, SP yang sudah telanjur diterbitkan TETAP berlaku.
                // recalculatePointsAndSp() akan menghitung ulang poin yang aktif dan mengabaikan record soft delete.
                $this->poinService->recalculatePointsAndSp($pelanggaran->siswa_id, $pelanggaran->tahun_akademik_id);
            });

            // Log Aktivitas dengan alasan penghapusan
            ActivityLog::record(
                'delete', 
                'pelanggaran_siswa', 
                "Menghapus pelanggaran (-{$poinDihapus} poin) siswa {$siswaObj->nama_lengkap} (NIS: {$siswaObj->nis}). Alasan: {$request->alasan_penghapusan}",
                $oldData,
                ['status' => 'deleted', 'alasan_penghapusan' => $request->alasan_penghapusan]
            );

            return redirect()->route('admin.pelanggaran.index')
                ->with('success', 'Catatan pelanggaran siswa berhasil dihapus dan poin akumulasi diperbarui.');

        } catch (Exception $e) {
            Log::error("Gagal menghapus pelanggaran siswa: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    /**
     * Streaming foto bukti secara privat dari private storage.
     */
    public function streamFoto($foto_id)
    {
        $foto = PelanggaranFoto::findOrFail($foto_id);
        
        // Autorisasi akses melihat pelanggaran terkait foto ini
        $pelanggaran = PelanggaranSiswa::findOrFail($foto->pelanggaran_id);
        $this->authorize('view', $pelanggaran);

        $path = $foto->path_foto;

        if (!Storage::exists($path)) {
            abort(404, 'File foto bukti tidak ditemukan.');
        }

        $file = Storage::get($path);
        $type = Storage::mimeType($path) ?: 'image/jpeg';

        return response($file, 200)->header('Content-Type', $type);
    }

    /**
     * AJAX: Search active students
     */
    public function searchSiswa(Request $request)
    {
        // Hanya user dengan otorisasi viewAny yang bisa akses
        if (!Auth::user()->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $search = $request->input('q');
        $taAktif = TahunAkademik::where('is_aktif', true)->first();
        $taId = $request->input('tahun_akademik_id', $taAktif?->id);

        if (empty($search)) {
            return response()->json([]);
        }

        $siswa = Siswa::with(['kelas', 'pelanggaranSp' => function ($q) use ($taId) {
            if ($taId) {
                $q->where('tahun_akademik_id', $taId);
            }
        }])
        ->where('status', 'aktif')
        ->where(function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
              ->orWhere('nis', 'like', "%{$search}%");
        })
        ->limit(10)
        ->get();

        $results = $siswa->map(function ($s) use ($taId) {
            // Hitung total poin
            $totalPoin = PelanggaranSiswa::where('siswa_id', $s->id)
                ->where('tahun_akademik_id', $taId)
                ->sum('poin_saat_itu');

            // Ambil SP tertinggi di tahun akademik ini
            $spTertinggi = $s->pelanggaranSp
                ->sortByDesc('level_sp')
                ->first();

            return [
                'id' => $s->id,
                'nama_lengkap' => $s->nama_lengkap,
                'nis' => $s->nis,
                'kelas_nama' => $s->kelas?->nama ?: 'Tidak Ada Kelas',
                'foto' => $s->foto ? asset('storage/foto-siswa/' . $s->foto) : asset('assets/img/avatars/1.png'),
                'total_poin' => (int) $totalPoin,
                'level_sp' => $spTertinggi ? $spTertinggi->level_sp : '-',
            ];
        });

        return response()->json($results);
    }

    /**
     * AJAX: Get points & level SP for a student
     */
    public function getSiswaPoin(Request $request, $id)
    {
        if (!Auth::user()->hasAnyRole([
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_PIKET,
        ])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $taAktif = TahunAkademik::where('is_aktif', true)->first();
        $taId = $request->input('tahun_akademik_id', $taAktif?->id);

        $siswa = Siswa::findOrFail($id);

        $totalPoin = PelanggaranSiswa::where('siswa_id', $siswa->id)
            ->where('tahun_akademik_id', $taId)
            ->sum('poin_saat_itu');

        $spTertinggi = PelanggaranSp::where('siswa_id', $siswa->id)
            ->where('tahun_akademik_id', $taId)
            ->orderBy('level_sp', 'desc')
            ->first();

        return response()->json([
            'total_poin' => (int) $totalPoin,
            'level_sp' => $spTertinggi ? $spTertinggi->level_sp : '-',
        ]);
    }

    /**
     * Export master data & catatan pelanggaran siswa ke file JSON
     */
    public function exportData(Request $request)
    {
        $this->authorize('viewAny', PelanggaranSiswa::class);

        $kategoris = KategoriPelanggaran::orderBy('urutan', 'asc')->get();
        $jenisPelanggarans = JenisPelanggaran::with('kategori')->get();
        $pelanggarans = PelanggaranSiswa::with(['siswa', 'jenisPelanggaran.kategori', 'pencatat'])->get();

        $exportData = [
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'app_name' => config('app.name', 'Aplikasi Presensi & Pelanggaran'),
            'kategori' => $kategoris->map(function ($k) {
                return [
                    'nama' => $k->nama,
                    'deskripsi' => $k->deskripsi,
                    'warna' => $k->warna,
                    'urutan' => $k->urutan,
                    'is_aktif' => (bool) $k->is_aktif,
                ];
            })->toArray(),
            'jenis' => $jenisPelanggarans->map(function ($j) {
                return [
                    'kategori_nama' => $j->kategori?->nama,
                    'nama' => $j->nama,
                    'deskripsi' => $j->deskripsi,
                    'bobot_poin' => (int) $j->bobot_poin,
                    'is_aktif' => (bool) $j->is_aktif,
                ];
            })->toArray(),
            'pelanggaran_siswa' => $pelanggarans->map(function ($p) {
                return [
                    'siswa_nis' => $p->siswa?->nis,
                    'siswa_nama' => $p->siswa?->nama_lengkap,
                    'jenis_nama' => $p->jenisPelanggaran?->nama,
                    'tanggal_kejadian' => $p->tanggal_kejadian ? $p->tanggal_kejadian->toDateString() : null,
                    'keterangan' => $p->keterangan,
                    'poin_saat_itu' => (int) $p->poin_saat_itu,
                    'pencatat_email' => $p->pencatat?->email,
                ];
            })->toArray(),
        ];

        $filename = 'export_pelanggaran_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import master data & catatan pelanggaran siswa dari file JSON
     */
    public function importData(Request $request)
    {
        $this->authorize('create', PelanggaranSiswa::class);

        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:5120',
        ], [
            'json_file.required' => 'File JSON data pelanggaran wajib diunggah.',
            'json_file.mimes' => 'Format file harus berupa JSON.',
            'json_file.max' => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $content = file_get_contents($request->file('json_file')->getRealPath());
            $data = json_decode($content, true);

            if (!is_array($data)) {
                return redirect()->back()->with('error', 'Format file JSON tidak valid.');
            }

            $taAktif = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::first();
            if (!$taAktif) {
                return redirect()->back()->with('error', 'Tahun akademik tidak ditemukan di sistem ini.');
            }

            $importedKategori = 0;
            $importedJenis = 0;
            $importedPelanggaran = 0;

            DB::transaction(function () use ($data, $taAktif, &$importedKategori, &$importedJenis, &$importedPelanggaran) {
                $kategoriMap = [];

                // 1. Import / Sync Kategori Pelanggaran
                if (isset($data['kategori']) && is_array($data['kategori'])) {
                    foreach ($data['kategori'] as $katData) {
                        if (empty($katData['nama'])) continue;

                        $kategori = KategoriPelanggaran::updateOrCreate(
                            ['nama' => $katData['nama']],
                            [
                                'deskripsi' => $katData['deskripsi'] ?? null,
                                'warna' => $katData['warna'] ?? '#7367f0',
                                'urutan' => $katData['urutan'] ?? 1,
                                'is_aktif' => $katData['is_aktif'] ?? true,
                            ]
                        );
                        $kategoriMap[$katData['nama']] = $kategori->id;
                        $importedKategori++;
                    }
                }

                // 2. Import / Sync Jenis Pelanggaran
                if (isset($data['jenis']) && is_array($data['jenis'])) {
                    foreach ($data['jenis'] as $jData) {
                        if (empty($jData['nama'])) continue;

                        $katId = null;
                        if (!empty($jData['kategori_nama']) && isset($kategoriMap[$jData['kategori_nama']])) {
                            $katId = $kategoriMap[$jData['kategori_nama']];
                        } else if (!empty($jData['kategori_nama'])) {
                            $kat = KategoriPelanggaran::where('nama', $jData['kategori_nama'])->first();
                            $katId = $kat ? $kat->id : null;
                        }

                        if (!$katId) {
                            $katId = KategoriPelanggaran::first()?->id;
                        }

                        if ($katId) {
                            JenisPelanggaran::updateOrCreate(
                                ['nama' => $jData['nama'], 'kategori_id' => $katId],
                                [
                                    'deskripsi' => $jData['deskripsi'] ?? null,
                                    'bobot_poin' => $jData['bobot_poin'] ?? 5,
                                    'is_aktif' => $jData['is_aktif'] ?? true,
                                ]
                            );
                            $importedJenis++;
                        }
                    }
                }

                // 3. Import / Sync Transaksi Pelanggaran Siswa
                if (isset($data['pelanggaran_siswa']) && is_array($data['pelanggaran_siswa'])) {
                    $pencatatId = Auth::id();

                    foreach ($data['pelanggaran_siswa'] as $pData) {
                        if (empty($pData['jenis_nama'])) continue;

                        // Match Siswa by NIS or Nama
                        $siswa = null;
                        if (!empty($pData['siswa_nis'])) {
                            $siswa = Siswa::where('nis', $pData['siswa_nis'])->first();
                        }
                        if (!$siswa && !empty($pData['siswa_nama'])) {
                            $siswa = Siswa::where('nama_lengkap', $pData['siswa_nama'])->first();
                        }

                        if (!$siswa) continue; // Skip if student not found

                        // Match Jenis Pelanggaran
                        $jenis = JenisPelanggaran::where('nama', $pData['jenis_nama'])->first();
                        if (!$jenis) continue;

                        $tanggal = !empty($pData['tanggal_kejadian']) ? $pData['tanggal_kejadian'] : now()->toDateString();
                        $keterangan = $pData['keterangan'] ?? 'Imported data pelanggaran';
                        $poin = $pData['poin_saat_itu'] ?? $jenis->bobot_poin;

                        // Check duplicate
                        $exists = PelanggaranSiswa::where('siswa_id', $siswa->id)
                            ->where('jenis_id', $jenis->id)
                            ->where('tanggal_kejadian', $tanggal)
                            ->where('keterangan', $keterangan)
                            ->exists();

                        if (!$exists) {
                            PelanggaranSiswa::create([
                                'siswa_id' => $siswa->id,
                                'jenis_id' => $jenis->id,
                                'tahun_akademik_id' => $taAktif->id,
                                'tanggal_kejadian' => $tanggal,
                                'keterangan' => $keterangan,
                                'poin_saat_itu' => $poin,
                                'dicatat_oleh' => $pencatatId,
                                'is_diarsipkan' => false,
                            ]);

                            // Re-calculate & trigger SP
                            $this->poinService->checkAndTriggerSp($siswa->id, $taAktif->id);
                            $importedPelanggaran++;
                        }
                    }
                }
            });

            ActivityLog::record(
                'import',
                'pelanggaran',
                "Import {$importedKategori} Kategori, {$importedJenis} Jenis, dan {$importedPelanggaran} Pelanggaran Siswa."
            );

            return redirect()->route('admin.pelanggaran.index')
                ->with('success', "Berhasil mengimpor {$importedKategori} Kategori, {$importedJenis} Jenis Pelanggaran, dan {$importedPelanggaran} Catatan Pelanggaran Siswa.");

        } catch (\Exception $e) {
            Log::error('Import Pelanggaran error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    /**
     * Reset / Hapus seluruh data transaksi pelanggaran siswa, foto bukti, dan SP.
     */
    public function resetData(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isRole(User::ROLE_ADMIN_SEKOLAH)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mereset data pelanggaran.');
        }

        try {
            DB::transaction(function () {
                // Hapus foto fisik dari storage
                $fotos = PelanggaranFoto::all();
                foreach ($fotos as $f) {
                    if ($f->path_foto && Storage::disk('public')->exists($f->path_foto)) {
                        Storage::disk('public')->delete($f->path_foto);
                    }
                }

                // Hapus data relasi transaksi
                PelanggaranFoto::query()->delete();
                PelanggaranNotifLog::query()->delete();
                PelanggaranSp::query()->delete();

                // Force delete seluruh catatan pelanggaran siswa
                PelanggaranSiswa::withTrashed()->forceDelete();
            });

            ActivityLog::record(
                'delete',
                'pelanggaran',
                'Mereset seluruh data transaksi pelanggaran siswa, foto bukti, dan Surat Peringatan (SP).'
            );

            return redirect()->route('admin.pelanggaran.index')
                ->with('success', 'Seluruh data transaksi pelanggaran siswa, foto bukti, dan Surat Peringatan (SP) berhasil direset.');

        } catch (\Exception $e) {
            Log::error('Reset Pelanggaran error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mereset data pelanggaran: ' . $e->getMessage());
        }
    }
}

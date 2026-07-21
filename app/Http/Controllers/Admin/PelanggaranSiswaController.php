<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranFoto;
use App\Models\PelanggaranSp;
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

        $pelanggarans = $query->paginate(15)->withQueryString();

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
}

<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaExport;
use App\Http\Controllers\Controller;
use App\Imports\SiswaImport;
use App\Jobs\GoogleSheetsSyncJob;
use App\Models\AbsensiSiswa;
use App\Models\ActivityLog;
use App\Models\GoogleSheetSetting;
use App\Models\IdCardTemplate;
use App\Models\IzinSakit;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\SiswaService;
use App\Services\GoogleDriveService;
use App\Services\IdCardPdfService;
use App\Support\QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SiswaController extends Controller
{
    protected $googleDriveService;

    public function __construct(protected SiswaService $siswaService, GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $siswa = Siswa::with(['kelas', 'tahunAkademik'])
            ->where('status', '!=', 'alumni')
            ->where(function ($q) use ($tahunAjaranId) {
                if ($tahunAjaranId) {
                    // Tampilkan siswa yang sesuai tahun ajaran ATAU yang tahun_akademik_id-nya NULL
                    // (karena kemungkinan di-sync/import sebelum fitur ini ada)
                    $q->where('tahun_akademik_id', $tahunAjaranId)
                        ->orWhereNull('tahun_akademik_id');
                }
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->paginate($perPage)
            ->withQueryString();

        // Deteksi siswa dengan tahun_akademik_id NULL untuk notifikasi admin
        $siswaNullTahun = $tahunAjaranId
            ? Siswa::whereNull('tahun_akademik_id')->where('status', '!=', 'alumni')->count()
            : 0;

        if ($request->ajax()) {
            return view('admin.siswa.table', compact('siswa'))->render();
        }

        $tahunAjaranOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.siswa.index', compact('siswa', 'tahunAjaranOptions', 'siswaNullTahun'));
    }

    public function create()
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)
            ->orderBy('nama')
            ->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.siswa.form', compact('kelasOptions', 'tahunAkademikOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nis' => 'nullable|string|max:50|unique:siswa,nis',
            'nisn' => 'required|string|max:50|unique:siswa,nisn',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:50',
            'no_hp_ortu' => 'required|string|max:50',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'status' => 'required|in:aktif,nonaktif,alumni',
            'foto' => 'nullable|image|max:2048',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $identifier = strtolower(trim($data['nisn'] ?? $data['nis'] ?? ''));
        $email = ($identifier ?: 'siswa').'@'.$domainEmail;
        $username = $data['nisn'];

        $user = User::firstOrCreate(
            ['username' => $username],
            [
                'name' => $data['nama_lengkap'],
                'email' => $email,
                'password' => Hash::make($data['nisn'] ?? $data['nis'] ?? 'password123'),
                'role' => User::ROLE_SISWA,
            ]
        );

        // Auto-create parent user
        $emailOrtu = 'ortu.'.$identifier.'@'.$domainEmail;
        $usernameOrtu = 'ortu.'.$identifier;
        $userOrtu = User::firstOrCreate(
            ['username' => $usernameOrtu],
            [
                'name' => 'Wali Murid '.$data['nama_lengkap'],
                'email' => $emailOrtu,
                'password' => Hash::make($data['nisn'] ?? $data['nis'] ?? 'password123'),
                'role' => User::ROLE_ORANG_TUA,
            ]
        );

        if ($request->hasFile('foto')) {
            $fileId = $this->googleDriveService->uploadPhoto($request->file('foto'));
            $data['foto'] = $fileId;
        }

        $siswa = Siswa::create(array_merge($data, [
            'qr_code' => $data['nisn'],
            'user_id' => $user->id,
            'ortu_user_id' => $userOrtu->id,
        ]));

        // Sync to pivot table siswa_ortu
        $siswa->ortu()->syncWithoutDetaching([$userOrtu->id]);

        ActivityLog::record('create', 'siswa', "Tambah siswa: {$siswa->nama_lengkap} (NIS: {$siswa->nis})", null, $siswa->toArray());

        session(['tahun_ajaran_id' => $siswa->tahun_akademik_id, 'tahun_akademik_id' => $siswa->tahun_akademik_id]);

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function import()
    {
        return redirect()->route('admin.siswa.index');
    }

    public function importStore(Request $request)
    {
        // Validasi file dan ukurannya (max 20MB = 20480KB)
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480',
        ]);

        $originalFileName = $request->file('import_file')->getClientOriginalName();

        // Hitung total baris dari file (kurangi 1 untuk header)
        try {
            $reader = IOFactory::createReaderForFile($request->file('import_file'));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($request->file('import_file'));
            $totalRows = $spreadsheet->getActiveSheet()->getHighestRow() - 1;
            $spreadsheet->disconnectWorksheets();
        } catch (\Exception $e) {
            Log::error('Gagal membaca total baris file excel untuk progress', [
                'file' => $originalFileName,
                'error' => $e->getMessage(),
            ]);
            $totalRows = 0;
        }

        // Set timeout dinamis: 300 detik + 0.5 detik per baris (untuk file besar)
        $timeout = max(300, (int) ($totalRows * 0.5));
        set_time_limit($timeout);
        ini_set('max_execution_time', (string) $timeout);

        // Set memory limit lebih tinggi
        ini_set('memory_limit', '512M');

        cache()->put('siswa_import_progress', 0);
        cache()->put('siswa_import_total', max($totalRows, 0));
        cache()->put('siswa_import_status', 'processing');

        // Gunakan session_write_close() agar request polling tidak terblokir (TANPA menghapus session)
        session_write_close();

        Log::info('Import siswa dimulai oleh User ID: '.(Auth::id() ?? 'System').' dengan file: '.$originalFileName, [
            'total_rows' => $totalRows,
            'timeout' => $timeout,
        ]);

        try {
            $import = new SiswaImport;
            $import->setFileName($originalFileName);

            Excel::import($import, $request->file('import_file'));

            cache()->put('siswa_import_progress', $totalRows);
            cache()->put('siswa_import_status', 'completed');

            $result = $import->getImportResult();

            Log::info('Import siswa selesai', [
                'file' => $originalFileName,
                'success_count' => $result['success'],
                'failed_count' => $result['failed'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "Import selesai. {$result['success']} baris berhasil".
                    ($result['failed'] > 0 ? ", {$result['failed']} baris gagal." : '.'),
                'success_count' => $result['success'],
                'failed_count' => $result['failed'],
                'errors' => $result['errors'],
            ]);
        } catch (ExcelValidationException $exception) {
            cache()->put('siswa_import_status', 'failed');
            Log::warning('Import validasi error (Maatwebsite)', [
                'file' => $originalFileName,
                'failures' => $exception->failures(),
            ]);

            $failures = [];
            foreach ($exception->failures() as $failure) {
                $failures[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                ];
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi file gagal. Silakan periksa data Anda.',
                'validation_errors' => $failures,
            ], 422);
        } catch (\PhpOffice\PhpSpreadsheet\Exception $exception) {
            cache()->put('siswa_import_status', 'failed');
            Log::error('Import file corrupt atau tidak dapat dibaca', [
                'file' => $originalFileName,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'File Excel tidak dapat dibaca. Pastikan file tidak corrupt.',
            ], 400);
        } catch (QueryException $exception) {
            cache()->put('siswa_import_status', 'failed');
            Log::error('Import DB QueryException', [
                'file' => $originalFileName,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan database. Silakan hubungi developer.',
            ], 500);
        } catch (\Error $exception) {
            cache()->put('siswa_import_status', 'failed');
            Log::error('Import fatal error (memory limit?)', [
                'file' => $originalFileName,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Memori server tidak cukup untuk memproses file ini. Coba bagi file menjadi lebih kecil.',
            ], 500);
        } catch (\Throwable $exception) {
            cache()->put('siswa_import_status', 'failed');
            Log::error('Import unknown error', [
                'file' => $originalFileName,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan yang tidak terduga: '.$exception->getMessage(),
            ], 500);
        }
    }

    public function importProgress()
    {
        return response()->json([
            'progress' => (int) cache()->get('siswa_import_progress', 0),
            'total' => (int) cache()->get('siswa_import_total', 0),
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->query('search');
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));
        $format = $request->query('format', 'xlsx');

        $filename = 'data_siswa_'.now()->format('Y-m-d_H-i-s');

        if ($format === 'csv') {
            return Excel::download(new SiswaExport($search, $tahunAjaranId), $filename.'.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new SiswaExport($search, $tahunAjaranId), $filename.'.xlsx');
    }

    public function downloadSample()
    {
        // Ambil data aktual dari DB untuk contoh yang akurat
        $namaKelasContoh = Kelas::orderBy('nama')->value('nama') ?? 'X.E-1';
        $tahunAkademikContoh = TahunAkademik::where('is_aktif', true)->first()
            ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
        $tahunContoh = $tahunAkademikContoh
            ? $tahunAkademikContoh->nama.' '.ucfirst($tahunAkademikContoh->semester)
            : '2025-2026 Genap';

        $headers = [
            'nis',
            'nisn',
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'alamat',
            'no_hp',
            'no_hp_ortu',
            'kelas',
            'tahun_ajaran',   // format: "2025-2026 Genap" atau "2025-2026 Ganjil"
            'status',
        ];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sampel Import Siswa');

        // Header
        $sheet->fromArray($headers, null, 'A1');

        // Set header bold
        $sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex(count($headers)).'1')->getFont()->setBold(true);

        // Data baris 1 (L)
        $sheet->fromArray([
            '12345', '0012345678', 'Ahmad Siswa Sampel', 'L', 'Jakarta', '01/01/2010',
            'Jl. Merdeka No. 1', '08123456789', '08123456780', $namaKelasContoh, $tahunContoh, 'aktif',
        ], null, 'A2');

        // Data baris 2 (P)
        $sheet->fromArray([
            '12346', '0012345679', 'Siti Siswi Sampel', 'P', 'Bandung', '15/06/2010',
            'Jl. Sudirman No. 5', '', '08198765432', $namaKelasContoh, $tahunContoh, 'aktif',
        ], null, 'A3');

        // FORMAT SEMUA KOLOM SEBAGAI TEXT (kunci utama agar NISN panjang tidak berubah scientific notation)
        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A2:{$lastColumn}3")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        // Auto-size kolom
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Write to output
        $writer = new Xlsx($spreadsheet);
        $fileName = 'sampel_import_siswa.xlsx';

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function edit(Siswa $siswa)
    {
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)
            ->orderBy('nama')
            ->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.siswa.form', compact('siswa', 'kelasOptions', 'tahunAkademikOptions'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'nis' => 'nullable|string|max:50|unique:siswa,nis,'.$siswa->id,
            'nisn' => 'required|string|max:50|unique:siswa,nisn,'.$siswa->id,
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'nullable|string',
            'no_hp' => 'nullable|string|max:50',
            'no_hp_ortu' => 'required|string|max:50',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'status' => 'required|in:aktif,nonaktif,alumni',
            'foto' => 'nullable|image|max:2048',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $identifier = strtolower(trim($data['nisn']));
        $email = $identifier.'@'.$domainEmail;
        $username = $data['nisn'];

        if ($request->hasFile('foto')) {
            // check if there is an old photo and pass it for deletion
            $oldFileId = (strlen($siswa->foto) > 30) ? $siswa->foto : null;
            $newFileId = $this->googleDriveService->uploadPhoto($request->file('foto'), $oldFileId);
            $data['foto'] = $newFileId;
        }

        $old = $siswa->toArray();
        $siswa->update(array_merge($data, [
            'qr_code' => $data['nisn'],
        ]));

        if ($siswa->user) {
            $siswa->user->update([
                'name' => $data['nama_lengkap'],
                'username' => $username,
                'email' => $email,
            ]);
        }

        $parentUser = $siswa->ortu_user_id ? User::find($siswa->ortu_user_id) : null;
        if ($parentUser) {
            $parentUser->update([
                'name' => 'Wali Murid '.$data['nama_lengkap'],
                'username' => 'ortu.'.$identifier,
                'email' => 'ortu.'.$identifier.'@'.$domainEmail,
            ]);
            $siswa->ortu()->syncWithoutDetaching([$siswa->ortu_user_id]);
        } else {
            $emailOrtu = 'ortu.'.$identifier.'@'.$domainEmail;
            $usernameOrtu = 'ortu.'.$identifier;
            $userOrtu = User::firstOrCreate(
                ['username' => $usernameOrtu],
                [
                    'name' => 'Wali Murid '.$data['nama_lengkap'],
                    'email' => $emailOrtu,
                    'password' => Hash::make($data['nisn'] ?? $data['nis'] ?? 'password123'),
                    'role' => User::ROLE_ORANG_TUA,
                    'roles' => [User::ROLE_ORANG_TUA],
                ]
            );
            $siswa->update(['ortu_user_id' => $userOrtu->id]);
            $siswa->ortu()->syncWithoutDetaching([$userOrtu->id]);
        }

        ActivityLog::record('update', 'siswa', "Update siswa: {$siswa->nama_lengkap} (NIS: {$siswa->nis})", $old, $siswa->fresh()->toArray());

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil diperbarui.');
    }

    public function destroyAll(Request $request)
    {
        try {
            $deletedCount = 0;
            $deletedUserIds = [];
            $deletedOrtuUserIds = [];
            $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

            $query = Siswa::with(['user', 'ortu']);
            if ($tahunAjaranId) {
                $query->where('tahun_akademik_id', $tahunAjaranId);
            }

            $query->chunkById(100, function ($siswaBatch) use (&$deletedCount, &$deletedUserIds, &$deletedOrtuUserIds) {
                foreach ($siswaBatch as $siswa) {
                    $user = $siswa->user;
                    $siswa->delete(); // This triggers Siswa model observers (absensi, etc.)
                    $deletedCount++;

                    if ($user) {
                        $deletedUserIds[] = $user->id;
                        $user->delete();
                    }

                    // Kumpulkan user orang tua untuk dihapus setelah batch
                    foreach ($siswa->ortu as $ortu) {
                        $deletedOrtuUserIds[] = $ortu->id;
                    }
                }
            });

            // Hapus user orang tua yang sudah dikumpulkan (unique)
            $deletedOrtuUserIds = array_unique($deletedOrtuUserIds);
            if (! empty($deletedOrtuUserIds)) {
                User::whereIn('id', $deletedOrtuUserIds)->chunkById(100, function ($ortuUsers) {
                    foreach ($ortuUsers as $ortuUser) {
                        $ortuUser->delete();
                    }
                });
            }

            // Optional: Hapus akun user siswa yang mungkin sudah tidak punya entitas Siswa terkait
            // Hanya lakukan jika tidak memfilter per tahun ajaran, atau sesuaikan logikanya
            if (! $tahunAjaranId) {
                User::where('role', User::ROLE_SISWA)
                    ->whereDoesntHave('siswa')
                    ->chunkById(100, function ($users) {
                        foreach ($users as $user) {
                            $user->delete();
                        }
                    });

                // Reset auto increment hanya jika benar-benar menghapus SEMUA (tanpa filter)
                DB::statement('ALTER TABLE siswa AUTO_INCREMENT = 1');
            }

            $userCount = count($deletedUserIds);
            $ortuCount = count($deletedOrtuUserIds);
            ActivityLog::record('delete', 'siswa', "Hapus semua siswa ({$deletedCount} siswa, {$userCount} user siswa, {$ortuCount} user orang tua)", null, ['count' => $deletedCount]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deletedCount} siswa.",
                ]);
            }

            return redirect()->route('admin.siswa.index')->with('success', "Berhasil menghapus {$deletedCount} siswa.");
        } catch (\Throwable $e) {
            \Log::error('Error di destroyAll siswa: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function destroy(Request $request, Siswa $siswa)
    {
        $user = $siswa->user;
        $nama = $siswa->nama_lengkap;
        $nis = $siswa->nis;

        ActivityLog::record('delete', 'siswa', "Hapus siswa: {$nama} (NIS: {$nis})", $siswa->toArray(), null);
        $siswa->delete();

        if ($user) {
            $user->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Siswa {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }

    public function profilSaya()
    {
        $user = Auth::user();
        $siswa = $user->siswa;

        if (! $siswa) {
            abort(404, 'Profil siswa Anda tidak ditemukan.');
        }

        return $this->profil($siswa);
    }

    public function cetakQrKelas(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'))
            ?? TahunAkademik::where('is_aktif', true)->value('id');

        $kelasOptions = Kelas::where('tahun_akademik_id', $tahunAjaranId)
            ->orderBy('nama')
            ->get();

        if (! $kelasId) {
            return view('admin.siswa.cetak-qr-pilih', compact('kelasOptions'));
        }

        if ($kelasId === 'semua') {
            $siswaList = Siswa::with(['kelas', 'tahunAkademik'])
                ->where('status', 'aktif')
                ->where(function ($q) use ($tahunAjaranId) {
                    if ($tahunAjaranId) {
                        $q->where('tahun_akademik_id', $tahunAjaranId);
                    }
                })
                ->orderBy('nama_lengkap')
                ->get();
            $namaKelas = 'Semua Kelas';
        } else {
            $kelas = Kelas::findOrFail($kelasId);
            $siswaList = Siswa::with(['kelas', 'tahunAkademik'])
                ->where('kelas_id', $kelasId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->get();
            $namaKelas = $kelas->nama;
        }

        $template = IdCardTemplate::where('type', 'siswa')->active()->first();

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuSiswa($siswaList, $template, "kartu-pelajar-{$namaKelas}");
        } catch (\Exception $e) {
            Log::error('Gagal cetak kartu siswa: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    /**
     * Generate & download kartu QR untuk satu siswa.
     */
    public function generateQrSatu(Siswa $siswa)
    {
        $this->ensureQrCode($siswa);
        $siswa->load(['kelas', 'tahunAkademik']);

        $template = IdCardTemplate::where('type', 'siswa')->active()->first();

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuSiswa(collect([$siswa]), $template, "kartu-pelajar-{$siswa->nisn}");
        } catch (\Exception $e) {
            Log::error('Gagal generate kartu siswa: ' . $e->getMessage());
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    private function ensureQrCode(Siswa $siswa): void
    {
        if ($siswa->qr_code) {
            return;
        }

        $fallback = $siswa->nisn ?: QrCodeGenerator::generate('SISWA');
        $siswa->update(['qr_code' => $fallback]);
        $siswa->refresh();

        logger()->warning('Missing qr_code detected for siswa; generated fallback.', [
            'siswa_id' => $siswa->id,
            'nisn' => $siswa->nisn,
            'new_qr_code' => $fallback,
        ]);
    }

    /**
     * Pindah kelas siswa dalam tahun ajaran yang sama.
     */
    public function pindahKelas(Request $request, Siswa $siswa)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
        ], [
            'kelas_id.required' => 'Kelas tujuan wajib dipilih.',
            'kelas_id.exists' => 'Kelas tujuan tidak ditemukan.',
        ]);

        try {
            $this->siswaService->pindahKelas($siswa, (int) $request->kelas_id);

            return back()->with('success', "Siswa {$siswa->nama_lengkap} berhasil dipindahkan ke kelas tujuan.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Naik kelas siswa ke tahun ajaran baru.
     */
    public function naikKelas(Request $request, Siswa $siswa)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'tahun_akademik_id' => 'required|integer|exists:tahun_akademik,id',
        ], [
            'kelas_id.required' => 'Kelas tujuan wajib dipilih.',
            'kelas_id.exists' => 'Kelas tujuan tidak ditemukan.',
            'tahun_akademik_id.required' => 'Tahun akademik tujuan wajib dipilih.',
            'tahun_akademik_id.exists' => 'Tahun akademik tujuan tidak ditemukan.',
        ]);

        try {
            $this->siswaService->naikKelas(
                $siswa,
                (int) $request->kelas_id,
                (int) $request->tahun_akademik_id
            );

            return back()->with('success', "Siswa {$siswa->nama_lengkap} berhasil dinaikkan kelas.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Halaman profil detail siswa (Stats & History).
     */
    public function profil(Siswa $siswa)
    {
        $user = auth()->user();

        // Security check: if student, can only see own profile
        if ($user && $user->role === 'siswa') {
            if ($siswa->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke profil siswa lain.');
            }
        }

        $siswa->load(['kelas.waliKelas', 'tahunAkademik']);

        // Riwayat absensi paginated
        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->orderByDesc('tanggal')
            ->paginate(15);

        // Riwayat izin/sakit
        $izinSakit = IzinSakit::where('tipe', 'siswa')
            ->where('reference_id', $siswa->id)
            ->orderByDesc('created_at')
            ->get();

        // Statistik ringkasan
        $statsRaw = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'hadir' => $statsRaw['hadir'] ?? 0,
            'sakit' => $statsRaw['sakit'] ?? 0,
            'izin' => $statsRaw['izin'] ?? 0,
            'alpha' => $statsRaw['alpha'] ?? 0,
            'terlambat' => $statsRaw['terlambat'] ?? 0,
            'total' => array_sum($statsRaw) ?: 1, // avoid div zero
        ];

        // QR Code for display
        $this->ensureQrCode($siswa);
        $qrImage = QrCodeGenerator::renderDataUri($siswa->qr_code, 150);

        // Data untuk modal Pindah Kelas & Naik Kelas
        $kelasOptions = Kelas::with('tahunAkademik')->orderBy('nama')->get();
        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.siswa.profil', compact(
            'siswa', 'absensi', 'izinSakit', 'stats', 'qrImage',
            'kelasOptions', 'tahunAkademikOptions'
        ));
    }

    public function generateOrtuMassal(Request $request)
    {
        // 1. Get the list of student IDs who don't have a parent account yet
        if ($request->has('get_ids')) {
            try {
                $siswaIds = Siswa::whereNull('ortu_user_id')
                    ->where(function($q) {
                        $q->whereNotNull('nisn')->orWhereNotNull('nis');
                    })
                    ->pluck('id');

                return response()->json([
                    'success' => true,
                    'ids' => $siswaIds
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil data siswa: ' . $e->getMessage()
                ], 500);
            }
        }

        // 2. Process batch of student IDs
        $siswaIds = $request->input('siswa_ids');
        DB::beginTransaction();
        try {
            // Ambil domain dari pengaturan atau default
            $domain = Pengaturan::where('key', 'website_lembaga')->value('value');
            if ($domain && filter_var($domain, FILTER_VALIDATE_URL)) {
                $domain = parse_url($domain, PHP_URL_HOST);
            }
            if (!$domain) {
                $domain = 'madrasah.sch.id';
            }

            // If siswa_ids parameter is provided, only process those. Otherwise process all.
            if ($siswaIds) {
                $siswaTanpaOrtu = Siswa::whereIn('id', $siswaIds)->whereNull('ortu_user_id')->get();
            } else {
                $siswaTanpaOrtu = Siswa::whereNull('ortu_user_id')->get();
            }

            $jumlahOrtuDibuat = 0;
            $jumlahSiswaDiupdate = 0;

            foreach ($siswaTanpaOrtu as $siswa) {
                $identifier = $siswa->nisn ?? $siswa->nis;
                if (!$identifier) {
                    continue;
                }

                $username = 'ortu.' . $identifier;
                $email = 'ortu.' . $identifier . '@' . $domain;
                $namaWali = 'Wali Murid ' . $siswa->nama_lengkap;
                $password = $siswa->nisn ? Hash::make($siswa->nisn) : Hash::make('password123');

                $ortu = User::where('username', $username)->first();

                if (!$ortu) {
                    $ortu = User::create([
                        'name' => $namaWali,
                        'email' => $email,
                        'username' => $username,
                        'password' => $password,
                        'is_active' => true,
                        'role' => User::ROLE_ORANG_TUA,
                        'roles' => [User::ROLE_ORANG_TUA],
                        'no_hp' => $siswa->no_hp_ortu,
                    ]);
                    $jumlahOrtuDibuat++;
                } else {
                    if (empty($ortu->no_hp) && !empty($siswa->no_hp_ortu)) {
                        $ortu->update([
                            'no_hp' => $siswa->no_hp_ortu
                        ]);
                    }
                }

                $siswa->ortu_user_id = $ortu->id;
                $siswa->save();

                $siswa->ortu()->syncWithoutDetaching([$ortu->id]);
                $jumlahSiswaDiupdate++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'created' => $jumlahOrtuDibuat,
                'updated' => $jumlahSiswaDiupdate,
                'message' => "Berhasil memproses " . count($siswaTanpaOrtu) . " siswa."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat generate akun: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncGoogleSheet(Request $request)
    {
        $setting = GoogleSheetSetting::first();

        if (! $setting || ! $setting->is_active) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi Google Sheets belum diatur atau tidak aktif.']);
        }

        if (empty($setting->credentials_json)) {
            return response()->json(['success' => false, 'message' => 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.']);
        }

        if ($setting->last_sync_status === 'in_progress') {
            return response()->json(['success' => false, 'message' => 'Sinkronisasi sedang berlangsung. Tunggu hingga selesai.']);
        }

        $setting->update([
            'last_sync_status' => 'in_progress',
            'last_sync_message' => 'Menjadwalkan sinkronisasi...',
            'sync_total_rows' => 0,
            'sync_processed_rows' => 0,
            'sync_offset' => 0,
        ]);

        try {
            GoogleSheetsSyncJob::dispatch($setting->id, 0);

            Log::info('Sinkronisasi Google Sheets dijadwalkan dari halaman siswa.');

            return response()->json([
                'success' => true,
                'message' => 'Sinkronisasi Google Sheets telah dijadwalkan dan akan diproses di latar belakang. Proses akan berlanjut meskipun halaman ditutup.',
            ]);
        } catch (\Exception $e) {
            $setting->update([
                'last_sync_status' => 'failed',
                'last_sync_message' => 'Gagal menjadwalkan: '.$e->getMessage(),
            ]);

            Log::error('Gagal menjadwalkan sync Google Sheets dari halaman siswa.', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Gagal menjadwalkan sinkronisasi: '.$e->getMessage()]);
        }
    }

    public function naikKelasMassalPage(Request $request)
    {
        $tahunAkademikAsalId = $request->query('tahun_akademik_asal');
        $tahunAkademikTujuanId = $request->query('tahun_akademik_tujuan');
        $preview = null;

        if ($tahunAkademikAsalId && $tahunAkademikTujuanId) {
            try {
                $preview = $this->siswaService->previewNaikKelasMassal(
                    (int) $tahunAkademikAsalId,
                    (int) $tahunAkademikTujuanId
                );
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $tahunAkademikOptions = TahunAkademik::orderBy('tanggal_mulai', 'desc')->get();

        return view('admin.siswa.naik-kelas-massal', compact(
            'tahunAkademikOptions',
            'tahunAkademikAsalId',
            'tahunAkademikTujuanId',
            'preview'
        ));
    }

    public function naikKelasMassalExecute(Request $request)
    {
        $request->validate([
            'tahun_akademik_asal' => 'required|integer|exists:tahun_akademik,id',
            'tahun_akademik_tujuan' => 'required|integer|exists:tahun_akademik,id|different:tahun_akademik_asal',
        ], [
            'tahun_akademik_asal.required' => 'Tahun akademik asal wajib dipilih.',
            'tahun_akademik_tujuan.required' => 'Tahun akademik tujuan wajib dipilih.',
            'tahun_akademik_tujuan.different' => 'Tahun akademik tujuan harus berbeda dari tahun akademik asal.',
        ]);

        try {
            $result = $this->siswaService->naikKelasMassal(
                (int) $request->tahun_akademik_asal,
                (int) $request->tahun_akademik_tujuan
            );

            return redirect()
                ->route('admin.siswa.naik-kelas-massal', [
                    'tahun_akademik_asal' => $request->tahun_akademik_asal,
                    'tahun_akademik_tujuan' => $request->tahun_akademik_tujuan,
                ])
                ->with('naik_kelas_result', $result)
                ->with('success', "Naik kelas massal berhasil. {$result['success']} siswa diproses.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.siswa.naik-kelas-massal')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Error naik kelas massal: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->route('admin.siswa.naik-kelas-massal')->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function syncProgress()
    {
        $setting = GoogleSheetSetting::first();
        if (! $setting) {
            return response()->json(['status' => 'idle', 'total' => 0, 'processed' => 0, 'message' => '']);
        }

        return response()->json([
            'status' => $setting->last_sync_status,
            'total' => $setting->sync_total_rows ?? 0,
            'processed' => $setting->sync_processed_rows ?? 0,
            'message' => $setting->last_sync_message ?? '',
        ]);
    }
}

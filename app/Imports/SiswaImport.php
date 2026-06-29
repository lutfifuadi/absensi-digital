<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SiswaImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
     * Cache kelas & tahun akademik agar tidak query DB per baris.
     */
    protected array $kelasCache = [];

    protected array $tahunAkademikCache = [];

    protected ?string $domainEmail = null;

    protected int $rowCount = 0;

    protected string $fileName = 'siswa_import';

    protected array $importErrors = [];

    protected int $successCount = 0;

    protected int $failedCount = 0;

    /**
     * Set file name for logging context.
     */
    public function setFileName(string $name): void
    {
        $this->fileName = $name;
    }

    /**
     * Get summary import results.
     */
    public function getImportResult(): array
    {
        return [
            'success' => $this->successCount,
            'failed' => $this->failedCount,
            'errors' => $this->importErrors,
        ];
    }

    public function model(array $row)
    {
        $this->rowCount++;
        
        // Update progress in cache for every single row
        cache()->put('siswa_import_progress', $this->rowCount);
        cache()->put('siswa_import_status', 'processing');

        try {
            // ── Resolve Kelas ────────────────────────────────────────────────────
            $namaKelas = trim($row['kelas'] ?? '');
            if (! isset($this->kelasCache[$namaKelas])) {
                $this->kelasCache[$namaKelas] = Kelas::whereRaw('TRIM(nama) = ?', [$namaKelas])->first();
            }
            $kelas = $this->kelasCache[$namaKelas];

            if (! $kelas) {
                throw new \Exception("Kelas '{$namaKelas}' tidak ditemukan. Cek daftar kelas di master data.");
            }

            // ── Resolve Tahun Akademik ────────────────────────────────────────────
            $tahunAkademikRaw = trim($row['tahun_ajaran'] ?? $row['tahun_akademik'] ?? '');
            $tahunAkademikId = $this->resolveTahunAkademik($tahunAkademikRaw, $kelas);

            // ── Tanggal Lahir ────────────────────────────────────────────────────
            $tanggalLahir = $this->parseTanggalLahir($row['tanggal_lahir'] ?? null);

            // ── Auto-create akun User siswa ───────────────────────────────────────
            if ($this->domainEmail === null) {
                $this->domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
            }
            $nisn = isset($row['nisn']) ? trim((string) $row['nisn']) : '';
            $nis = isset($row['nis']) ? trim((string) $row['nis']) : '';
            
            if (empty($nisn)) {
                throw new \Exception("Kolom NISN wajib diisi.");
            }

            $identifier = strtolower($nisn ?: $nis);
            $email = $identifier.'@'.$this->domainEmail;
            $username = $nisn ?: $nis;

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name' => trim($row['nama_lengkap']),
                    'email' => $email,
                    'password' => Hash::make($nisn ?: $nis),
                    'role' => User::ROLE_SISWA,
                ]
            );

            // Auto-create akun orang tua
            $usernameOrtu = 'ortu.'.$identifier;
            $emailOrtu = 'ortu.'.$identifier.'@'.$this->domainEmail;
            $userOrtu = User::firstOrCreate(
                ['username' => $usernameOrtu],
                [
                    'name' => 'Wali Murid '.trim($row['nama_lengkap']),
                    'email' => $emailOrtu,
                    'password' => Hash::make($nisn ?: $nis),
                    'role' => User::ROLE_ORANG_TUA,
                ]
            );

            // ── Upsert Siswa (berdasarkan NISN agar idempotent) ─────────────────
            $siswa = Siswa::updateOrCreate(
                ['nisn' => $nisn],
                [
                    'user_id' => $user->id,
                    'ortu_user_id' => $userOrtu->id,
                    'nis' => $nis,
                    'nama_lengkap' => trim($row['nama_lengkap']),
                    'jenis_kelamin' => strtoupper(trim($row['jenis_kelamin'])),
                    'tempat_lahir' => trim($row['tempat_lahir']),
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => trim($row['alamat'] ?? ''),
                    'no_hp' => trim($row['no_hp'] ?? ''),
                    'no_hp_ortu' => trim($row['no_hp_ortu'] ?? ''),
                    'kelas_id' => $kelas?->id,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'status' => trim($row['status'] ?? 'aktif'),
                    'qr_code' => $nisn ?: $nis,
                ]
            );

            // Sync pivot table siswa_ortu
            $siswa->ortu()->syncWithoutDetaching([$userOrtu->id]);

            $this->successCount++;
        } catch (\Throwable $e) {
            $this->failedCount++;
            $this->importErrors[] = [
                'row' => $this->rowCount + 1, // +1 for the Excel header row
                'nisn' => $row['nisn'] ?? 'N/A',
                'nama' => $row['nama_lengkap'] ?? 'N/A',
                'error' => $e->getMessage(),
            ];

            Log::warning("Import row {$this->rowCount} failed in file '{$this->fileName}'", [
                'file' => $this->fileName,
                'row_number' => $this->rowCount + 1,
                'row_data' => $row,
                'error' => $e->getMessage(),
            ]);
        }

        // Kembalikan null agar Maatwebsite tidak double-insert (karena kita pakai updateOrCreate)
        return null;
    }

    /**
     * Resolve Tahun Akademik.
     */
    private function resolveTahunAkademik(string $raw, ?Kelas $kelas): ?int
    {
        if (empty($raw)) {
            // Fallback ke tahun aktif dari kelas atau DB
            $ta = $kelas?->tahun_akademik
                ?? TahunAkademik::where('is_aktif', true)->first()
                ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
            if ($ta) {
                Log::info("Tahun akademik tidak disediakan, fallback ke: {$ta->nama} {$ta->semester}");
            }
            return $ta?->id;
        }

        if (isset($this->tahunAkademikCache[$raw])) {
            return $this->tahunAkademikCache[$raw]?->id;
        }

        // Normalisasi separator dan spasi
        $normalized = preg_replace('/\s+/', ' ', trim($raw));
        $normalized = str_replace('/', '-', $normalized);

        // Coba match: "2025-2026 Genap" atau "2025-2026"
        $parts = explode(' ', $normalized, 2);
        $namaPart = $parts[0] ?? '';                    // "2025-2026"
        $semesterPart = strtolower($parts[1] ?? '');    // "genap" / "ganjil"

        // Validasi semester
        $validSemesters = ['genap', 'ganjil'];
        if ($semesterPart !== '' && !in_array($semesterPart, $validSemesters)) {
            Log::warning("Semester tidak dikenal: '{$semesterPart}' untuk tahun ajaran '{$raw}', fallback tanpa filter semester");
            $semesterPart = '';
        }

        $query = TahunAkademik::where('nama', $namaPart);
        if ($semesterPart !== '') {
            $query->where('semester', $semesterPart);
        }
        $ta = $query->first();

        if (!$ta) {
            Log::warning("Tahun akademik tidak ditemukan untuk: '{$raw}', fallback ke default");
            $ta = $kelas?->tahun_akademik
                ?? TahunAkademik::where('is_aktif', true)->first()
                ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
        }

        $this->tahunAkademikCache[$raw] = $ta;
        return $ta?->id;
    }

    /**
     * Parse Tanggal Lahir with various formats, throwing error instead of silent null.
     */
    private function parseTanggalLahir($value): ?string
    {
        if (empty($value)) {
            return null; // Boleh null jika kolom benar-benar kosong
        }

        // Excel serial date number
        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                throw new \Exception("Format tanggal lahir '{$value}' (serial number) tidak valid.");
            }
        }

        $valueStr = trim((string) $value);

        // Format dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valueStr)) {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $valueStr);
                $errors = Carbon::getLastErrors();
                if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                    throw new \Exception("Format tanggal lahir '{$valueStr}' tidak valid.");
                }
                return $date->format('Y-m-d');
            } catch (\Throwable $e) {
                throw new \Exception("Format tanggal lahir '{$valueStr}' tidak valid. Gunakan format dd/mm/yyyy.");
            }
        }

        // Format yyyy-mm-dd (ISO)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valueStr)) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $valueStr);
                $errors = Carbon::getLastErrors();
                if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                    throw new \Exception("Format tanggal lahir '{$valueStr}' tidak valid.");
                }
                return $date->format('Y-m-d');
            } catch (\Throwable $e) {
                throw new \Exception("Format tanggal lahir '{$valueStr}' tidak valid.");
            }
        }

        // Fallback: coba Carbon parse dengan throw jika gagal
        try {
            $date = Carbon::parse($valueStr);
            $errors = Carbon::getLastErrors();
            if ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                throw new \Exception("Format tanggal lahir '{$valueStr}' tidak valid.");
            }
            return $date->format('Y-m-d');
        } catch (\Throwable $e) {
            throw new \Exception("Format tanggal lahir '{$valueStr}' tidak dikenal. Gunakan format dd/mm/yyyy.");
        }
    }

    public function rules(): array
    {
        return [
            'nis' => ['nullable', 'max:50'],
            'nisn' => ['required', 'max:50'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P', 'l', 'p'])],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required'],
            'kelas' => [
                'required', 
                'string', 
                function ($attribute, $value, $fail) {
                    $exists = Kelas::whereRaw('TRIM(nama) = ?', [trim($value)])->exists();
                    if (!$exists) {
                        $fail('Kelas "' . trim($value) . '" tidak ditemukan. Pastikan nama kelas sesuai dengan data di master kelas.');
                    }
                }
            ],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'alumni'])],
            'no_hp_ortu' => ['required', 'max:50'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            'no_hp_ortu.required' => 'Kolom no_hp_ortu wajib diisi.',
        ];
    }
}

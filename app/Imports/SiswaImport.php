<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
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

    public function model(array $row)
    {
        // ── Update progress setiap 5 baris ────────────────────────────────────
        $this->rowCount++;
        if ($this->rowCount % 5 === 0) {
            cache()->put('siswa_import_progress', $this->rowCount);
        }

        // ── Resolve Kelas ────────────────────────────────────────────────────
        $namaKelas = trim($row['kelas'] ?? '');
        if (! isset($this->kelasCache[$namaKelas])) {
            $this->kelasCache[$namaKelas] = Kelas::where('nama', $namaKelas)->first();
        }
        $kelas = $this->kelasCache[$namaKelas];

        // ── Resolve Tahun Akademik ────────────────────────────────────────────
        // Format kolom: "2025-2026 Genap" atau "2025/2026 Ganjil"
        $tahunAkademikRaw = trim($row['tahun_ajaran'] ?? $row['tahun_akademik'] ?? '');
        $tahunAkademikId = null;

        if ($tahunAkademikRaw !== '') {
            if (! isset($this->tahunAkademikCache[$tahunAkademikRaw])) {
                // Coba pisahkan nama dan semester ("2025-2026 Genap")
                $parts = preg_split('/[\s]+/', $tahunAkademikRaw, 2);
                $namaPart = $parts[0] ?? '';                    // "2025-2026"
                $semPart = strtolower($parts[1] ?? '');        // "genap" / "ganjil"

                // Normalisasi: "2025/2026" → "2025-2026"
                $namaPart = str_replace('/', '-', $namaPart);

                $query = TahunAkademik::where('nama', $namaPart);
                if ($semPart !== '') {
                    $query->where('semester', $semPart);
                }
                $this->tahunAkademikCache[$tahunAkademikRaw] = $query->first();
            }
            $ta = $this->tahunAkademikCache[$tahunAkademikRaw];
            $tahunAkademikId = $ta?->id;
        }

        // Jika masih null, ambil dari kelas (jika tersedia) atau tahun aktif/terbaru
        if (empty($tahunAkademikId)) {
            $tahunAkademikId = $kelas?->tahun_akademik_id
                ?? TahunAkademik::where('is_aktif', true)->value('id')
                ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->value('id');
        }

        // ── Tanggal Lahir ────────────────────────────────────────────────────
        $tanggalLahir = $row['tanggal_lahir'];
        if (is_numeric($tanggalLahir)) {
            $tanggalLahir = Date::excelToDateTimeObject($tanggalLahir)->format('Y-m-d');
        } else {
            try {
                $tanggalLahir = Carbon::createFromFormat('d/m/Y', $tanggalLahir)->format('Y-m-d');
            } catch (\Exception $e) {
                try {
                    $tanggalLahir = Carbon::parse($tanggalLahir)->format('Y-m-d');
                } catch (\Exception $e2) {
                    $tanggalLahir = null;
                }
            }
        }

        // ── Auto-create akun User siswa ───────────────────────────────────────
        if ($this->domainEmail === null) {
            $this->domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        }
        $nisn = trim($row['nisn']);
        $nis = trim($row['nis']);
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

        // Kembalikan null agar Maatwebsite tidak double-insert (karena kita pakai updateOrCreate)
        return null;
    }

    public function rules(): array
    {
        return [
            'nis' => ['nullable', 'string', 'max:50'],
            'nisn' => ['required', 'string', 'max:50'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P', 'l', 'p'])],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required'],
            'kelas' => ['required', 'string', 'exists:kelas,nama'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif', 'alumni'])],
            'no_hp_ortu' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'kelas.exists' => 'Kelas ":input" tidak ditemukan. Pastikan nama kelas sesuai dengan data di master kelas.',
            'jenis_kelamin.in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
        ];
    }
}

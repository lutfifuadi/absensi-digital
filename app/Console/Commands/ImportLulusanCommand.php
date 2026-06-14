<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Carbon\Carbon;

class ImportLulusanCommand extends Command
{
    protected $signature = 'import:lulusan-xii';
    protected $description = 'Import data lulusan Kelas XII 2026 dari Excel dan lakukan sinkronisasi nomor kontak';

    public function handle()
    {
        $this->info("Memulai proses import data lulusan Kelas XII 2026...");

        $f1 = base_path('docs/data_siswa.xlsx');
        $f2 = base_path('docs/daftar_siswa_xii_2026.xlsx');

        if (!file_exists($f1)) {
            $this->error("File docs/data_siswa.xlsx tidak ditemukan.");
            return 1;
        }

        if (!file_exists($f2)) {
            $this->error("File docs/daftar_siswa_xii_2026.xlsx tidak ditemukan.");
            return 1;
        }

        // 1. Baca data dari excel
        $this->info("Membaca data dari Excel...");
        $excelData1 = Excel::toArray(new class implements ToArray {
            public function array(array $array) { return $array; }
        }, $f1)[2]; // Sheet 2 (Tahun Lulus 2026)

        $excelData2 = Excel::toArray(new class implements ToArray {
            public function array(array $array) { return $array; }
        }, $f2)[0]; // Sheet 0

        // 2. Index data_siswa.xlsx Sheet 2
        $this->info("Membuat index data referensi...");
        $referenceList = [];
        for ($i = 1; $i < count($excelData1); $i++) {
            $row = $excelData1[$i];
            $nisn = $this->cleanNisn($row[0] ?? '');
            $name = trim($row[1] ?? '');
            $phone = trim($row[7] ?? '');
            $nis = trim($row[6] ?? '');

            $referenceList[] = [
                'nisn' => $nisn,
                'nis' => $nis,
                'name' => $name,
                'norm_name' => $this->normalizeName($name),
                'phone' => $phone
            ];
        }

        // 3. Resolve Active Academic Year
        $ta = TahunAkademik::where('is_aktif', true)->first() 
            ?? TahunAkademik::orderBy('tanggal_mulai', 'desc')->first();
        if (!$ta) {
            $this->error("Tahun akademik aktif tidak ditemukan di sistem.");
            return 1;
        }
        $this->info("Menggunakan Tahun Akademik: {$ta->nama} - Semester " . ucfirst($ta->semester) . " (ID: {$ta->id})");

        // Get email domain settings
        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';

        // 4. Process each student
        $total = count($excelData2) - 1;
        $successCount = 0;
        $failedCount = 0;

        $this->info("Menyinkronkan data siswa (Total: $total)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($excelData2 as $idx => $row) {
            if ($idx === 0) continue; // Skip header
            if (empty(trim($row[0] ?? ''))) {
                $bar->advance();
                continue; // Skip empty row
            }

            DB::beginTransaction();
            try {
                $namaLengkap = trim($row[0]);
                $nisn = $this->cleanNisn($row[1] ?? '');
                $tempatLahir = trim($row[2] ?? '');
                $tanggalLahirRaw = trim($row[3] ?? '');
                $genderRaw = trim($row[4] ?? 'Laki-laki');
                $nis = trim($row[5] ?? '');
                $noPeserta = trim($row[6] ?? '');
                $namaKelas = trim($row[7] ?? '');
                $jurusan = trim($row[8] ?? 'UMUM');

                $tanggalLahir = $this->parseIndonesianDate($tanggalLahirRaw);
                $jenisKelamin = ($genderRaw === 'Perempuan' || strtolower($genderRaw) === 'p') ? 'P' : 'L';

                // Resolve Kelas
                $kelas = Kelas::where('nama', $namaKelas)->where('tahun_akademik_id', $ta->id)->first();
                if (!$kelas) {
                    $kelas = Kelas::create([
                        'nama' => $namaKelas,
                        'tingkat' => 'XII',
                        'jurusan' => $jurusan,
                        'tahun_akademik_id' => $ta->id,
                        'is_aktif_absensi' => true
                    ]);
                }

                // Match Phone Number from reference data
                $phone = '';
                $normName = $this->normalizeName($namaLengkap);
                
                // Match by NISN
                foreach ($referenceList as $ref) {
                    if ($ref['nisn'] === $nisn && !empty($nisn)) {
                        $phone = $ref['phone'];
                        break;
                    }
                }
                // Match by NIS
                if (empty($phone)) {
                    foreach ($referenceList as $ref) {
                        if ($ref['nis'] === $nis && !empty($nis)) {
                            $phone = $ref['phone'];
                            break;
                        }
                    }
                }
                // Match by Normalized Name
                if (empty($phone)) {
                    foreach ($referenceList as $ref) {
                        if ($ref['norm_name'] === $normName) {
                            $phone = $ref['phone'];
                            break;
                        }
                    }
                }
                // Match by fuzzy normalized name
                if (empty($phone)) {
                    foreach ($referenceList as $ref) {
                        if (substr($ref['norm_name'], 0, 15) === substr($normName, 0, 15)) {
                            $phone = $ref['phone'];
                            break;
                        }
                    }
                }

                // Sanitize phone number (remove space, dash, or non-numeric)
                if (!empty($phone)) {
                    $phone = preg_replace('/[^0-9]/', '', $phone);
                    if (str_starts_with($phone, '08')) {
                        $phone = '628' . substr($phone, 2);
                    }
                }

                // Create User Accounts
                $identifier = strtolower($nisn ?: $nis);
                $email = $identifier . '@' . $domainEmail;
                $username = $nisn ?: $nis;

                $user = User::firstOrCreate(
                    ['username' => $username],
                    [
                        'name' => $namaLengkap,
                        'email' => $email,
                        'password' => Hash::make($nisn ?: 'password123'), // Default password is NISN
                        'role' => User::ROLE_SISWA,
                    ]
                );

                // Parent account
                $usernameOrtu = 'ortu.' . $identifier;
                $emailOrtu = 'ortu.' . $identifier . '@' . $domainEmail;
                $userOrtu = User::firstOrCreate(
                    ['username' => $usernameOrtu],
                    [
                        'name' => 'Wali Murid ' . $namaLengkap,
                        'email' => $emailOrtu,
                        'password' => Hash::make($nisn ?: 'password123'),
                        'role' => User::ROLE_ORANG_TUA,
                    ]
                );

                // Upsert Siswa
                $siswa = Siswa::updateOrCreate(
                    ['nisn' => $nisn],
                    [
                        'user_id' => $user->id,
                        'ortu_user_id' => $userOrtu->id,
                        'nis' => $nis,
                        'nama_lengkap' => $namaLengkap,
                        'jenis_kelamin' => $jenisKelamin,
                        'tempat_lahir' => $tempatLahir,
                        'tanggal_lahir' => $tanggalLahir,
                        'no_hp' => $phone,
                        'no_hp_ortu' => $phone, // Mapped to parent phone
                        'kelas_id' => $kelas->id,
                        'tahun_akademik_id' => $ta->id,
                        'status' => 'aktif',
                        'qr_code' => $nisn ?: $nis,
                    ]
                );

                DB::commit();
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $failedCount++;
                $this->error("\nGagal meng-import baris " . ($idx + 1) . " (" . ($row[0] ?? 'N/A') . "): " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\nImport selesai!");
        $this->info("Sukses: {$successCount} siswa");
        $this->error("Gagal: {$failedCount} siswa");

        // Log Activity
        ActivityLog::record(
            'create',
            'siswa',
            "Import massal lulusan Kelas XII 2026: {$successCount} siswa sukses, {$failedCount} gagal",
            null,
            ['success' => $successCount, 'failed' => $failedCount]
        );

        return 0;
    }

    private function cleanNisn($nisn)
    {
        return str_pad(trim((string)$nisn), 10, '0', STR_PAD_LEFT);
    }

    private function normalizeName($name)
    {
        $name = strtolower(trim((string)$name));
        $name = preg_replace('/[^a-z0-9]/', '', $name);
        return $name;
    }

    private function parseIndonesianDate($dateStr)
    {
        $dateStr = strtolower(trim((string)$dateStr));
        
        $months = [
            'januari' => '01', 'pebruari' => '02', 'februari' => '02', 'maret' => '03', 
            'april' => '04', 'mei' => '05', 'juni' => '06', 'juli' => '07', 
            'agustus' => '08', 'september' => '09', 'oktober' => '10', 'nopember' => '11', 
            'november' => '11', 'desember' => '12'
        ];

        // Format: "dd Month YYYY"
        $parts = preg_split('/\s+/', $dateStr);
        if (count($parts) === 3) {
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $monthWord = $parts[1];
            $year = $parts[2];

            $month = $months[$monthWord] ?? '01';
            return "{$year}-{$month}-{$day}";
        }

        // Fallback to carbon parsing
        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}

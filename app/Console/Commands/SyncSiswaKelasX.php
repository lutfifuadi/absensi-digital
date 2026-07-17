<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\Pengaturan;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class SyncSiswaKelasX extends Command
{
    protected $signature = 'siswa:sync-kelas-x {--dry-run : Menjalankan simulasi update tanpa menyimpan ke database}';
    protected $description = 'Menyesuaikan data siswa kelas X.UMUM dengan file Excel DATA-LENGKAP-SISWA-KELAS-X-2026-2027.xlsx';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $filePath = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\DATA-LENGKAP-SISWA-KELAS-X-2026-2027.xlsx';

        if (!file_exists($filePath)) {
            $this->error("File Excel tidak ditemukan di: " . $filePath);
            return 1;
        }

        $this->info("Membaca file Excel dari: {$filePath}");
        if ($dryRun) {
            $this->warn("--- RUNNING IN DRY-RUN MODE (SIMULATION) ---");
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                $this->error("Sheet kosong.");
                return 1;
            }

            // Cari header
            $headerRowIndex = -1;
            foreach ($rows as $index => $row) {
                $cleanRow = array_filter(array_map('trim', $row));
                $hasNis = false;
                $hasNama = false;
                foreach ($cleanRow as $cell) {
                    if (stripos($cell, 'nis') !== false) $hasNis = true;
                    if (stripos($cell, 'nama') !== false) $hasNama = true;
                }
                if ($hasNis && $hasNama) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            if ($headerRowIndex === -1) {
                $headerRowIndex = 0;
            }

            $colNama = 1;
            $colNis = 2;
            $colNisn = 3;
            $colJK = 5; // JENIS_KELAMIN
            $colTempatLahir = 6;
            $colTanggalLahir = 7;
            $colPhoneWa = 15;
            $colAlamat = 17;
            $colNamaAyah = 23;
            $colPhoneAyah = 25;
            $colNamaIbu = 29;
            $colPhoneIbu = 31;

            // First pass: Kumpulkan NISN dan deteksi duplikat
            $seenNisns = [];
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $namaExcel = trim((string)($rows[$i][$colNama] ?? ''));
                if (empty($namaExcel) || strtoupper($namaExcel) === 'NULL') continue;

                $nisnExcel = str_pad(trim((string)($rows[$i][$colNisn] ?? '')), 10, '0', STR_PAD_LEFT);
                if (!empty($nisnExcel) && $nisnExcel !== '0000000000') {
                    $seenNisns[$nisnExcel][] = [
                        'row' => $i,
                        'name' => $namaExcel,
                    ];
                }
            }

            // Bangun pemetaan NISN final untuk tiap baris Excel
            $nisnMapping = [];
            foreach ($seenNisns as $originalNisn => $occurrences) {
                if (count($occurrences) === 1) {
                    $nisnMapping[$occurrences[0]['row']] = $originalNisn;
                } else {
                    // Deteksi jika ada duplikasi baris dengan nama sama persis (misal NAURA)
                    $uniqueNames = [];
                    foreach ($occurrences as $occ) {
                        $normName = strtolower(trim($occ['name']));
                        if (!in_array($normName, $uniqueNames)) {
                            $uniqueNames[] = $normName;
                        } else {
                            $nisnMapping[$occ['row']] = 'SKIP_DUPLICATE_ROW';
                        }
                    }

                    // Hubungkan dengan DB untuk mencari nama yang pas (misal REIZYA sudah ada di DB dengan NISN ini)
                    $dbStudent = Siswa::where('nisn', $originalNisn)->first();
                    $dbName = $dbStudent ? strtolower(trim($dbStudent->nama_lengkap)) : '';
                    
                    $matchedIdx = -1;
                    if ($dbName !== '') {
                        foreach ($occurrences as $idx => $occ) {
                            if (isset($nisnMapping[$occ['row']]) && $nisnMapping[$occ['row']] === 'SKIP_DUPLICATE_ROW') {
                                continue;
                            }
                            $occName = strtolower(trim($occ['name']));
                            if (stripos($occName, $dbName) !== false || stripos($dbName, $occName) !== false) {
                                $matchedIdx = $idx;
                                break;
                            }
                        }
                    }

                    $suffix = 2;
                    foreach ($occurrences as $idx => $occ) {
                        if (isset($nisnMapping[$occ['row']]) && $nisnMapping[$occ['row']] === 'SKIP_DUPLICATE_ROW') {
                            continue;
                        }

                        if ($idx === $matchedIdx || ($matchedIdx === -1 && $idx === 0)) {
                            $nisnMapping[$occ['row']] = $originalNisn;
                        } else {
                            $nisnMapping[$occ['row']] = $originalNisn . '-' . $suffix;
                            $suffix++;
                        }
                    }
                }
            }

            $excelData = [];
            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $namaExcel = trim((string)($rows[$i][$colNama] ?? ''));
                if (empty($namaExcel) || strtoupper($namaExcel) === 'NULL') continue;

                // Cek apakah baris ini dilewati (karena duplikat nama)
                if (isset($nisnMapping[$i]) && $nisnMapping[$i] === 'SKIP_DUPLICATE_ROW') {
                    $this->warn("Baris " . ($i + 1) . ": Duplikat data baris untuk '{$namaExcel}', di-skip.");
                    continue;
                }

                $nisExcel = trim((string)($rows[$i][$colNis] ?? ''));
                $nisnExcel = $nisnMapping[$i] ?? str_pad(trim((string)($rows[$i][$colNisn] ?? '')), 10, '0', STR_PAD_LEFT);

                if (empty($nisnExcel) || $nisnExcel === '0000000000') {
                    $this->warn("Baris " . ($i + 1) . ": NISN kosong untuk '{$namaExcel}', di-skip.");
                    continue;
                }

                $jkExcel = trim((string)($rows[$i][$colJK] ?? ''));
                if (empty($jkExcel)) {
                    $jkExcel = trim((string)($rows[$i][4] ?? '')); // fallback ke P/L
                }
                $jenisKelamin = (strtoupper($jkExcel) === 'P' || stripos($jkExcel, 'perempuan') !== false) ? 'P' : 'L';

                $tanggalLahirRaw = $rows[$i][$colTanggalLahir];
                $tanggalLahir = $this->parseTanggalLahir($tanggalLahirRaw) ?? '2010-01-01';

                $tempatLahir = trim((string)($rows[$i][$colTempatLahir] ?? ''));
                if (empty($tempatLahir) || strtoupper($tempatLahir) === 'NULL') {
                    $tempatLahir = 'BANDUNG';
                }

                $noHpSiswa = $this->formatPhone(trim((string)($rows[$i][$colPhoneWa] ?? '')));
                
                $phoneAyah = trim((string)($rows[$i][$colPhoneAyah] ?? ''));
                $phoneIbu = trim((string)($rows[$i][$colPhoneIbu] ?? ''));
                $noHpOrtu = $this->formatPhone($phoneAyah ?: $phoneIbu);

                $namaAyah = trim((string)($rows[$i][$colNamaAyah] ?? ''));
                $namaIbu = trim((string)($rows[$i][$colNamaIbu] ?? ''));
                $namaOrtu = $namaAyah ?: ($namaIbu ?: 'Wali Murid ' . $namaExcel);

                $excelData[] = [
                    'nama_lengkap' => $namaExcel,
                    'nis' => $nisExcel,
                    'nisn' => $nisnExcel,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'no_hp' => $noHpSiswa,
                    'alamat' => trim((string)($rows[$i][$colAlamat] ?? '')),
                    'no_hp_ortu' => $noHpOrtu,
                    'nama_ortu' => $namaOrtu,
                ];
            }

            $this->info("Berhasil membaca " . count($excelData) . " data siswa dari Excel.");

            // Ambil Kelas X.UMUM
            $kelas = Kelas::where('nama', 'X.UMUM')->first();
            if (!$kelas) {
                $this->error("Kelas X.UMUM tidak ditemukan di database.");
                return 1;
            }

            // Ambil TA Aktif/Ganjil 2026-2027 (ID: 1)
            $ta = TahunAkademik::find(1) 
                ?? TahunAkademik::where('nama', '2026-2027')->where('semester', 'ganjil')->first();
            if (!$ta) {
                $this->error("Tahun Akademik 2026-2027 Ganjil (ID: 1) tidak ditemukan.");
                return 1;
            }

            $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';

            // Ambil semua NISN dari Excel untuk deteksi penonaktifan
            $excelNisns = collect($excelData)->pluck('nisn')->toArray();

            // Ambil semua siswa X.UMUM saat ini di DB
            $dbSiswa = Siswa::where('kelas_id', $kelas->id)->get();
            $dbSiswaNisns = $dbSiswa->pluck('nisn')->toArray();

            $toCreate = [];
            $toUpdate = [];
            $toDeactivate = [];

            foreach ($excelData as $item) {
                if (in_array($item['nisn'], $dbSiswaNisns)) {
                    $toUpdate[] = $item;
                } else {
                    $toCreate[] = $item;
                }
            }

            foreach ($dbSiswa as $s) {
                if (!in_array($s->nisn, $excelNisns)) {
                    $toDeactivate[] = $s;
                }
            }

            $this->info("\n=== PREVIEW SINKRONISASI ===");
            $this->info("1. Siswa yang akan DIBUAT baru: " . count($toCreate));
            $this->info("2. Siswa yang akan DIUPDATE datanya: " . count($toUpdate));
            $this->info("3. Siswa DB yang akan DINONAKTIFKAN (karena tidak ada di Excel): " . count($toDeactivate));

            if (count($toDeactivate) > 0) {
                $this->warn("\nDaftar 10 siswa yang akan dinonaktifkan:");
                foreach (array_slice($toDeactivate, 0, 10) as $idx => $s) {
                    $this->line(" - #" . ($idx + 1) . ": '{$s->nama_lengkap}' (NISN: {$s->nisn})");
                }
            }

            if ($dryRun) {
                $this->info("\nDry-run selesai. Tidak ada perubahan disimpan ke database.");
                return 0;
            }

            DB::beginTransaction();

            $createdCount = 0;
            $updatedCount = 0;
            $deactivatedCount = 0;

            // 1. Buat & Update Siswa
            foreach ($excelData as $item) {
                $identifier = strtolower($item['nisn']);
                $email = $identifier . '@' . $domainEmail;
                $username = $item['nisn'];

                // User Siswa
                $user = User::where('username', $username)->first();
                if ($user) {
                    $user->update([
                        'name' => $item['nama_lengkap'],
                        'email' => $email,
                    ]);
                } else {
                    $user = User::create([
                        'username' => $username,
                        'name' => $item['nama_lengkap'],
                        'email' => $email,
                        'password' => Hash::make($item['nisn']),
                        'role' => User::ROLE_SISWA,
                    ]);
                }

                // User Ortu
                $usernameOrtu = 'ortu.' . $identifier;
                $emailOrtu = 'ortu.' . $identifier . '@' . $domainEmail;
                
                $userOrtu = User::where('username', $usernameOrtu)->first();
                if ($userOrtu) {
                    $userOrtu->update([
                        'name' => $item['nama_ortu'],
                        'email' => $emailOrtu,
                        'no_hp' => $item['no_hp_ortu'],
                    ]);
                } else {
                    $userOrtu = User::create([
                        'username' => $usernameOrtu,
                        'name' => $item['nama_ortu'],
                        'email' => $emailOrtu,
                        'password' => Hash::make($item['nisn']),
                        'role' => User::ROLE_ORANG_TUA,
                        'no_hp' => $item['no_hp_ortu'],
                    ]);
                }

                // Siswa
                $siswa = Siswa::where('nisn', $item['nisn'])->first();
                $isNew = !$siswa;

                $siswaData = [
                    'user_id' => $user->id,
                    'ortu_user_id' => $userOrtu->id,
                    'nis' => $item['nis'],
                    'nisn' => $item['nisn'],
                    'nama_lengkap' => $item['nama_lengkap'],
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'tempat_lahir' => $item['tempat_lahir'],
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'alamat' => $item['alamat'],
                    'no_hp' => $item['no_hp'],
                    'no_hp_ortu' => $item['no_hp_ortu'],
                    'kelas_id' => $kelas->id,
                    'tahun_akademik_id' => $ta->id,
                    'status' => 'aktif',
                    'qr_code' => $item['nisn'],
                ];

                if ($isNew) {
                    $siswa = Siswa::create($siswaData);
                    $createdCount++;
                } else {
                    $siswa->update($siswaData);
                    $updatedCount++;
                }

                // Sync pivot table
                $siswa->ortu()->syncWithoutDetaching([$userOrtu->id]);
            }

            // 2. Nonaktifkan yang tidak ada di Excel
            foreach ($toDeactivate as $s) {
                $s->update(['status' => 'nonaktif']);
                $deactivatedCount++;
            }

            DB::commit();

            $this->info("\nSinkronisasi Sukses!");
            $this->info(" - Siswa Baru Dibuat: " . $createdCount);
            $this->info(" - Siswa Diupdate: " . $updatedCount);
            $this->info(" - Siswa Dinonaktifkan: " . $deactivatedCount);

            ActivityLog::record(
                'update',
                'siswa',
                "Sinkronisasi data siswa Kelas X dari Excel: {$createdCount} dibuat, {$updatedCount} diupdate, {$deactivatedCount} dinonaktifkan",
                null,
                ['created' => $createdCount, 'updated' => $updatedCount, 'deactivated' => $deactivatedCount]
            );

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("\nTerjadi kesalahan fatal. Database di-rollback.");
            $this->error($e->getMessage());
            Log::error("SyncSiswaKelasX Command Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return 1;
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (empty($phone)) return '';
        if (str_starts_with($phone, '08')) {
            return '628' . substr($phone, 2);
        }
        if (str_starts_with($phone, '8')) {
            return '628' . substr($phone, 1);
        }
        return $phone;
    }

    private function parseTanggalLahir($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // ignore, try general parsing
            }
        }

        $valueStr = strtolower(trim((string)$value));
        $valueStr = str_replace('/', '-', $valueStr);

        $months = [
            'januari' => '01', 'pebruari' => '02', 'februari' => '02', 'maret' => '03', 
            'april' => '04', 'mei' => '05', 'juni' => '06', 'juli' => '07', 
            'agustus' => '08', 'september' => '09', 'oktober' => '10', 'nopember' => '11', 
            'november' => '11', 'desember' => '12'
        ];

        // Cek format: "dd Month YYYY"
        $parts = preg_split('/[\s\-]+/', $valueStr);
        if (count($parts) === 3) {
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $monthWord = $parts[1];
            $year = $parts[2];

            if (isset($months[$monthWord])) {
                $month = $months[$monthWord];
                return "{$year}-{$month}-{$day}";
            }
        }

        try {
            return Carbon::parse($valueStr)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning("Format tanggal lahir '{$value}' tidak dikenal.");
            return null;
        }
    }
}

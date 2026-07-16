<?php

require 'vendor/autoload.php';

// Initialize Laravel application context
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = "D:/Project/Website/MAN 1 Kota Bandung/Tahun Pelajaran 2026-2027/KELAS_XII_TA_2026_2027.xlsx";

if (!file_exists($filePath)) {
    die("File Excel tidak ditemukan di: " . $filePath . "\n");
}

echo "Memuat file Excel...\n";
$spreadsheet = IOFactory::load($filePath);

// Dapatkan mapping Kelas XII aktif untuk tahun_akademik_id = 1
$classes = Kelas::where('tahun_akademik_id', 1)
    ->where('nama', 'like', 'XII.F%')
    ->get()
    ->keyBy('nama'); // format nama di DB: XII.F-1, XII.F-2, ...

// Buat array translat kelas dari format Excel (XII.F.1) ke DB (XII.F-1)
$classMapping = [];
foreach (range(1, 12) as $num) {
    $excelClassName = "XII.F." . $num;
    $dbClassName = "XII.F-" . $num;
    
    if (!$classes->has($dbClassName)) {
        die("Kelas DB '{$dbClassName}' tidak ditemukan untuk Tahun Akademik Aktif (ID 1).\n");
    }
    
    $classMapping[$excelClassName] = $classes->get($dbClassName)->id;
}

echo "Berhasil memetakan kelas Excel ke kelas DB.\n";

$recap = [
    'updated' => 0,
    'created' => 0,
    'total_processed' => 0,
];

DB::beginTransaction();

try {
    foreach (range(1, 12) as $num) {
        $sheetName = "XII.F." . $num;
        $classId = $classMapping[$sheetName];
        $sheet = $spreadsheet->getSheetByName($sheetName);
        
        echo "\nMemproses sheet: {$sheetName} (Kelas ID: {$classId})\n";
        
        $rowNum = 10; // Baris data pertama dimulai pada baris 10
        while (true) {
            $nameVal = $sheet->getCellByColumnAndRow(6, $rowNum)->getValue();
            if (empty($nameVal)) {
                break;
            }
            
            $nameVal = trim(strtoupper($nameVal));
            
            // Kolom NIS (kolom 4) dan NISN (kolom 5)
            $nis = trim($sheet->getCellByColumnAndRow(4, $rowNum)->getCalculatedValue());
            
            $nisnCell = $sheet->getCellByColumnAndRow(5, $rowNum);
            $nisnRaw = $nisnCell->getValue();
            $nisn = trim($nisnCell->getCalculatedValue());
            
            // Handle #NAME? pada NISN
            if (is_string($nisn) && strpos($nisn, "#NAME?") !== false) {
                // Ekstrak dari formula jika ada
                if (preg_match("/\"([0-9]+)\"/", $nisnRaw, $matches)) {
                    $nisn = $matches[1];
                } else {
                    $nisn = '';
                }
            }
            
            if (empty($nisn) || $nisn == '') {
                $nisn = '';
            }
            
            // Gender (kolom 7)
            $gender = trim(strtoupper($sheet->getCellByColumnAndRow(7, $rowNum)->getValue()));
            if ($gender === 'P') {
                $jenisKelamin = 'P';
            } else {
                $jenisKelamin = 'L';
            }
            
            // Cari siswa di database
            // 1. Coba cari berdasarkan nama lengkap dan NIS atau NISN
            $siswa = null;
            
            // Prioritas 1: Cari berdasarkan nama lengkap saja, karena nama lengkap adalah unik di level sekolah
            $siswa = Siswa::where('nama_lengkap', $nameVal)->first();
            
            if (!$siswa) {
                // Cari alternatif pencocokan nama yang sedikit berbeda spasi dan huruf ganda (misal NUR NAILA DZAKKIYYAH vs NURNAILA DZAKIYYAH)
                // Kita bersihkan spasi dan huruf ganda yang berurutan
                $cleanedName = preg_replace('/(.)\1+/', '$1', str_replace(' ', '', $nameVal));
                
                // Cari semua siswa dan filter di level PHP untuk performa dan akurasi regex ganda
                $siswa = Siswa::all()->first(function($item) use ($cleanedName) {
                    $itemCleaned = preg_replace('/(.)\1+/', '$1', str_replace(' ', '', $item->nama_lengkap));
                    return $itemCleaned === $cleanedName;
                });
            }
            
            // Prioritas 2: Jika tidak ada nama yang persis sama, cari berdasarkan NISN
            if (!$siswa && $nisn && $nisn != '') {
                $siswa = Siswa::where('nisn', $nisn)->first();
            }
            
            // Prioritas 3: Jika masih tidak ketemu, cari berdasarkan NIS
            if (!$siswa && $nis) {
                // Cari berdasarkan NIS dan pastikan namanya mirip untuk menghindari bentrok record lain
                $siswa = Siswa::where('nis', $nis)->where('nama_lengkap', 'like', substr($nameVal, 0, 5) . '%')->first();
            }
            
            if ($siswa) {
                // Update kelas_id dan info lainnya
                $siswa->kelas_id = $classId;
                
                // Mencegah duplicate entry pada update untuk NIS (jika NIS di-set, pastikan tidak memicu unique constraint)
                if ($nis) {
                    $existingNis = Siswa::where('nis', $nis)->where('id', '!=', $siswa->id)->first();
                    if (!$existingNis) {
                        $siswa->nis = $nis;
                    }
                }
                
                // Mencegah duplicate entry pada update untuk NISN/QR_CODE (jika NISN/QR_CODE di-set, pastikan tidak memicu unique constraint)
                if ($nisn && $nisn != '') {
                    $existingNisn = Siswa::where('nisn', $nisn)->where('id', '!=', $siswa->id)->first();
                    if (!$existingNisn) {
                        $siswa->nisn = $nisn;
                        $siswa->qr_code = $nisn;
                    }
                } else if ($nis) {
                    $existingQr = Siswa::where('qr_code', $nis)->where('id', '!=', $siswa->id)->first();
                    if (!$existingQr) {
                        $siswa->qr_code = $nis;
                    }
                }
                
                $siswa->nama_lengkap = $nameVal;
                $siswa->jenis_kelamin = $jenisKelamin;
                $siswa->tahun_akademik_id = 1;
                $siswa->status = 'aktif';
                $siswa->save();
                
                // Update username & email di model User terkait
                if ($siswa->user) {
                    $user = $siswa->user;
                    $user->name = $nameVal;
                    
                    if ($siswa->nisn && $siswa->nisn != '') {
                        $existingUser = User::where('username', $siswa->nisn)->where('id', '!=', $user->id)->first();
                        if (!$existingUser) {
                            $user->username = $siswa->nisn;
                            $user->email = $siswa->nisn . "@man1kotabandung.sch.id";
                        }
                    } else if ($siswa->nis) {
                        $existingUser = User::where('username', $siswa->nis)->where('id', '!=', $user->id)->first();
                        if (!$existingUser) {
                            $user->username = $siswa->nis;
                            $user->email = $siswa->nis . "@man1kotabandung.sch.id";
                        }
                    }
                    
                    $user->status = 'aktif';
                    $user->save();
                }
                
                // Update username & email di model User Ortu terkait
                $ortu = $siswa->ortu()->first();
                if ($ortu) {
                    $ortu->name = "Wali Murid " . $nameVal;
                    
                    if ($siswa->nisn && $siswa->nisn != '') {
                        $existingOrtu = User::where('username', "ortu." . $siswa->nisn)->where('id', '!=', $ortu->id)->first();
                        if (!$existingOrtu) {
                            $ortu->username = "ortu." . $siswa->nisn;
                            $ortu->email = "ortu." . $siswa->nisn . "@man1kotabandung.sch.id";
                        }
                    } else if ($siswa->nis) {
                        $existingOrtu = User::where('username', "ortu." . $siswa->nis)->where('id', '!=', $ortu->id)->first();
                        if (!$existingOrtu) {
                            $ortu->username = "ortu." . $siswa->nis;
                            $ortu->email = "ortu." . $siswa->nis . "@man1kotabandung.sch.id";
                        }
                    }
                    
                    $ortu->status = 'aktif';
                    $ortu->save();
                }
                
                $recap['updated']++;
                echo "UPDATE: [{$siswa->id}] {$nameVal} -> Kelas ID {$classId}\n";
            }             else {
                // Buat user baru untuk siswa
                $username = ($nisn && $nisn != '') ? $nisn : ($nis ? $nis : 'siswa_' . time() . '_' . rand(100, 999));
                $email = $username . "@man1kotabandung.sch.id";
                
                // Pastikan email unik jika ada fallback username
                $existingUser = User::where('email', $email)->orWhere('username', $username)->first();
                if ($existingUser) {
                    $username = $username . '_' . rand(10, 99);
                    $email = $username . "@man1kotabandung.sch.id";
                }
                
                $user = User::create([
                    'name' => $nameVal,
                    'username' => $username,
                    'email' => $email,
                    'role' => 'siswa',
                    'status' => 'aktif',
                    'password' => Hash::make('12345678'),
                ]);
                
                // Buat user baru untuk ortu
                $ortuUsername = "ortu." . $username;
                $ortuEmail = "ortu." . $username . "@man1kotabandung.sch.id";
                
                $ortuUser = User::create([
                    'name' => "Wali Murid " . $nameVal,
                    'username' => $ortuUsername,
                    'email' => $ortuEmail,
                    'role' => 'orang_tua',
                    'status' => 'aktif',
                    'password' => Hash::make('12345678'),
                ]);
                
                // Mencegah duplicate entry NIS saat create (jika NIS sudah dimiliki oleh siswa kelas lain)
                $safeNis = $nis;
                if ($nis) {
                    $existingNisSiswa = Siswa::where('nis', $nis)->first();
                    if ($existingNisSiswa) {
                        $safeNis = null;
                    }
                }
                
                // Mencegah duplicate entry NISN saat create
                $safeNisn = ($nisn && $nisn != '') ? $nisn : '';
                if ($safeNisn != '') {
                    $existingNisnSiswa = Siswa::where('nisn', $safeNisn)->first();
                    if ($existingNisnSiswa) {
                        $safeNisn = '';
                    }
                }
                
                // Buat record siswa
                $newSiswa = Siswa::create([
                    'user_id' => $user->id,
                    'ortu_user_id' => $ortuUser->id,
                    'nis' => $safeNis,
                    'nisn' => $safeNisn,
                    'nama_lengkap' => $nameVal,
                    'jenis_kelamin' => $jenisKelamin,
                    'kelas_id' => $classId,
                    'tahun_akademik_id' => 1,
                    'status' => 'aktif',
                    'qr_code' => ($safeNisn != '') ? $safeNisn : ($safeNis ? $safeNis : $username),
                    'tempat_lahir' => '',
                    'tanggal_lahir' => '2008-01-01',
                    'alamat' => '',
                ]);
                
                // Hubungkan ke ortu
                $newSiswa->ortu()->attach($ortuUser->id);
                
                $recap['created']++;
                echo "CREATE: {$nameVal} (Siswa ID: {$newSiswa->id}) -> Kelas ID {$classId}\n";
            }
            
            $recap['total_processed']++;
            $rowNum++;
        }
    }
    
    DB::commit();
    echo "\nSINKRONISASI SELESAI DENGAN SUKSES!\n";
    echo "====================================\n";
    echo "Total Diproses: {$recap['total_processed']}\n";
    echo "Total Diupdate : {$recap['updated']}\n";
    echo "Total Dibuat   : {$recap['created']}\n";
    echo "====================================\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\nTERJADI ERROR, TRANSACTION ROLLED BACK!\n";
    echo "Pesan Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

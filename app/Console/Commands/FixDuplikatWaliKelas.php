<?php

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDuplikatWaliKelas extends Command
{
    protected $signature = 'app:fix-duplikat-wali-kelas
                            {--dry-run : Jalankan simulasi tanpa perubahan data}
                            {--force : Jalankan perubahan data}';

    protected $description = 'Perbaiki data wali kelas yang duplikat dengan data guru existing';

    public function handle()
    {
        if (!$this->option('dry-run') && !$this->option('force')) {
            $this->error('Gunakan --dry-run untuk preview atau --force untuk menjalankan.');
            return 1;
        }

        $isDryRun = $this->option('dry-run');
        $mode = $isDryRun ? 'DRY RUN' : 'EKSEKUSI';
        $this->info("=== MODE: $mode ===");
        $this->newLine();

        // Ambil semua guru yang user-nya berrole wali_kelas (hasil import)
        $guruWaliBaru = Guru::whereHas('user', function ($q) {
            $q->where('role', 'wali_kelas');
        })->get();

        $this->info("Total guru dengan user role wali_kelas: " . $guruWaliBaru->count());
        $this->newLine();

        $totalDiperbaiki = 0;
        $totalError = 0;

        foreach ($guruWaliBaru as $guruBaru) {
            $this->line("----------------------------------------");
            $this->line("Memproses: {$guruBaru->nama_lengkap} (ID: {$guruBaru->id}, NIP: {$guruBaru->nip})");

            // Ambil SEMUA guru existing (bukan user wali_kelas) untuk dicocokkan di PHP
            $semuaGuruExisting = Guru::where('id', '!=', $guruBaru->id)
                ->whereHas('user', function ($q) {
                    $q->where('role', '!=', 'wali_kelas');
                })
                ->get();

            $bestMatch = null;
            $bestScore = 0;

            foreach ($semuaGuruExisting as $ge) {
                $score = $this->hitungKecocokanNama($guruBaru->nama_lengkap, $ge->nama_lengkap);
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $ge;
                }
            }

            // Minimum threshold: 85% ΓÇö sangat ketat untuk menghindari false match!
            $threshold = 80;
            if ($bestMatch === null || $bestScore < $threshold) {
                $this->warn("  Tidak ditemukan guru existing yang cocok (best score: $bestScore%, threshold: $threshold%). SKIP.");
                continue;
            }

            // Safety check: pastikan nama depan cocok persis (minimal kata pertama)
            $n1 = $this->normalisasiNama($guruBaru->nama_lengkap);
            $n2 = $this->normalisasiNama($bestMatch->nama_lengkap);
            $first1 = explode(' ', $n1)[0] ?? '';
            $first2 = explode(' ', $n2)[0] ?? '';
            if ($first1 !== $first2 && $bestScore < 90) {
                $this->warn("  SAFETY: Nama depan berbeda ('$first1' vs '$first2') dan skor < 95%. SKIP.");
                continue;
            }

            $ge = $bestMatch;
            $this->line("  >> Terbaik: {$ge->nama_lengkap} (ID: {$ge->id}, NIP: {$ge->nip}, UserID: {$ge->user_id}) ΓÇö skor: $bestScore%");

            // Cari kelas yang menggunakan guru_baru sebagai wali_kelas
            $kelasList = Kelas::where('wali_kelas_id', $guruBaru->id)->get();

            if ($kelasList->count() === 0) {
                $this->warn("     Tidak ada kelas dengan wali_kelas_id = {$guruBaru->id}. SKIP.");
                continue;
            }

            $userBaru = User::find($guruBaru->user_id);
            $userExisting = User::find($ge->user_id);

            $this->info("     >>> KELAS YANG AKAN DIPINDAHKAN:");
            foreach ($kelasList as $k) {
                $this->info("         - {$k->nama} (ID: {$k->id})");
            }

            $this->info("     >>> User Baru: ID={$userBaru->id}, role={$userBaru->role}, nama={$userBaru->name}");
            $this->info("     >>> User Existing: ID={$userExisting->id}, role={$userExisting->role}, nama={$userExisting->name}");

            if ($isDryRun) {
                $this->warn("     [DRY RUN] Tidak melakukan perubahan.");
            } else {
                try {
                    DB::transaction(function () use ($kelasList, $guruBaru, $ge, $userBaru, $userExisting) {
                        // 1. Pindahkan wali_kelas_id ke guru existing
                        foreach ($kelasList as $k) {
                            $k->wali_kelas_id = $ge->id;
                            $k->save();
                            $this->info("     [OK] Kelas {$k->nama} -> wali_kelas_id = {$ge->id}");
                        }

                        // 2. Update role user existing jadi wali_kelas (jika perlu)
                        if ($userExisting->role !== 'wali_kelas') {
                            $oldRole = $userExisting->role;
                            $userExisting->role = 'wali_kelas';
                            $userExisting->save();
                            $this->info("     [OK] User {$userExisting->name} role berubah: {$oldRole} -> wali_kelas");
                        }

                        // 3. Hapus user baru (import)
                        if ($userBaru) {
                            $this->info("     [DEL]  Hapus user {$userBaru->name} (ID: {$userBaru->id})");
                            $userBaru->delete();
                        }

                        // 4. Hapus guru baru (import)
                        $this->info("     [DEL]  Hapus guru {$guruBaru->nama_lengkap} (ID: {$guruBaru->id})");
                        $guruBaru->delete();
                    });

                    $totalDiperbaiki++;
                    $this->info("     [OK] BERHASIL diperbaiki!");
                } catch (\Exception $e) {
                    $totalError++;
                    $this->error("     Γ¥î ERROR: " . $e->getMessage());
                }
            }
            $this->newLine();
        }

        $this->newLine();
        $this->info("=== $mode SELESAI ===");
        $this->info("Berhasil diperbaiki: $totalDiperbaiki");
        $this->info("Error: $totalError");

        if ($isDryRun) {
            $this->warn("Ini hanya dry-run. Jalankan dengan --force untuk eksekusi nyata.");
        }

        return 0;
    }

    /**
     * Daftar gelar yang akan dihapus dari nama
     */
    private array $gelar = [
        'S\.PD', 'M\.PD', 'M\.PFIS', 'M\.PDI', 'M\.P\.MAT', 'M\.P\.',
        'S\.SI', 'S\.SN', 'S\.AG', 'S\.HUM', 'S\.PDI', 'S\.PD\.',
        'DRS\.', 'DRS', 'HJ\.', 'HJ', 'H\.', 'H\b',
        'S\.KOM', 'S\.E', 'M\.SI', 'M\.KOM', 'MM\.', 'S\. SOS',
        'S\.H', 'S\.IP', 'M\.H', 'M\.SC', 'PH\.D', 'DRS',
    ];

    /**
     * Normalisasi nama untuk perbandingan
     */
    private function normalisasiNama($nama)
    {
        $nama = strtoupper($nama);
        // Normalisasi: MUH. -> MUHAMMAD
        $nama = preg_replace('/\bMUH\.?\s*/', 'MUHAMMAD ', $nama);
        // Hapus gelar
        $pattern = '/\b(' . implode('|', $this->gelar) . ')\b\.?\s*/i';
        $nama = preg_replace($pattern, ' ', $nama);
        // Hapus sisa tanda baca
        $nama = preg_replace('/[.,()\'\"\-]/', '', $nama);
        // Hapus spasi berlebih
        $nama = preg_replace('/\s+/', ' ', $nama);
        return trim($nama);
    }

    /**
     * Hitung skor kecocokan antara 2 nama (0-100)
     *
     * Strategi:
     * 1. Jika setelah normalisasi kedua nama SAMA PERSIS ΓåÆ 100%
     * 2. Jika semua kata dari nama pendek ada di nama panjang (subset) ΓåÆ 100%
     * 3. Jika kata pertama (nama depan) sama DAN kata terakhir (nama belakang) sama ΓåÆ 95%
     * 4. Jika hanya kata pertama sama dan similaritas tinggi ΓåÆ 85-95%
     * 5. Selain itu ΓåÆ skor similar_text murni (tanpa bonus)
     */
    private function hitungKecocokanNama($nama1, $nama2)
    {
        $n1 = $this->normalisasiNama($nama1);
        $n2 = $this->normalisasiNama($nama2);

        if (empty($n1) || empty($n2)) return 0;

        $parts1 = explode(' ', $n1);
        $parts2 = explode(' ', $n2);
        $first1 = $parts1[0] ?? '';
        $first2 = $parts2[0] ?? '';
        $last1 = end($parts1);
        $last2 = end($parts2);

        // 1. Exact match
        if ($n1 === $n2) return 100;

        // 2. Subset check (semua kata dari nama pendek ada di nama panjang)
        $kataPendek = count($parts1) <= count($parts2) ? $parts1 : $parts2;
        $kataPanjang = count($parts1) > count($parts2) ? $parts1 : $parts2;
        $allWordsMatch = true;
        foreach ($kataPendek as $kp) {
            if (!in_array($kp, $kataPanjang)) {
                $allWordsMatch = false;
                break;
            }
        }
        if ($allWordsMatch && count($kataPendek) >= 2) return 100;

        // 3. Nama depan + nama belakang sama persis
        if ($first1 === $first2 && $last1 === $last2) return 95;

        // 4. Nama depan sama + nama belakang similar
        if ($first1 === $first2) {
            $sim = similar_text($n1, $n2);
            $maxLen = max(strlen($n1), strlen($n2));
            $baseScore = ($maxLen > 0) ? ($sim / $maxLen) * 100 : 0;
            return min($baseScore, 90);
        }

        // 5. Fallback: similar_text murni
        $sim = similar_text($n1, $n2);
        $maxLen = max(strlen($n1), strlen($n2));
        return ($maxLen > 0) ? ($sim / $maxLen) * 100 : 0;
    }
}



<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateSiswaKelasXii extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-siswa-kelas-xii {--dry-run : Menjalankan simulasi update tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan update data siswa kelas XII berdasarkan file pemetaan di folder temp (update_plan.json / smart_final_update_plan.json)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $tempPath = 'C:\\Users\\luthf\\AppData\\Local\\Temp\\opencode\\smart_final_update_plan.json';
        if (!file_exists($tempPath)) {
            $tempPath = 'C:\\Users\\luthf\\AppData\\Local\\Temp\\opencode\\update_plan.json';
        }

        if (!file_exists($tempPath)) {
            $this->error("File pemetaan tidak ditemukan di folder temp!");
            return 1;
        }

        $this->info("Membaca file pemetaan dari: {$tempPath}");
        $content = file_get_contents($tempPath);
        $updatePlans = json_decode($content, true);

        if (!$updatePlans || !is_array($updatePlans)) {
            $this->error("Format file pemetaan tidak valid atau kosong.");
            return 1;
        }

        $totalPlans = count($updatePlans);
        $this->info("Menemukan {$totalPlans} data siswa yang akan diproses.");

        if ($dryRun) {
            $this->warn("--- RUNNING IN DRY-RUN MODE (SIMULATION) ---");
        }

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();

        try {
            $bar = $this->output->createProgressBar($totalPlans);
            $bar->start();

            foreach ($updatePlans as $plan) {
                $siswaId = $plan['siswa_id'] ?? null;
                if (!$siswaId) {
                    $skippedCount++;
                    $bar->advance();
                    continue;
                }

                $siswa = Siswa::find($siswaId);
                if (!$siswa) {
                    $this->warn("\nSiswa ID {$siswaId} tidak ditemukan di database. Skipped.");
                    $skippedCount++;
                    $bar->advance();
                    continue;
                }

                // Siapkan data update
                $updateData = [];
                
                // 1. Update kelas_id jika berbeda
                $kelasIdBaru = $plan['kelas_id_baru'] ?? null;
                if ($kelasIdBaru && $siswa->kelas_id != $kelasIdBaru) {
                    $updateData['kelas_id'] = $kelasIdBaru;
                }

                // 2. Update nis jika berbeda
                $nisExcel = $plan['nis_excel'] ?? null;
                if ($nisExcel && $siswa->nis !== $nisExcel) {
                    $updateData['nis'] = $nisExcel;
                }

                // 3. Update nisn jika berbeda
                $nisnExcel = $plan['nisn_excel'] ?? null;
                if ($nisnExcel && $siswa->nisn !== $nisnExcel) {
                    $updateData['nisn'] = $nisnExcel;
                }

                if (empty($updateData)) {
                    $skippedCount++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    $updated = $siswa->update($updateData);
                    if ($updated) {
                        $successCount++;
                    } else {
                        $failCount++;
                        $this->error("\nGagal mengupdate Siswa ID {$siswaId} (Nama: {$siswa->nama_lengkap})");
                    }
                } else {
                    $successCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->info("\n");

            if ($dryRun) {
                DB::rollBack();
                $this->info("Simulasi selesai. Tidak ada data yang diubah di database.");
                $this->info("Jumlah data yang akan diupdate: {$successCount}");
                $this->info("Jumlah data skipped (tidak ada perubahan atau tidak ditemukan): {$skippedCount}");
            } else {
                DB::commit();
                $this->info("Proses update selesai dan berhasil disimpan ke database!");
                $this->info("Berhasil diupdate: {$successCount} baris");
                $this->info("Gagal diupdate: {$failCount} baris");
                $this->info("Skipped: {$skippedCount} baris");
            }

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("\nTerjadi kesalahan fatal selama proses update. Database di-rollback.");
            $this->error($e->getMessage());
            Log::error("UpdateSiswaKelasXii Command Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return 1;
        }
    }
}

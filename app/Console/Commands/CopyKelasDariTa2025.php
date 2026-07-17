<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class CopyKelasDariTa2025 extends Command
{
    protected $signature = 'kelas:copy-dari-2025 {--dry-run : Preview saja tanpa eksekusi}';
    protected $description = 'Copy semua kelas dari TA 2025-2026 Genap ke TA 2026-2027 Ganjil';

    public function handle()
    {
        $taSumber = TahunAkademik::where('nama', '2025-2026')->where('semester', 'genap')->first();
        if (!$taSumber) {
            $this->error('TA Sumber (2025-2026 Genap) tidak ditemukan.');
            return 1;
        }

        $taTujuan = TahunAkademik::where('nama', '2026-2027')->where('semester', 'ganjil')->first();
        if (!$taTujuan) {
            $this->error('TA Tujuan (2026-2027 Ganjil) tidak ditemukan.');
            return 1;
        }

        $kelasSumber = Kelas::with('jurusan')->where('tahun_akademik_id', $taSumber->id)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        if ($kelasSumber->isEmpty()) {
            $this->error('Tidak ada kelas di TA Sumber.');
            return 1;
        }

        $kelasTujuanExisting = Kelas::where('tahun_akademik_id', $taTujuan->id)->get()->keyBy('nama');

        $baru = [];
        $skip = [];

        foreach ($kelasSumber as $kelas) {
            if ($kelasTujuanExisting->has($kelas->nama)) {
                $skip[] = $kelas;
            } else {
                $baru[] = $kelas;
            }
        }

        $this->line('');
        $this->line('=== PREVIEW ===');
        $this->line("TA Sumber : {$taSumber->nama} " . ucfirst($taSumber->semester) . " (ID: {$taSumber->id})");
        $this->line("TA Tujuan : {$taTujuan->nama} " . ucfirst($taTujuan->semester) . " (ID: {$taTujuan->id})");
        $this->line("Total kelas sumber     : " . $kelasSumber->count());
        $this->line("Sudah ada di tujuan    : " . count($skip));
        $this->line("Akan dibuat baru       : " . count($baru));
        $this->line('');
        $this->line('Daftar:');

        foreach ($baru as $kelas) {
            $this->line("  [BARU] {$kelas->nama} ({$kelas->tingkat} - {$kelas->jurusan?->nama})");
        }

        foreach ($skip as $kelas) {
            $this->line("  [SKIP] {$kelas->nama} ({$kelas->tingkat} - {$kelas->jurusan?->nama}) — sudah ada");
        }

        $this->line('');

        if ($this->option('dry-run')) {
            $this->line('Status: DRY-RUN — Tidak ada perubahan');
            $this->line('');
            return 0;
        }

        if (count($baru) === 0) {
            $this->warn('Tidak ada kelas baru yang perlu dibuat.');
            return 0;
        }

        if (!$this->confirm("Yakin ingin menyalin " . count($baru) . " kelas dari TA {$taSumber->nama} ke TA {$taTujuan->nama}?", false)) {
            $this->warn('Dibatalkan oleh pengguna.');
            return 0;
        }

        $bar = $this->output->createProgressBar(count($baru));
        $bar->start();

        $berhasil = 0;
        $gagal = 0;

        DB::beginTransaction();
        try {
            foreach ($baru as $kelas) {
                Kelas::create([
                    'nama' => $kelas->nama,
                    'tingkat' => $kelas->tingkat,
                    'jurusan_id' => $kelas->jurusan_id,
                    'tahun_akademik_id' => $taTujuan->id,
                    'wali_kelas_id' => null,
                    'is_aktif_absensi' => true,
                ]);
                $berhasil++;
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);

            ActivityLog::record(
                'create',
                'kelas',
                "Copy {$berhasil} kelas dari {$taSumber->nama} " . ucfirst($taSumber->semester) . " ke {$taTujuan->nama} " . ucfirst($taTujuan->semester) . " (" . count($skip) . " skip)",
                ['tahun_akademik_id_sumber' => $taSumber->id],
                ['tahun_akademik_id_tujuan' => $taTujuan->id, 'kelas_baru' => $berhasil, 'kelas_skip' => count($skip)]
            );

            $this->info("Berhasil menyalin {$berhasil} kelas. " . count($skip) . " kelas sudah ada (di-skip).");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine(2);
            $this->error('Gagal menyalin kelas: ' . $e->getMessage());
            return 1;
        }
    }
}

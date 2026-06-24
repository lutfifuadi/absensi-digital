<?php

namespace App\Console\Commands;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class PindahkanSiswaXe1KeXumum extends Command
{
    protected $signature = 'siswa:pindahkan-xe1-ke-xumum {--dry-run : Preview saja tanpa eksekusi}';
    protected $description = 'Memindahkan 452 siswa dari X.E-1 (TA 2025-2026 Genap) ke X.UMUM (TA 2026-2027 Ganjil)';

    public function handle()
    {
        $kelasAsal = Kelas::where('nama', 'X.E-1')->first();
        if (!$kelasAsal) {
            $this->error('Kelas asal X.E-1 tidak ditemukan.');
            return 1;
        }

        $kelasTujuan = Kelas::where('nama', 'X.UMUM')->first();
        if (!$kelasTujuan) {
            $this->error('Kelas tujuan X.UMUM tidak ditemukan.');
            return 1;
        }

        $taTujuan = TahunAkademik::where('nama', '2026-2027')->where('semester', 'ganjil')->first();
        if (!$taTujuan) {
            $this->error('Tahun Akademik 2026-2027 Ganjil tidak ditemukan.');
            return 1;
        }

        $taAsal = $kelasAsal->tahunAkademik;

        $jumlahSiswa = Siswa::where('kelas_id', $kelasAsal->id)->where('status', 'aktif')->count();

        if ($jumlahSiswa === 0) {
            $this->error('Tidak ada siswa aktif di kelas X.E-1.');
            return 1;
        }

        $this->line('');
        $this->line('=== PREVIEW ===');
        $this->line("Kelas Asal  : {$kelasAsal->nama} (TA: {$taAsal->nama} " . ucfirst($taAsal->semester) . ")");
        $this->line("Kelas Tujuan: {$kelasTujuan->nama} (TA: {$taTujuan->nama} " . ucfirst($taTujuan->semester) . ")");
        $this->line("Jumlah Siswa: {$jumlahSiswa}");

        if ($this->option('dry-run')) {
            $this->line('Status      : DRY-RUN — Tidak ada perubahan');
            $this->line('');
            return 0;
        }

        $this->line('');

        if (!$this->confirm("Yakin ingin memindahkan {$jumlahSiswa} siswa?", false)) {
            $this->warn('Dibatalkan oleh pengguna.');
            return 0;
        }

        $siswaList = Siswa::where('kelas_id', $kelasAsal->id)->where('status', 'aktif')->get();

        $bar = $this->output->createProgressBar($siswaList->count());
        $bar->start();

        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($siswaList as $siswa) {
                $siswa->update([
                    'kelas_id' => $kelasTujuan->id,
                    'tahun_akademik_id' => $taTujuan->id,
                ]);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);

            ActivityLog::record(
                'update',
                'siswa',
                "Pindah {$jumlahSiswa} siswa dari {$kelasAsal->nama} (TA {$taAsal->nama}) ke {$kelasTujuan->nama} (TA {$taTujuan->nama})",
                ['kelas_id_asal' => $kelasAsal->id, 'tahun_akademik_id_asal' => $taAsal->id],
                ['kelas_id_tujuan' => $kelasTujuan->id, 'tahun_akademik_id_tujuan' => $taTujuan->id]
            );

            $this->info("\u{2705} Berhasil memindahkan {$jumlahSiswa} siswa dari {$kelasAsal->nama} ke {$kelasTujuan->nama}");

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine(2);
            $this->error('Gagal memindahkan siswa: ' . $e->getMessage());
            return 1;
        }
    }
}

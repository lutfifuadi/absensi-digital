<?php

namespace App\Console\Commands;

use App\Services\PenjadwalanKegiatanService;
use Illuminate\Console\Command;

class GenerateKegiatanFromJadwal extends Command
{
    /**
     * Nama dan tanda tangan dari perintah konsol.
     *
     * @var string
     */
    protected $signature = 'kegiatan:generate-berulang {--date= : Tanggal pelaksanaan spesifik YYYY-MM-DD}';

    /**
     * Deskripsi perintah konsol.
     *
     * @var string
     */
    protected $description = 'Menghasilkan sesi kegiatan harian otomatis dari jadwal kegiatan berulang (PRD-005)';

    /**
     * Eksekusi perintah konsol.
     */
    public function handle(PenjadwalanKegiatanService $service): int
    {
        $dateOpt = $this->option('date');
        $this->info("Menjalankan generator sesi kegiatan berulang" . ($dateOpt ? " untuk tanggal {$dateOpt}" : " untuk hari ini") . "...");

        $count = $service->generateSesiForDate($dateOpt);

        $this->info("Selesai! {$count} sesi kegiatan berulang berhasil di-generate.");

        return Command::SUCCESS;
    }
}

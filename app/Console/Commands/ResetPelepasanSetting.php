<?php

namespace App\Console\Commands;

use App\Models\Kegiatan;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResetPelepasanSetting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pelepasan:reset-setting
                            {--kegiatan-id= : ID kegiatan yang akan dijadikan setting (jika kosong, akan dicari otomatis)}
                            {--force : Hapus setting tanpa konfirmasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset setting pelepasan_kegiatan_id yang tidak valid di tabel pengaturan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Memeriksa setting pelepasan_kegiatan_id...');

        $setting = Pengaturan::where('key', 'pelepasan_kegiatan_id')->first();

        if (!$setting) {
            $this->warn('⚠️  Setting pelepasan_kegiatan_id tidak ditemukan di database.');
        } else {
            $currentId = $setting->value;
            $this->line("   Setting saat ini: pelepasan_kegiatan_id = {$currentId}");

            // Cek apakah kegiatan dengan ID tersebut masih ada
            $kegiatan = Kegiatan::find($currentId);

            if ($kegiatan) {
                $this->info("   ✅ Kegiatan ID {$currentId} valid: \"{$kegiatan->nama_kegiatan}\"");
                $this->line("   Tidak perlu ada perubahan.");
                return Command::SUCCESS;
            }

            $this->warn("   ⚠️  Kegiatan ID {$currentId} sudah tidak ada (telah dihapus).");
        }

        // Reset setting
        $kegiatanId = $this->option('kegiatan-id');

        if (!$kegiatanId) {
            // Cari otomatis kegiatan pelepasan
            $taId = TahunAkademik::where('is_aktif', true)->value('id');

            $kegiatan = Kegiatan::where('tahun_akademik_id', $taId)
                ->where(function ($q) {
                    $q->where('nama_kegiatan', 'like', '%Pelepasan%Kelas%XII%');
                })
                ->first();

            if (!$kegiatan) {
                $this->error('❌ Tidak ditemukan kegiatan pelepasan yang valid. Gunakan --kegiatan-id untuk menentukan manual.');
                return Command::FAILURE;
            }

            $kegiatanId = $kegiatan->id;
            $this->line("   Ditemukan kegiatan: \"{$kegiatan->nama_kegiatan}\" (ID: {$kegiatanId})");
        } else {
            $kegiatan = Kegiatan::find($kegiatanId);
            if (!$kegiatan) {
                $this->error("❌ Kegiatan ID {$kegiatanId} tidak ditemukan di database.");
                return Command::FAILURE;
            }
            $this->line("   Menggunakan kegiatan: \"{$kegiatan->nama_kegiatan}\" (ID: {$kegiatanId})");
        }

        // Konfirmasi
        if (!$this->option('force')) {
            if (!$this->confirm("Set pelepasan_kegiatan_id ke {$kegiatanId}?", true)) {
                $this->warn('⚠️  Dibatalkan.');
                return Command::FAILURE;
            }
        }

        // Simpan setting
        Pengaturan::updateOrCreate(
            ['key' => 'pelepasan_kegiatan_id'],
            ['value' => $kegiatanId, 'group' => 'pelepasan']
        );

        $this->info("✅ Setting pelepasan_kegiatan_id berhasil direset ke ID {$kegiatanId}.");
        Log::info('Setting pelepasan_kegiatan_id direset via command ke ID ' . $kegiatanId);

        return Command::SUCCESS;
    }
}

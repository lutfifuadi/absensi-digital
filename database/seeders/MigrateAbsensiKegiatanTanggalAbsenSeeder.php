<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AbsensiKegiatan;
use Illuminate\Support\Facades\DB;

class MigrateAbsensiKegiatanTanggalAbsenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Mengambil semua absensi_kegiatan yang tanggal_absen-nya masih null
            $absensiList = AbsensiKegiatan::whereNull('tanggal_absen')->with('kegiatan')->get();

            $updatedCount = 0;
            foreach ($absensiList as $absensi) {
                // Ambil tanggal_pelaksanaan dari kegiatan
                $tanggal = null;
                if ($absensi->kegiatan && $absensi->kegiatan->tanggal_pelaksanaan) {
                    $tanggal = $absensi->kegiatan->tanggal_pelaksanaan;
                } else {
                    // Fallback ke created_at di tabel absensi_kegiatan
                    $tanggal = $absensi->created_at ? $absensi->created_at->toDateString() : now()->toDateString();
                }

                $absensi->update([
                    'tanggal_absen' => $tanggal
                ]);
                $updatedCount++;
            }

            $this->command->info("Berhasil mengupdate {$updatedCount} data absensi_kegiatan dengan tanggal_absen.");
        });
    }
}

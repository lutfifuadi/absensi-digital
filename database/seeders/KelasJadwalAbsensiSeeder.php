<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use Illuminate\Database\Seeder;

class KelasJadwalAbsensiSeeder extends Seeder
{
    /**
     * Seed tabel kelas_jadwal_absensi dengan jadwal default.
     *
     * Jadwal default:
     * - Senin-Jumat: jam_mulai_absensi 06:00, jam_masuk 07:00,
     *                jam_pulang 15:00, jam_akhir_pulang 17:00, is_libur false
     * - Sabtu-Minggu: is_libur true, semua field waktu NULL
     */
    public function run(): void
    {
        // Ambil semua kelas yang aktif
        $kelasList = Kelas::all();

        if ($kelasList->isEmpty()) {
            $this->command?->warn('Tidak ada data kelas. Seeder dilewati.');
            return;
        }

        // Daftar hari dalam seminggu
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        // Hari libur (Sabtu dan Minggu)
        $hariLibur = ['sabtu', 'minggu'];

        foreach ($kelasList as $kelas) {
            foreach ($hariList as $hari) {
                $isLibur = in_array($hari, $hariLibur);

                KelasJadwalAbsensi::updateOrCreate(
                    [
                        'kelas_id' => $kelas->id,
                        'hari' => $hari,
                    ],
                    [
                        'jam_mulai_absensi' => $isLibur ? null : '06:00',
                        'jam_masuk' => $isLibur ? null : '07:00',
                        'jam_pulang' => $isLibur ? null : '15:00',
                        'jam_akhir_pulang' => $isLibur ? null : '17:00',
                        'is_libur' => $isLibur,
                    ]
                );
            }
        }

        $this->command?->info("Jadwal absensi untuk {$kelasList->count()} kelas berhasil dibuat.");
    }
}

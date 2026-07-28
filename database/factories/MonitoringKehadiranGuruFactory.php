<?php

namespace Database\Factories;

use App\Models\MonitoringKehadiranGuru;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MonitoringKehadiranGuru>
 */
class MonitoringKehadiranGuruFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['hadir', 'tidak_hadir', 'terlambat']);
        
        $keterangan = null;
        $keteranganLain = null;
        $lamaTerlambat = null;
        $adaPengganti = false;
        $guruPenggantiId = null;
        $guruPenggantiNama = null;

        if ($status === 'tidak_hadir') {
            $keterangan = $this->faker->randomElement(['sakit', 'izin', 'dinas_luar', 'alfa']);
            if ($keterangan === 'sakit') {
                $keteranganLain = 'Surat dokter menyusul';
            }
            $adaPengganti = $this->faker->boolean();
            if ($adaPengganti) {
                $guruPenggantiNama = $this->faker->name();
            }
        } elseif ($status === 'terlambat') {
            $lamaTerlambat = $this->faker->numberBetween(5, 60);
        }

        return [
            'jadwal_pelajaran_id' => \App\Models\JadwalPelajaran::factory(),
            'tanggal' => $this->faker->date(),
            'status' => $status,
            'keterangan' => $keterangan,
            'keterangan_lain' => $keteranganLain,
            'lama_terlambat' => $lamaTerlambat,
            'ada_pengganti' => $adaPengganti,
            'guru_pengganti_id' => $guruPenggantiId,
            'guru_pengganti_nama' => $guruPenggantiNama,
            'dicatat_oleh' => \App\Models\User::factory(),
        ];
    }
}

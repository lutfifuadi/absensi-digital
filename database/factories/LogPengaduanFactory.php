<?php

namespace Database\Factories;

use App\Models\LogPengaduan;
use App\Models\Pengaduan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LogPengaduan>
 */
class LogPengaduanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['baru', 'diproses', 'selesai', 'ditolak'];
        $statusDari = fake()->randomElement($statuses);
        $filtered = array_values(array_filter($statuses, fn ($s) => $s !== $statusDari));
        $statusKe = fake()->randomElement($filtered);

        return [
            'status_dari' => $statusDari,
            'status_ke' => $statusKe,
            'catatan' => fake()->optional(0.8)->sentence(),
            'diubah_oleh' => fake()->randomElement([
                'sistem',
                'admin:1',
                'admin:2',
                'admin:3',
            ]),
        ];
    }

    /**
     * Set the pengaduan for this log.
     */
    public function forPengaduan(Pengaduan $pengaduan): static
    {
        return $this->state(fn (array $attributes) => [
            'status_dari' => 'baru',
            'status_ke' => $pengaduan->status,
            'pengaduan_id' => $pengaduan->id,
        ]);
    }
}

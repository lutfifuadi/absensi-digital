<?php

namespace Database\Factories;

use App\Models\Pengaduan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pengaduan>
 */
class PengaduanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal = fake()->dateTimeBetween('-2 weeks', 'now');

        return [
            'kode_unik' => 'PGN-' . $tanggal->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'nama_lengkap' => fake()->name(),
            'status_pelapor' => fake()->randomElement(['siswa', 'orang_tua']),
            'kategori' => fake()->randomElement([
                'Nama tidak sesuai',
                'NIS/NIP salah',
                'Kelas tidak sesuai',
                'Tempat lahir salah',
                'Tanggal lahir salah',
                'Alamat tidak valid',
                'Data orang tua salah',
                'Foto tidak sesuai',
            ]),
            'deskripsi' => fake()->paragraph(rand(2, 5)),
            'nomor_wa' => '08' . fake()->numerify('##########'),
            'status' => fake()->randomElement(['baru', 'diproses', 'selesai', 'ditolak']),
            'catatan_admin' => fake()->optional(0.7)->sentence(),
            'verified_at' => fake()->optional(0.8)->dateTimeBetween('-2 weeks', 'now'),
        ];
    }

    /**
     * Indikator bahwa pengaduan berstatus baru.
     */
    public function baru(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'baru',
            'catatan_admin' => null,
        ]);
    }

    /**
     * Indikator bahwa pengaduan sedang diproses.
     */
    public function diproses(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diproses',
            'catatan_admin' => 'Sedang diverifikasi oleh admin.',
        ]);
    }

    /**
     * Indikator bahwa pengaduan sudah selesai.
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
            'catatan_admin' => 'Data sudah diperbaiki. Silakan cek data terbaru.',
        ]);
    }

    /**
     * Indikator bahwa pengaduan ditolak.
     */
    public function ditolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ditolak',
            'catatan_admin' => 'Data sudah benar, tidak ada perubahan yang diperlukan.',
        ]);
    }
}

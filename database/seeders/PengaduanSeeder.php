<?php

namespace Database\Seeders;

use App\Models\Pengaduan;
use Illuminate\Database\Seeder;

class PengaduanSeeder extends Seeder
{
    /**
     * Seed the pengaduan table.
     */
    public function run(): void
    {
        $now = now();

        // Pengaduan 1-3: Baru
        $p1 = Pengaduan::factory()->baru()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-001']);
        $p1->logs()->create([
            'status_dari' => 'baru',
            'status_ke' => 'baru',
            'catatan' => 'Pengaduan berhasil dibuat.',
            'diubah_oleh' => 'sistem',
        ]);

        $p2 = Pengaduan::factory()->baru()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-002']);
        $p2->logs()->create([
            'status_dari' => 'baru',
            'status_ke' => 'baru',
            'catatan' => 'Pengaduan berhasil dibuat.',
            'diubah_oleh' => 'sistem',
        ]);

        $p3 = Pengaduan::factory()->baru()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-003']);
        $p3->logs()->create([
            'status_dari' => 'baru',
            'status_ke' => 'baru',
            'catatan' => 'Pengaduan berhasil dibuat.',
            'diubah_oleh' => 'sistem',
        ]);

        // Pengaduan 4-5: Diproses
        $p4 = Pengaduan::factory()->diproses()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-004']);
        $p4->logs()->create([
            'status_dari' => 'baru',
            'status_ke' => 'diproses',
            'catatan' => 'Pengaduan masuk dan sedang diproses.',
            'diubah_oleh' => 'sistem',
        ]);

        $p5 = Pengaduan::factory()->diproses()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-005']);
        $p5->logs()->create([
            'status_dari' => 'baru',
            'status_ke' => 'diproses',
            'catatan' => 'Pengaduan masuk dan sedang diproses.',
            'diubah_oleh' => 'sistem',
        ]);

        // Pengaduan 6-8: Selesai
        $p6 = Pengaduan::factory()->selesai()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-006']);
        $p6->logs()->create(['status_dari' => 'baru', 'status_ke' => 'diproses', 'catatan' => 'Pengaduan masuk, sedang diverifikasi.', 'diubah_oleh' => 'sistem']);
        $p6->logs()->create(['status_dari' => 'diproses', 'status_ke' => 'selesai', 'catatan' => 'Data sudah diperbaiki.', 'diubah_oleh' => 'admin:1']);

        $p7 = Pengaduan::factory()->selesai()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-007']);
        $p7->logs()->create(['status_dari' => 'baru', 'status_ke' => 'diproses', 'catatan' => 'Pengaduan masuk, sedang diverifikasi.', 'diubah_oleh' => 'sistem']);
        $p7->logs()->create(['status_dari' => 'diproses', 'status_ke' => 'selesai', 'catatan' => 'Data sudah diperbaiki.', 'diubah_oleh' => 'admin:1']);

        $p8 = Pengaduan::factory()->selesai()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-008']);
        $p8->logs()->create(['status_dari' => 'baru', 'status_ke' => 'diproses', 'catatan' => 'Pengaduan masuk, sedang diverifikasi.', 'diubah_oleh' => 'sistem']);
        $p8->logs()->create(['status_dari' => 'diproses', 'status_ke' => 'selesai', 'catatan' => 'Data sudah diperbaiki.', 'diubah_oleh' => 'admin:1']);

        // Pengaduan 9-10: Ditolak
        $p9 = Pengaduan::factory()->ditolak()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-009']);
        $p9->logs()->create(['status_dari' => 'baru', 'status_ke' => 'diproses', 'catatan' => 'Pengaduan masuk, sedang diverifikasi.', 'diubah_oleh' => 'sistem']);
        $p9->logs()->create(['status_dari' => 'diproses', 'status_ke' => 'ditolak', 'catatan' => 'Data sudah benar, tidak ada perubahan.', 'diubah_oleh' => 'admin:1']);

        $p10 = Pengaduan::factory()->ditolak()->create(['kode_unik' => 'PGN-' . $now->format('Ymd') . '-010']);
        $p10->logs()->create(['status_dari' => 'baru', 'status_ke' => 'diproses', 'catatan' => 'Pengaduan masuk, sedang diverifikasi.', 'diubah_oleh' => 'sistem']);
        $p10->logs()->create(['status_dari' => 'diproses', 'status_ke' => 'ditolak', 'catatan' => 'Data sudah benar, tidak ada perubahan.', 'diubah_oleh' => 'admin:1']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\KategoriPelanggaran;
use App\Models\JenisPelanggaran;
use Illuminate\Database\Seeder;

class JenisPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriTataTertib = KategoriPelanggaran::where('nama', 'Tata Tertib')->first();
        $kategoriMoral = KategoriPelanggaran::where('nama', 'Moral')->first();
        $kategoriAkademik = KategoriPelanggaran::where('nama', 'Akademik')->first();
        $kategoriKehadiran = KategoriPelanggaran::where('nama', 'Kehadiran')->first();

        // 1. Tata Tertib
        if ($kategoriTataTertib) {
            $tataTertib = [
                ['nama' => 'Tidak memakai seragam sesuai ketentuan', 'bobot_poin' => 5, 'deskripsi' => 'Atribut seragam tidak lengkap/tidak sesuai hari.'],
                ['nama' => 'Rambut gondrong / tidak rapi (putra)', 'bobot_poin' => 5, 'deskripsi' => 'Panjang rambut melebihi kerah baju atau menutupi telinga.'],
                ['nama' => 'Membawa barang terlarang (non-senjata/narkoba)', 'bobot_poin' => 15, 'deskripsi' => 'Membawa kartu domino, komik tidak mendidik, dll.'],
                ['nama' => 'Merusak fasilitas sekolah skala ringan', 'bobot_poin' => 20, 'deskripsi' => 'Mencoret-coret meja, kursi, atau dinding kelas.'],
            ];
            foreach ($tataTertib as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriTataTertib->id],
                    $item
                );
            }
        }

        // 2. Moral
        if ($kategoriMoral) {
            $moral = [
                ['nama' => 'Berkelahi / tawuran', 'bobot_poin' => 50, 'deskripsi' => 'Melakukan kekerasan fisik terhadap sesama siswa.'],
                ['nama' => 'Melawan guru atau staff sekolah', 'bobot_poin' => 40, 'deskripsi' => 'Membentak, menghina, atau melakukan tindakan menantang guru.'],
                ['nama' => 'Merokok di area sekolah / berseragam sekolah', 'bobot_poin' => 25, 'deskripsi' => 'Kedapatan merokok di lingkungan sekolah atau di luar sekolah memakai seragam.'],
                ['nama' => 'Melakukan tindakan bullying / perundungan', 'bobot_poin' => 30, 'deskripsi' => 'Merundung teman baik secara verbal, fisik, maupun cyber-bullying.'],
            ];
            foreach ($moral as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriMoral->id],
                    $item
                );
            }
        }

        // 3. Akademik
        if ($kategoriAkademik) {
            $akademik = [
                ['nama' => 'Menyontek saat ujian', 'bobot_poin' => 20, 'deskripsi' => 'Membawa catatan, melihat hp, atau meniru jawaban teman saat ujian.'],
                ['nama' => 'Tidak mengerjakan tugas/PR berulang', 'bobot_poin' => 10, 'deskripsi' => 'Lebih dari 3 kali tidak mengumpulkan tugas mata pelajaran yang sama.'],
                ['nama' => 'Membuat gaduh saat KBM berlangsung', 'bobot_poin' => 5, 'deskripsi' => 'Mengganggu konsentrasi belajar kelas dengan sengaja.'],
            ];
            foreach ($akademik as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriAkademik->id],
                    $item
                );
            }
        }

        // 4. Kehadiran
        if ($kategoriKehadiran) {
            $kehadiran = [
                ['nama' => 'Terlambat masuk sekolah', 'bobot_poin' => 5, 'deskripsi' => 'Datang setelah bel masuk berbunyi tanpa alasan sah.'],
                ['nama' => 'Bolos jam pelajaran / keluar sekolah tanpa izin', 'bobot_poin' => 10, 'deskripsi' => 'Meninggalkan kelas atau area sekolah di jam pelajaran tanpa kartu izin.'],
                ['nama' => 'Alpa / tidak masuk tanpa keterangan', 'bobot_poin' => 5, 'deskripsi' => 'Tidak hadir sekolah tanpa surat sakit/izin dari orang tua.'],
            ];
            foreach ($kehadiran as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriKehadiran->id],
                    $item
                );
            }
        }
    }
}

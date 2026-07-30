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
        $kategoriKedisiplinan = KategoriPelanggaran::where('nama', 'Kedisiplinan & Kerapian')->first();
        $kategoriKehadiran = KategoriPelanggaran::where('nama', 'Kehadiran & Akses')->first();
        $kategoriEtika = KategoriPelanggaran::where('nama', 'Etika & Perilaku')->first();
        $kategoriBerat = KategoriPelanggaran::where('nama', 'Pelanggaran Berat & Keamanan')->first();

        // 1. Kedisiplinan & Kerapian
        if ($kategoriKedisiplinan) {
            $kedisiplinan = [
                ['nama' => 'Terlambat masuk sekolah (< 15 menit)', 'bobot_poin' => 5, 'deskripsi' => 'Datang setelah bel masuk berbunyi tanpa alasan sah.'],
                ['nama' => 'Terlambat masuk sekolah (> 15 menit)', 'bobot_poin' => 10, 'deskripsi' => 'Datang sangat terlambat tanpa membawa surat/alasan yang dapat diterima.'],
                ['nama' => 'Atribut seragam tidak lengkap', 'bobot_poin' => 5, 'deskripsi' => 'Tidak memakai dasi, sabuk, topi, atau lokasi sekolah yang ditentukan.'],
                ['nama' => 'Baju tidak dimasukkan / seragam tidak rapi', 'bobot_poin' => 3, 'deskripsi' => 'Mengenakan seragam tidak sesuai aturan kerapian sekolah.'],
                ['nama' => 'Rambut gondrong / diwarnai / tidak rapi (putra)', 'bobot_poin' => 5, 'deskripsi' => 'Panjang rambut melebihi kerah baju/menutupi telinga atau diwarnai.'],
                ['nama' => 'Makeup berlebihan / perhiasan mencolok / kuku dicat', 'bobot_poin' => 5, 'deskripsi' => 'Menggunakan aksesori/makeup berlebihan atau memelihara/mengecat kuku.'],
                ['nama' => 'Main HP saat jam KBM tanpa izin', 'bobot_poin' => 10, 'deskripsi' => 'Menggunakan HP untuk keperluan pribadi di jam belajar tanpa instruksi guru.'],
                ['nama' => 'Membawa barang non-edukatif (Vape/Kartu Remi/Komik)', 'bobot_poin' => 15, 'deskripsi' => 'Membawa barang yang tidak berhubungan dengan kegiatan belajar.'],
            ];
            foreach ($kedisiplinan as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriKedisiplinan->id],
                    $item
                );
            }
        }

        // 2. Kehadiran & Akses
        if ($kategoriKehadiran) {
            $kehadiran = [
                ['nama' => 'Keluar kelas saat jam KBM tanpa izin', 'bobot_poin' => 10, 'deskripsi' => 'Meninggalkan ruang kelas saat pelajaran tanpa membawa kartu izin/alasan jelas.'],
                ['nama' => 'Bolos jam pelajaran tertentu', 'bobot_poin' => 15, 'deskripsi' => 'Sengaja tidak mengikuti jam pelajaran tertentu walau berada di area sekolah.'],
                ['nama' => 'Bolos sekolah / lompat pagar', 'bobot_poin' => 25, 'deskripsi' => 'Meninggalkan lingkungan sekolah sebelum jam belajar selesai tanpa izin.'],
                ['nama' => 'Alpa / tidak masuk tanpa keterangan', 'bobot_poin' => 5, 'deskripsi' => 'Tidak hadir sekolah tanpa memberikan surat perizinan dari orang tua/wali.'],
                ['nama' => 'Memalsukan tanda tangan / surat izin', 'bobot_poin' => 30, 'deskripsi' => 'Memalsukan dokumen perizinan, tanda tangan ortu, atau surat dokter.'],
            ];
            foreach ($kehadiran as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriKehadiran->id],
                    $item
                );
            }
        }

        // 3. Etika & Perilaku
        if ($kategoriEtika) {
            $etika = [
                ['nama' => 'Berkata kotor / tidak sopan kepada sesama', 'bobot_poin' => 5, 'deskripsi' => 'Mengucapkan kata-kata tidak pantas atau kotor di lingkungan sekolah.'],
                ['nama' => 'Membuat gaduh / keonaran saat KBM', 'bobot_poin' => 5, 'deskripsi' => 'Mengganggu suasana belajar mengajar kelas secara sengaja.'],
                ['nama' => 'Merusak fasilitas sekolah', 'bobot_poin' => 20, 'deskripsi' => 'Mencoret-coret meja, kursi, dinding atau merusak sarana prasarana sekolah.'],
                ['nama' => 'Merokok / vaping di area sekolah atau berseragam', 'bobot_poin' => 30, 'deskripsi' => 'Kedapatan merokok/vaping di lingkungan sekolah atau di luar mengenakan seragam.'],
                ['nama' => 'Melakukan tindakan perundungan / bullying', 'bobot_poin' => 30, 'deskripsi' => 'Merundung teman secara verbal, fisik, maupun media sosial.'],
                ['nama' => 'Melawan / membentak Guru atau Staf Sekolah', 'bobot_poin' => 40, 'deskripsi' => 'Menunjukkan sikap tidak sopan, membentak, atau menantang guru/staf.'],
            ];
            foreach ($etika as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriEtika->id],
                    $item
                );
            }
        }

        // 4. Pelanggaran Berat & Keamanan
        if ($kategoriBerat) {
            $berat = [
                ['nama' => 'Berkelahi / tawuran', 'bobot_poin' => 50, 'deskripsi' => 'Melakukan tindakan kekerasan fisik terhadap sesama siswa di dalam/luar sekolah.'],
                ['nama' => 'Membawa senjata tajam / berbahaya', 'bobot_poin' => 75, 'deskripsi' => 'Membawa pisau, gir, besi, atau benda berbahaya lain tanpa instruksi KBM.'],
                ['nama' => 'Membawa / mengonsumsi miras & narkoba', 'bobot_poin' => 100, 'deskripsi' => 'Kedapatan membawa, mengonsumsi, atau mengedarkan minuman keras/narkoba.'],
            ];
            foreach ($berat as $item) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $item['nama'], 'kategori_id' => $kategoriBerat->id],
                    $item
                );
            }
        }
    }
}

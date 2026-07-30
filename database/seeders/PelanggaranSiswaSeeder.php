<?php

namespace Database\Seeders;

use App\Models\JenisPelanggaran;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PoinPelanggaranService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PelanggaranSiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahunAkademik = TahunAkademik::where('is_aktif', true)->first() 
            ?? TahunAkademik::first();

        if (!$tahunAkademik) {
            $this->command->warn('Tahun akademik tidak ditemukan, seeder PelanggaranSiswa dilewati.');
            return;
        }

        $siswaList = Siswa::take(10)->get();
        if ($siswaList->isEmpty()) {
            $this->command->warn('Data siswa tidak ditemukan, seeder PelanggaranSiswa dilewati.');
            return;
        }

        $pencatat = User::withRole('guru_bk')->first() 
            ?? User::withRole('admin_sekolah')->first() 
            ?? User::withRole('operator')->first() 
            ?? User::first();

        $pencatatId = $pencatat ? $pencatat->id : 1;

        $jenisPelanggaran = JenisPelanggaran::all()->keyBy('nama');
        if ($jenisPelanggaran->isEmpty()) {
            $this->command->warn('Data jenis pelanggaran kosong, seeder PelanggaranSiswa dilewati.');
            return;
        }

        $sampleLogs = [
            // Siswa 1: Poin ringan - sedang (Terlambat & Seragam)
            0 => [
                ['jenis' => 'Terlambat masuk sekolah (< 15 menit)', 'harian' => 12, 'ket' => 'Terganggu kemacetan jalan raya, tiba pukul 07:12 WIB.'],
                ['jenis' => 'Atribut seragam tidak lengkap', 'harian' => 5, 'ket' => 'Lupa membawa dasi dan kaos kaki warna hitam.'],
                ['jenis' => 'Main HP saat jam KBM tanpa izin', 'harian' => 2, 'ket' => 'Membuka media sosial saat guru sedang menerangkan di depan.'],
            ],
            // Siswa 2: Poin sedang (Bolos & Terlambat)
            1 => [
                ['jenis' => 'Terlambat masuk sekolah (> 15 menit)', 'harian' => 20, 'ket' => 'Bangun kesiangan, tiba di sekolah pukul 07:35 WIB.'],
                ['jenis' => 'Keluar kelas saat jam KBM tanpa izin', 'harian' => 14, 'ket' => 'Izin ke toilet namun nongkrong di dekat kantin.'],
                ['jenis' => 'Bolos jam pelajaran tertentu', 'harian' => 7, 'ket' => 'Sengaja tidak mengikuti jam pelajaran Matematika.'],
                ['jenis' => 'Rambut gondrong / diwarnai / tidak rapi (putra)', 'harian' => 1, 'ket' => 'Panjang rambut sudah melewati kerah baju dan menutup telinga.'],
            ],
            // Siswa 3: Poin tinggi (Sp1/Sp2 trigger - Bullying/Merokok/Tawuran)
            2 => [
                ['jenis' => 'Merokok / vaping di area sekolah atau berseragam', 'harian' => 25, 'ket' => 'Kedapatan merokok di area belakang kamar mandi siswa.'],
                ['nama_alt' => 'Memalsukan tanda tangan / surat izin', 'jenis' => 'Memalsukan tanda tangan / surat izin', 'harian' => 18, 'ket' => 'Memalsukan TTD orang tua pada surat izin sakit.'],
                ['jenis' => 'Bolos sekolah / lompat pagar', 'harian' => 10, 'ket' => 'Lompat pagar belakang sekolah pada jam istirahat pertama.'],
            ],
            // Siswa 4: Kasus ringan
            3 => [
                ['jenis' => 'Baju tidak dimasukkan / seragam tidak rapi', 'harian' => 15, 'ket' => 'Seragam dikeluarkan saat berpindah kelas KBM.'],
                ['jenis' => 'Terlambat masuk sekolah (< 15 menit)', 'harian' => 8, 'ket' => 'Ban sepeda gembos di jalan.'],
            ],
            // Siswa 5: Perilaku & Fasilitas
            4 => [
                ['jenis' => 'Merusak fasilitas sekolah', 'harian' => 22, 'ket' => 'Mencoret meja kelas menggunakan spidol permanen.'],
                ['jenis' => 'Berkata kotor / tidak sopan kepada sesama', 'harian' => 11, 'ket' => 'Mengucapkan kata kasar kepada teman saat berdiskusi kelompok.'],
                ['jenis' => 'Membawa barang non-edukatif (Vape/Kartu Remi/Komik)', 'harian' => 3, 'ket' => 'Membawa kartu remi dan bermain saat jam pelajaran kosong.'],
            ]
        ];

        $poinService = new PoinPelanggaranService();

        foreach ($sampleLogs as $index => $logs) {
            if (!isset($siswaList[$index])) {
                continue;
            }

            $siswa = $siswaList[$index];

            foreach ($logs as $log) {
                $jenisName = $log['jenis'];
                if (!$jenisPelanggaran->has($jenisName)) {
                    continue;
                }

                $jenis = $jenisPelanggaran->get($jenisName);
                $tanggal = Carbon::now()->subDays($log['harian'])->toDateString();

                PelanggaranSiswa::create([
                    'siswa_id' => $siswa->id,
                    'jenis_id' => $jenis->id,
                    'tahun_akademik_id' => $tahunAkademik->id,
                    'tanggal_kejadian' => $tanggal,
                    'keterangan' => $log['ket'],
                    'poin_saat_itu' => $jenis->bobot_poin,
                    'dicatat_oleh' => $pencatatId,
                    'is_diarsipkan' => false,
                ]);
            }

            // Hitung akumulasi poin & terbitkan SP otomatis jika memenuhi batas
            try {
                $poinService->checkAndTriggerSp($siswa->id, $tahunAkademik->id);
            } catch (\Exception $e) {
                // Ignore SP trigger error in seeder if any
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use App\Models\KonfigurasiPelanggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service untuk memproses poin pelanggaran siswa dan penanganan SP (Surat Peringatan)
 */
class PoinPelanggaranService
{
    /**
     * Menghitung total poin siswa pada tahun akademik tertentu berdasarkan pelanggaran yang aktif.
     *
     * @param int $siswaId
     * @param int $tahunAkademikId
     * @return int
     */
    public function calculateAccumulatedPoints(int $siswaId, int $tahunAkademikId): int
    {
        return (int) PelanggaranSiswa::query()
            ->where('siswa_id', $siswaId)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->sum('poin_saat_itu');
    }

    /**
     * Memeriksa akumulasi poin siswa dan menerbitkan SP (Surat Peringatan) jika melewati ambang batas.
     * Logika sekuensial diterapkan untuk mengantisipasi lompatan poin yang drastis,
     * sehingga SP tingkat di bawahnya juga diterbitkan untuk menjaga historisitas data.
     * 
     * Menggunakan DB Transaction & locking (sharedLock/lockForUpdate) untuk mencegah race condition.
     *
     * @param int $siswaId
     * @param int $tahunAkademikId
     * @param int|null $poinTerbaru
     * @return PelanggaranSp|null
     * @throws Exception
     */
    public function checkAndTriggerSp(int $siswaId, int $tahunAkademikId, ?int $poinTerbaru = null): ?PelanggaranSp
    {
        return DB::transaction(function () use ($siswaId, $tahunAkademikId, $poinTerbaru) {
            // 1. Dapatkan konfigurasi SP (KonfigurasiPelanggaran) untuk tahun akademik tersebut
            $konfigurasi = KonfigurasiPelanggaran::query()
                ->where('tahun_akademik_id', $tahunAkademikId)
                ->first();

            // Batas default jika tidak dikonfigurasi
            $batasSp1 = $konfigurasi ? $konfigurasi->batas_sp1 : 25;
            $batasSp2 = $konfigurasi ? $konfigurasi->batas_sp2 : 50;
            $batasSp3 = $konfigurasi ? $konfigurasi->batas_sp3 : 75;

            // 2. Hitung total akumulasi poin saat ini. Jika $poinTerbaru tidak dikirim, hitung manual dari database.
            // Gunakan lockForUpdate pada query pelanggaran_siswa untuk memastikan data terbaru dan konsisten
            if ($poinTerbaru === null) {
                $poinTerbaru = (int) PelanggaranSiswa::query()
                    ->where('siswa_id', $siswaId)
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->lockForUpdate()
                    ->sum('poin_saat_itu');
            }

            // 3. Ambil SP yang sudah ada untuk siswa pada tahun akademik ini dengan locking
            $existingSpList = PelanggaranSp::query()
                ->where('siswa_id', $siswaId)
                ->where('tahun_akademik_id', $tahunAkademikId)
                ->lockForUpdate()
                ->get()
                ->keyBy('level_sp');

            $spTerbaruDibuat = null;

            // Kita periksa secara sekuensial dari SP1, SP2, lalu SP3
            // Aturan: Jika poin melompat langsung melewati batas SP2/SP3, terbitkan SP1, SP2, SP3 satu per satu
            
            // Evaluasi SP1
            if ($poinTerbaru >= $batasSp1 && !$existingSpList->has('SP1')) {
                $spTerbaruDibuat = PelanggaranSp::create([
                    'siswa_id' => $siswaId,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'level_sp' => 'SP1',
                    'total_poin_saat_sp' => $poinTerbaru,
                    'tanggal_sp' => now()->toDateString(),
                    'catatan_tambahan' => 'Diterbitkan otomatis karena total poin mencapai batas minimal SP1.',
                    'diterbitkan_oleh' => auth()->id(),
                ]);
            }

            // Evaluasi SP2
            if ($poinTerbaru >= $batasSp2 && !$existingSpList->has('SP2')) {
                $spTerbaruDibuat = PelanggaranSp::create([
                    'siswa_id' => $siswaId,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'level_sp' => 'SP2',
                    'total_poin_saat_sp' => $poinTerbaru,
                    'tanggal_sp' => now()->toDateString(),
                    'catatan_tambahan' => 'Diterbitkan otomatis karena total poin mencapai batas minimal SP2.',
                    'diterbitkan_oleh' => auth()->id(),
                ]);
            }

            // Evaluasi SP3
            if ($poinTerbaru >= $batasSp3 && !$existingSpList->has('SP3')) {
                $spTerbaruDibuat = PelanggaranSp::create([
                    'siswa_id' => $siswaId,
                    'tahun_akademik_id' => $tahunAkademikId,
                    'level_sp' => 'SP3',
                    'total_poin_saat_sp' => $poinTerbaru,
                    'tanggal_sp' => now()->toDateString(),
                    'catatan_tambahan' => 'Diterbitkan otomatis karena total poin mencapai batas minimal SP3.',
                    'diterbitkan_oleh' => auth()->id(),
                ]);
            }

            return $spTerbaruDibuat;
        });
    }

    /**
     * Melakukan rekalkulasi total poin dan pengecekan SP setelah terjadi perubahan data (misal penghapusan pelanggaran).
     * Sesuai BR-08, jika poin turun setelah penghapusan, SP yang sudah telanjur diterbitkan TETAP berlaku.
     *
     * @param int $siswaId
     * @param int $tahunAkademikId
     * @return void
     * @throws Exception
     */
    public function recalculatePointsAndSp(int $siswaId, int $tahunAkademikId): void
    {
        DB::transaction(function () use ($siswaId, $tahunAkademikId) {
            // Hitung ulang akumulasi poin siswa saat ini (tidak termasuk soft-deleted records)
            $totalPoin = $this->calculateAccumulatedPoints($siswaId, $tahunAkademikId);

            // Jalankan penanganan SP (jika ada poin yang bertambah atau perubahan yang memicu SP baru,
            // namun karena ini adalah proses pengurangan, biasanya tidak memicu SP baru.
            // Namun, jika ada pelanggaran tersisa yang belum memicu SP yang tertunda, method ini akan tetap memastikannya)
            $this->checkAndTriggerSp($siswaId, $tahunAkademikId, $totalPoin);
        });
    }
}

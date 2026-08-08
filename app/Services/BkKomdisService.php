<?php

namespace App\Services;

use App\Models\BkKasus;
use App\Models\BkLogKonseling;
use App\Models\KomdisSanksi;
use App\Models\KomdisSidang;
use App\Models\PelanggaranPemutihanLog;
use App\Models\PelanggaranSiswa;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use Exception;

class BkKomdisService
{
    protected PoinPelanggaranService $poinService;

    public function __construct(PoinPelanggaranService $poinService)
    {
        $this->poinService = $poinService;
    }

    /**
     * Auto referral kasus BK jika poin siswa menyentuh threshold SP.
     */
    public function autoReferralKasusBk(int $siswaId, string $levelSp, int $totalPoin, ?string $catatan = null): BkKasus
    {
        return DB::transaction(function () use ($siswaId, $levelSp, $totalPoin, $catatan) {
            $siswa = Siswa::findOrFail($siswaId);

            // Cek apakah sudah ada kasus terbuka/proses dengan judul referral SP yang sama
            $existingKasus = BkKasus::where('siswa_id', $siswaId)
                ->where('kategori', 'disiplin')
                ->whereIn('status', ['terbuka', 'dalam_proses'])
                ->where('judul_kasus', 'like', "%Referral Otomatis - {$levelSp}%")
                ->first();

            if ($existingKasus) {
                return $existingKasus;
            }

            $tingkatKeparahan = match ($levelSp) {
                'SP3' => 'sangat_berat',
                'SP2' => 'berat',
                'SP1' => 'sedang',
                default => 'ringan',
            };

            return BkKasus::create([
                'siswa_id' => $siswaId,
                'guru_bk_id' => null,
                'judul_kasus' => "Referral Otomatis - {$levelSp} ({$totalPoin} Poin)",
                'deskripsi' => $catatan ?? "Siswa {$siswa->nama_lengkap} (NIS: {$siswa->nis}) otomatis dirujuk ke BK karena akumulasi poin pelanggaran mencapai {$totalPoin} poin ({$levelSp}).",
                'kategori' => 'disiplin',
                'tingkat_keparahan' => $tingkatKeparahan,
                'status' => 'terbuka',
                'tanggal_lapor' => now()->toDateString(),
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Memproses pendaftaran / pencatatan sesi konseling BK.
     */
    public function tambahLogKonseling(array $data): BkLogKonseling
    {
        return DB::transaction(function () use ($data) {
            $log = BkLogKonseling::create([
                'bk_kasus_id' => $data['bk_kasus_id'] ?? null,
                'siswa_id' => $data['siswa_id'],
                'guru_bk_id' => $data['guru_bk_id'],
                'tanggal_konseling' => $data['tanggal_konseling'] ?? now()->toDateString(),
                'waktu_mulai' => $data['waktu_mulai'] ?? null,
                'waktu_selesai' => $data['waktu_selesai'] ?? null,
                'jenis_konseling' => $data['jenis_konseling'] ?? 'individu',
                'ringkasan_masalah' => $data['ringkasan_masalah'],
                'hasil_konseling' => $data['hasil_konseling'] ?? null,
                'rencana_tindak_lanjut' => $data['rencana_tindak_lanjut'] ?? null,
                'status_tindak_lanjut' => $data['status_tindak_lanjut'] ?? 'belum',
                'created_by' => auth()->id(),
            ]);

            // Jika terhubung ke kasus BK, ubah status kasus ke 'dalam_proses' jika masih 'terbuka'
            if (!empty($data['bk_kasus_id'])) {
                $kasus = BkKasus::find($data['bk_kasus_id']);
                if ($kasus && $kasus->status === 'terbuka') {
                    $kasus->update(['status' => 'dalam_proses']);
                }
            }

            return $log;
        });
    }

    /**
     * Memproses eksekusi pemutihan poin pelanggaran siswa.
     */
    public function eksekusiPemutihan(array $data): PelanggaranPemutihanLog
    {
        return DB::transaction(function () use ($data) {
            $siswaId = $data['siswa_id'];
            $poinYangDiputihkan = (int) $data['poin_yang_diputihkan'];

            $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();
            $taId = $ta ? $ta->id : null;

            // Hitung akumulasi poin saat ini
            $poinSebelum = $taId ? $this->poinService->calculateAccumulatedPoints($siswaId, $taId) : 0;

            // Poin sesudah pemutihan (tidak boleh minus)
            $poinSesudah = max(0, $poinSebelum - $poinYangDiputihkan);

            $logPemutihan = PelanggaranPemutihanLog::create([
                'siswa_id' => $siswaId,
                'tanggal_pemutihan' => $data['tanggal_pemutihan'] ?? now()->toDateString(),
                'poin_sebelum' => $poinSebelum,
                'poin_sesudah' => $poinSesudah,
                'poin_yang_diputihkan' => $poinYangDiputihkan,
                'alasan_pemutihan' => $data['alasan_pemutihan'],
                'diproses_oleh' => auth()->id(),
            ]);

            // Arsipkan atau kurangi catatan pelanggaran siswa secara proporsional jika diminta
            if (!empty($data['arsipkan_pelanggaran'])) {
                PelanggaranSiswa::where('siswa_id', $siswaId)
                    ->where('is_diarsipkan', false)
                    ->update(['is_diarsipkan' => true]);
            }

            return $logPemutihan;
        });
    }

    /**
     * Pendaftaran / Penjadwalan Sidang Komdis.
     */
    public function tambahSidangKomdis(array $data): KomdisSidang
    {
        return DB::transaction(function () use ($data) {
            $sidang = KomdisSidang::create([
                'siswa_id' => $data['siswa_id'],
                'bk_kasus_id' => $data['bk_kasus_id'] ?? null,
                'tanggal_sidang' => $data['tanggal_sidang'],
                'waktu_sidang' => $data['waktu_sidang'] ?? null,
                'lokasi_sidang' => $data['lokasi_sidang'] ?? null,
                'agenda' => $data['agenda'],
                'deskripsi_pelanggaran' => $data['deskripsi_pelanggaran'],
                'keputusan_sidang' => $data['keputusan_sidang'] ?? null,
                'status' => $data['status'] ?? 'terjadwal',
                'pimpinan_sidang_id' => $data['pimpinan_sidang_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update status kasus BK terkait jika ada
            if (!empty($data['bk_kasus_id'])) {
                $kasus = BkKasus::find($data['bk_kasus_id']);
                if ($kasus) {
                    $kasus->update(['status' => 'dirujuk']);
                }
            }

            return $sidang;
        });
    }

    /**
     * Eskalasi kasus BK ke Sidang Komdis.
     */
    public function eskalasiKasusKeKomdis(int $bkKasusId, array $sidangData): KomdisSidang
    {
        return DB::transaction(function () use ($bkKasusId, $sidangData) {
            $kasus = BkKasus::findOrFail($bkKasusId);
            $kasus->update(['status' => 'dirujuk']);

            $sidangData['bk_kasus_id'] = $kasus->id;
            $sidangData['siswa_id'] = $kasus->siswa_id;

            return $this->tambahSidangKomdis($sidangData);
        });
    }

    /**
     * Penetapan Sanksi Komdis.
     */
    public function tetapkanSanksi(array $data): KomdisSanksi
    {
        return DB::transaction(function () use ($data) {
            return KomdisSanksi::create([
                'siswa_id' => $data['siswa_id'],
                'komdis_sidang_id' => $data['komdis_sidang_id'] ?? null,
                'nama_sanksi' => $data['nama_sanksi'],
                'deskripsi_sanksi' => $data['deskripsi_sanksi'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'] ?? now()->toDateString(),
                'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
                'status' => $data['status'] ?? 'aktif',
                'diberikan_oleh' => $data['diberikan_oleh'] ?? null,
            ]);
        });
    }
}

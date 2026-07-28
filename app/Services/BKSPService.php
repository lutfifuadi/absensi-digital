<?php

namespace App\Services;

use App\Models\PelanggaranSiswa;
use App\Models\PelanggaranSp;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use Exception;

class BKSPService
{
    protected PoinPelanggaranService $poinService;

    public function __construct(PoinPelanggaranService $poinService)
    {
        $this->poinService = $poinService;
    }

    /**
     * Issue a formal SP (Surat Peringatan) for a student.
     */
    public function issueSp(array $data): PelanggaranSp
    {
        return DB::transaction(function () use ($data) {
            $siswaId = $data['siswa_id'];
            $levelSp = $data['level_sp']; // 'SP1', 'SP2', 'SP3'
            $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();

            if (!$ta) {
                throw new Exception('Tahun akademik aktif tidak ditemukan.');
            }

            // Check existing SP history for this academic year
            $existingSpList = PelanggaranSp::where('siswa_id', $siswaId)
                ->where('tahun_akademik_id', $ta->id)
                ->get()
                ->keyBy('level_sp');

            // BR-3: Level SP harus berurutan (SP1 -> SP2 -> SP3)
            if ($levelSp === 'SP2' && !$existingSpList->has('SP1')) {
                throw new Exception('Penerbitan SP2 memerlukan keberadaan SP1 terlebih dahulu.');
            }
            if ($levelSp === 'SP3' && !$existingSpList->has('SP2')) {
                throw new Exception('Penerbitan SP3 memerlukan keberadaan SP2 terlebih dahulu.');
            }
            if ($existingSpList->has($levelSp)) {
                throw new Exception("Surat Peringatan {$levelSp} sudah pernah diterbitkan untuk siswa ini pada tahun akademik aktif.");
            }

            // Calculate total points at the moment of SP issuance
            $totalPoin = $this->poinService->calculateAccumulatedPoints($siswaId, $ta->id);

            $sp = PelanggaranSp::create([
                'siswa_id' => $siswaId,
                'tahun_akademik_id' => $ta->id,
                'level_sp' => $levelSp,
                'total_poin_saat_sp' => $totalPoin,
                'tanggal_sp' => $data['tanggal_sp'] ?? now()->toDateString(),
                'catatan_tambahan' => $data['catatan_tambahan'] ?? null,
                'diterbitkan_oleh' => auth()->id(),
            ]);

            return $sp;
        });
    }

    /**
     * Get list of SP records with filters.
     */
    public function getSpList(array $filters = [], int $perPage = 15)
    {
        $query = PelanggaranSp::with(['siswa.kelas', 'penerbit', 'tahunAkademik']);

        if (!empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }

        if (!empty($filters['level_sp'])) {
            $query->where('level_sp', $filters['level_sp']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        return $query->latest('tanggal_sp')->paginate($perPage);
    }
}

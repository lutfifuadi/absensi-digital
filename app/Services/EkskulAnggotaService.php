<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class EkskulAnggotaService
{
    /**
     * Daftar anggota per ekskul.
     *
     * @param  int  $ekskulId
     * @return Collection
     */
    public function getAnggota(int $ekskulId): Collection
    {
        return EkskulAnggota::with('siswa:id,nama_lengkap,nis,kelas_id')
            ->where('ekskul_id', $ekskulId)
            ->latest()
            ->get();
    }

    /**
     * Daftarkan siswa ke ekskul.
     * Cek kuota dan duplikat terlebih dahulu.
     *
     * @param  int  $ekskulId
     * @param  int  $siswaId
     * @return EkskulAnggota
     *
     * @throws \InvalidArgumentException
     */
    public function addAnggota(int $ekskulId, int $siswaId): EkskulAnggota
    {
        return DB::transaction(function () use ($ekskulId, $siswaId) {
            $ekskul = Ekskul::where('id', $ekskulId)
                ->lockForUpdate()
                ->withCount('anggota')
                ->firstOrFail();

            if (!$ekskul->status) {
                throw new \InvalidArgumentException('Ekskul sedang nonaktif.');
            }

            // Cek duplikat di dalam transaksi
            $exists = EkskulAnggota::where('ekskul_id', $ekskulId)
                ->where('siswa_id', $siswaId)
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('Siswa sudah terdaftar di ekskul ini.');
            }

            // Cek kuota
            if ($ekskul->kuota && $ekskul->anggota_count >= $ekskul->kuota) {
                throw new \InvalidArgumentException('Kuota ekskul sudah penuh.');
            }

            $anggota = EkskulAnggota::create([
                'ekskul_id'     => $ekskulId,
                'siswa_id'      => $siswaId,
                'status'        => 'aktif',
                'tanggal_masuk' => now()->toDateString(),
            ]);

            ActivityLog::record(
                'create',
                'ekskul_anggota',
                "Siswa ID {$siswaId} ditambahkan ke ekskul {$ekskul->nama}",
                null,
                $anggota->toArray()
            );

            return $anggota->load('siswa:id,nama_lengkap,nis');
        });
    }

    /**
     * Hapus anggota dari ekskul.
     *
     * @param  int  $id
     * @return bool
     */
    public function removeAnggota(int $id): bool
    {
        $anggota = EkskulAnggota::with('ekskul')->findOrFail($id);

        ActivityLog::record(
            'delete',
            'ekskul_anggota',
            "Siswa ID {$anggota->siswa_id} dikeluarkan dari ekskul {$anggota->ekskul->nama}",
            $anggota->toArray(),
            null
        );

        return $anggota->delete();
    }

    /**
     * Update status anggota (aktif/cuti/keluar).
     *
     * @param  int     $id
     * @param  string  $status
     * @return EkskulAnggota
     *
     * @throws \InvalidArgumentException
     */
    public function updateStatus(int $id, string $status): EkskulAnggota
    {
        $validStatuses = ['aktif', 'cuti', 'keluar'];

        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException("Status tidak valid. Gunakan: " . implode(', ', $validStatuses));
        }

        $anggota = EkskulAnggota::with('ekskul')->findOrFail($id);

        $updateData = ['status' => $status];

        // Jika keluar, catat tanggal keluar
        if ($status === 'keluar') {
            $updateData['tanggal_keluar'] = now()->toDateString();
        }

        $anggota->update($updateData);

        ActivityLog::record(
            'update',
            'ekskul_anggota',
            "Status anggota ekskul {$anggota->ekskul->nama} diubah menjadi {$status}",
            ['status' => $anggota->getOriginal('status')],
            ['status' => $status]
        );

        return $anggota->fresh();
    }
}

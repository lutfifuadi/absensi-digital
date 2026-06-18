<?php

namespace App\Services;

use App\Models\Ekskul;
use App\Models\EkskulJadwal;
use App\Models\EkskulPembina;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EkskulService
{
    /**
     * Daftar ekskul dengan filter opsional.
     *
     * @param  array  $filters  ['kategori', 'status', 'search']
     * @param  int    $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Ekskul::query()->withCount('anggota');

        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Detail ekskul dengan jadwal, pembina, dan jumlah anggota.
     *
     * @param  int  $id
     * @return Ekskul
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Ekskul
    {
        return Ekskul::with([
            'jadwal',
            'pembina.guru',
            'anggota.siswa',
        ])->withCount('anggota')->findOrFail($id);
    }

    /**
     * Buat ekskul baru beserta jadwal dan pembina.
     *
     * @param  array  $data
     * @return Ekskul
     */
    public function create(array $data): Ekskul
    {
        return DB::transaction(function () use ($data) {
            $ekskul = Ekskul::create([
                'nama'      => $data['nama'],
                'kategori'  => $data['kategori'] ?? 'pilihan',
                'deskripsi' => $data['deskripsi'] ?? null,
                'kuota'     => $data['kuota'] ?? null,
                'status'    => $data['status'] ?? true,
                'icon'      => $data['icon'] ?? null,
            ]);

            // Simpan jadwal jika ada
            if (!empty($data['jadwal']) && is_array($data['jadwal'])) {
                foreach ($data['jadwal'] as $jadwal) {
                    EkskulJadwal::create([
                        'ekskul_id'   => $ekskul->id,
                        'hari'        => $jadwal['hari'],
                        'jam_mulai'   => $jadwal['jam_mulai'],
                        'jam_selesai' => $jadwal['jam_selesai'],
                        'lokasi'      => $jadwal['lokasi'],
                    ]);
                }
            }

            // Simpan pembina jika ada
            if (!empty($data['pembina']) && is_array($data['pembina'])) {
                foreach ($data['pembina'] as $pembina) {
                    EkskulPembina::create([
                        'ekskul_id' => $ekskul->id,
                        'guru_id'   => $pembina['guru_id'],
                        'jabatan'   => $pembina['jabatan'] ?? null,
                    ]);
                }
            }

            ActivityLog::record(
                'create',
                'ekskul',
                "Ekskul baru: {$ekskul->nama}",
                null,
                $ekskul->toArray()
            );

            return $ekskul;
        });
    }

    /**
     * Update data ekskul.
     *
     * @param  int    $id
     * @param  array  $data
     * @return Ekskul
     */
    public function update(int $id, array $data): Ekskul
    {
        return DB::transaction(function () use ($id, $data) {
            $ekskul = Ekskul::findOrFail($id);

            $oldData = $ekskul->toArray();

            $ekskul->update([
                'nama'      => $data['nama'] ?? $ekskul->nama,
                'kategori'  => $data['kategori'] ?? $ekskul->kategori,
                'deskripsi' => $data['deskripsi'] ?? $ekskul->deskripsi,
                'kuota'     => $data['kuota'] ?? $ekskul->kuota,
                'status'    => $data['status'] ?? $ekskul->status,
                'icon'      => $data['icon'] ?? $ekskul->icon,
            ]);

            // Update jadwal (replace all)
            if (array_key_exists('jadwal', $data)) {
                $ekskul->jadwal()->delete();
                if (!empty($data['jadwal']) && is_array($data['jadwal'])) {
                    foreach ($data['jadwal'] as $jadwal) {
                        EkskulJadwal::create([
                            'ekskul_id'   => $ekskul->id,
                            'hari'        => $jadwal['hari'],
                            'jam_mulai'   => $jadwal['jam_mulai'],
                            'jam_selesai' => $jadwal['jam_selesai'],
                            'lokasi'      => $jadwal['lokasi'],
                        ]);
                    }
                }
            }

            // Update pembina (replace all)
            if (array_key_exists('pembina', $data)) {
                $ekskul->pembina()->delete();
                if (!empty($data['pembina']) && is_array($data['pembina'])) {
                    foreach ($data['pembina'] as $pembina) {
                        EkskulPembina::create([
                            'ekskul_id' => $ekskul->id,
                            'guru_id'   => $pembina['guru_id'],
                            'jabatan'   => $pembina['jabatan'] ?? null,
                        ]);
                    }
                }
            }

            ActivityLog::record(
                'update',
                'ekskul',
                "Update ekskul: {$ekskul->nama}",
                $oldData,
                $ekskul->fresh()->toArray()
            );

            return $ekskul->fresh();
        });
    }

    /**
     * Soft delete ekskul.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $ekskul = Ekskul::findOrFail($id);

        ActivityLog::record(
            'delete',
            'ekskul',
            "Hapus ekskul: {$ekskul->nama}",
            $ekskul->toArray(),
            null
        );

        return $ekskul->delete();
    }

    /**
     * Toggle status aktif/nonaktif ekskul.
     *
     * @param  int  $id
     * @return Ekskul
     */
    public function toggleStatus(int $id): Ekskul
    {
        $ekskul = Ekskul::findOrFail($id);
        $newStatus = !$ekskul->status;

        $ekskul->update(['status' => $newStatus]);

        ActivityLog::record(
            'update',
            'ekskul',
            ($newStatus ? 'Mengaktifkan' : 'Menonaktifkan') . " ekskul: {$ekskul->nama}",
            ['status' => !$newStatus],
            ['status' => $newStatus]
        );

        return $ekskul->fresh();
    }
}

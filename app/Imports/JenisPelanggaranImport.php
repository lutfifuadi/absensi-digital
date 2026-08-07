<?php

namespace App\Imports;

use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;

class JenisPelanggaranImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    protected int $importedCount = 0;
    protected int $updatedCount = 0;

    public function model(array $row)
    {
        // 1. Dapatkan nama jenis pelanggaran
        $nama = null;
        foreach (['nama_pelanggaran', 'nama', 'jenis_pelanggaran', 'jenis'] as $key) {
            if (!empty($row[$key])) {
                $nama = trim((string) $row[$key]);
                break;
            }
        }

        if (!$nama) {
            return null;
        }

        $nama = mb_substr($nama, 0, 150);

        // 2. Dapatkan nama kategori / kategori_id
        $kategoriNama = null;
        foreach (['nama_kategori', 'kategori', 'kategori_pelanggaran'] as $key) {
            if (!empty($row[$key])) {
                $kategoriNama = trim((string) $row[$key]);
                break;
            }
        }

        $kategori = null;
        if ($kategoriNama) {
            $kategori = KategoriPelanggaran::where('nama', $kategoriNama)->first();
        }

        if (!$kategori) {
            $kategori = KategoriPelanggaran::firstOrCreate(
                ['nama' => $kategoriNama ?: 'Kedisiplinan & Kerapian'],
                ['deskripsi' => 'Kategori umum pelanggaran tata tertib.', 'warna' => '#ef4444', 'is_aktif' => true]
            );
        }

        // 3. Dapatkan bobot poin (0 - 255)
        $bobotPoin = 5;
        foreach (['bobot_poin', 'poin', 'bobot', 'poin_pelanggaran'] as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                $bobotPoin = (int) $row[$key];
                break;
            }
        }
        $bobotPoin = max(0, min(255, $bobotPoin));

        // 4. Deskripsi
        $deskripsi = null;
        foreach (['deskripsi', 'keterangan', 'detail'] as $key) {
            if (isset($row[$key])) {
                $d = trim((string) $row[$key]);
                $deskripsi = $d !== '' ? $d : null;
                break;
            }
        }

        // 5. Status Aktif
        $isAktif = true;
        foreach ($row as $key => $val) {
            if (str_contains($key, 'status') || str_contains($key, 'aktif')) {
                $valStr = strtolower(trim((string) $val));
                if ($valStr === '0' || $valStr === 'nonaktif' || $valStr === 'false' || $valStr === 'tidak') {
                    $isAktif = false;
                }
                break;
            }
        }

        $existing = JenisPelanggaran::where('nama', $nama)->first();

        if ($existing) {
            $existing->update([
                'kategori_id' => $kategori->id,
                'bobot_poin'  => $bobotPoin,
                'deskripsi'   => $deskripsi ?? $existing->deskripsi,
                'is_aktif'    => $isAktif,
            ]);
            $this->updatedCount++;
            return null;
        }

        $this->importedCount++;

        return new JenisPelanggaran([
            'kategori_id' => $kategori->id,
            'nama'        => $nama,
            'bobot_poin'  => $bobotPoin,
            'deskripsi'   => $deskripsi,
            'is_aktif'    => $isAktif,
        ]);
    }

    public function getImportResult(): array
    {
        return [
            'imported' => $this->importedCount,
            'updated'  => $this->updatedCount,
            'total'    => $this->importedCount + $this->updatedCount,
        ];
    }
}

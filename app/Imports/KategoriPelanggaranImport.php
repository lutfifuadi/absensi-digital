<?php

namespace App\Imports;

use App\Models\KategoriPelanggaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\Importable;

class KategoriPelanggaranImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    protected int $importedCount = 0;
    protected int $updatedCount = 0;

    public function model(array $row)
    {
        // Temukan nama dari berbagai kemungkinan key heading
        $nama = null;
        foreach (['nama_kategori', 'nama', 'kategori', 'nama_kategori_pelanggaran'] as $key) {
            if (!empty($row[$key])) {
                $nama = trim((string) $row[$key]);
                break;
            }
        }

        // Jika tidak ada nama, lewati baris ini
        if (!$nama) {
            return null;
        }

        // Sesuai aturan DB: nama max 100 karakter
        $nama = mb_substr($nama, 0, 100);

        $deskripsi = null;
        foreach (['deskripsi', 'keterangan', 'detail'] as $key) {
            if (isset($row[$key])) {
                $d = trim((string) $row[$key]);
                $deskripsi = $d !== '' ? $d : null;
                break;
            }
        }

        // Sesuai aturan DB: warna hex max 7 karakter (e.g. #ef4444)
        $warna = '#ef4444';
        foreach (['warna_hex', 'warna', 'color'] as $key) {
            if (!empty($row[$key])) {
                $w = trim((string) $row[$key]);
                if (preg_match('/^#[a-fA-F0-9]{6}$/', $w) || preg_match('/^#[a-fA-F0-9]{3}$/', $w)) {
                    $warna = $w;
                }
                break;
            }
        }
        $warna = mb_substr($warna, 0, 7);

        // Sesuai aturan DB: urutan unsigned tinyInteger (0 - 255)
        $urutan = 0;
        foreach (['urutan', 'order', 'no_urutan'] as $key) {
            if (isset($row[$key]) && is_numeric($row[$key])) {
                $urutan = (int) $row[$key];
                break;
            }
        }
        $urutan = max(0, min(255, $urutan));

        // Sesuai aturan DB: is_aktif boolean
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

        $existing = KategoriPelanggaran::where('nama', $nama)->first();

        if ($existing) {
            $existing->update([
                'deskripsi' => $deskripsi ?? $existing->deskripsi,
                'warna'     => $warna ?: $existing->warna,
                'urutan'    => $urutan,
                'is_aktif'  => $isAktif,
            ]);
            $this->updatedCount++;
            return null;
        }

        $this->importedCount++;

        return new KategoriPelanggaran([
            'nama'      => $nama,
            'deskripsi' => $deskripsi,
            'warna'     => $warna,
            'urutan'    => $urutan,
            'is_aktif'  => $isAktif,
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

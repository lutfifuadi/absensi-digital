<?php

namespace App\Exports;

use App\Models\KategoriPelanggaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KategoriPelanggaranExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;
    protected $isAktif;

    public function __construct($search = null, $isAktif = null)
    {
        $this->search = $search;
        $this->isAktif = $isAktif;
    }

    public function collection()
    {
        $query = KategoriPelanggaran::query()->withCount('jenisPelanggaran');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('deskripsi', 'like', "%{$this->search}%");
            });
        }

        if ($this->isAktif !== null && $this->isAktif !== '') {
            $query->where('is_aktif', $this->isAktif);
        }

        return $query->orderBy('urutan')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kategori',
            'Deskripsi',
            'Warna Hex',
            'Urutan',
            'Status Aktif',
            'Jumlah Jenis Pelanggaran'
        ];
    }

    /**
     * @param KategoriPelanggaran $kategori
     */
    public function map($kategori): array
    {
        return [
            $kategori->nama,
            $kategori->deskripsi ?? '',
            $kategori->warna ?? '#ef4444',
            $kategori->urutan ?? 0,
            $kategori->is_aktif ? 'Aktif' : 'Nonaktif',
            $kategori->jenis_pelanggaran_count ?? 0,
        ];
    }
}

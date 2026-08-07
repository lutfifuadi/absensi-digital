<?php

namespace App\Exports;

use App\Models\JenisPelanggaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JenisPelanggaranExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $search;
    protected $kategoriId;
    protected $isAktif;

    public function __construct($search = null, $kategoriId = null, $isAktif = null)
    {
        $this->search = $search;
        $this->kategoriId = $kategoriId;
        $this->isAktif = $isAktif;
    }

    public function collection()
    {
        $query = JenisPelanggaran::query()->with(['kategori'])->withCount('pelanggaranSiswa');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                  ->orWhere('deskripsi', 'like', "%{$this->search}%");
            });
        }

        if ($this->kategoriId) {
            $query->where('kategori_id', $this->kategoriId);
        }

        if ($this->isAktif !== null && $this->isAktif !== '') {
            $query->where('is_aktif', $this->isAktif);
        }

        return $query->orderBy('kategori_id')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kategori',
            'Nama Pelanggaran',
            'Bobot Poin',
            'Deskripsi',
            'Status Aktif',
            'Jumlah Pelanggaran Siswa'
        ];
    }

    /**
     * @param JenisPelanggaran $jenis
     */
    public function map($jenis): array
    {
        return [
            $jenis->kategori->nama ?? 'Kedisiplinan & Kerapian',
            $jenis->nama,
            $jenis->bobot_poin,
            $jenis->deskripsi ?? '',
            $jenis->is_aktif ? 'Aktif' : 'Nonaktif',
            $jenis->pelanggaran_siswa_count ?? 0,
        ];
    }
}

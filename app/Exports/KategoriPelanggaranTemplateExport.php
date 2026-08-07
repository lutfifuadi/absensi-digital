<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KategoriPelanggaranTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function collection()
    {
        return collect([
            [
                'nama' => 'Kedisiplinan',
                'deskripsi' => 'Pelanggaran terkait kehadiran, keterlambatan, dan jam belajar',
                'warna' => '#ef4444',
                'urutan' => 1,
                'is_aktif' => 1,
            ],
            [
                'nama' => 'Kerapian',
                'deskripsi' => 'Pelanggaran terkait pemakaian atribut, seragam, dan penampilan',
                'warna' => '#f59e0b',
                'urutan' => 2,
                'is_aktif' => 1,
            ],
            [
                'nama' => 'Sikap & Perilaku',
                'deskripsi' => 'Pelanggaran terkait etika, norma, dan sopan santun',
                'warna' => '#8b5cf6',
                'urutan' => 3,
                'is_aktif' => 1,
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nama Kategori',
            'Deskripsi',
            'Warna Hex',
            'Urutan',
            'Status Aktif (1=Aktif, 0=Nonaktif)',
        ];
    }
}

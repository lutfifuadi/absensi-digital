<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JenisPelanggaranTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Nama Kategori',
            'Nama Pelanggaran',
            'Bobot Poin',
            'Deskripsi',
            'Status Aktif'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Kedisiplinan & Kerapian',
                'Terlambat masuk sekolah (< 15 menit)',
                5,
                'Datang setelah bel masuk berbunyi tanpa alasan sah.',
                'Aktif'
            ],
            [
                'Kedisiplinan & Kerapian',
                'Atribut seragam tidak lengkap',
                5,
                'Tidak memakai dasi, sabuk, topi, atau lokasi sekolah.',
                'Aktif'
            ],
            [
                'Kehadiran & Akses',
                'Bolos sekolah / lompat pagar',
                25,
                'Meninggalkan lingkungan sekolah sebelum jam KBM selesai tanpa izin.',
                'Aktif'
            ],
            [
                'Etika & Perilaku',
                'Merusak fasilitas sekolah',
                20,
                'Mencoret-coret meja, kursi, dinding atau merusak sarana sekolah.',
                'Aktif'
            ],
            [
                'Pelanggaran Berat & Keamanan',
                'Berkelahi / tawuran',
                50,
                'Melakukan tindakan kekerasan fisik terhadap sesama siswa.',
                'Aktif'
            ],
        ];
    }
}

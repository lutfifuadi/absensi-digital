<?php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RekapBulananSiswaExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected int $bulan;
    protected int $tahun;
    protected ?int $kelasId;

    public function __construct(int $bulan = null, int $tahun = null, int $kelasId = null)
    {
        $this->bulan = $bulan ?? now()->month;
        $this->tahun = $tahun ?? now()->year;
        $this->kelasId = $kelasId;
    }

    public function collection()
    {
        return AbsensiSiswa::with(['siswa', 'kelas', 'guru'])
            ->when($this->kelasId, fn ($q) => $q->where('kelas_id', $this->kelasId))
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->orderBy('tanggal')
            ->get();
    }

    /**
     * @param AbsensiSiswa $item
     */
    public function map($item): array
    {
        return [
            $item->tanggal->format('Y-m-d'),
            $item->kelas?->nama,
            (string) $item->siswa?->nis,
            $item->siswa?->nama_lengkap,
            $item->status,
            $item->jam_masuk,
            $item->jam_pulang,
            $item->guru?->nama_lengkap,
            $item->metode,
            $item->keterangan,
        ];
    }

    /**
     * Format kolom NIS (C) sebagai TEXT agar Excel tidak auto-format.
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kelas',
            'NIS',
            'Nama Siswa',
            'Status',
            'Jam Masuk',
            'Jam Pulang',
            'Guru',
            'Metode',
            'Keterangan',
        ];
    }
}

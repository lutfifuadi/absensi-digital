<?php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBulananSiswaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithCustomValueBinder, WithStyles, ShouldAutoSize
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

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value) || (is_string($value) && preg_match('/^[0-9]+$/', $value))) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
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
            (string) ($item->siswa?->nis ?? ''),
            $item->siswa?->nama_lengkap,
            ucfirst($item->status ?? '-'),
            $item->jam_masuk ?? '-',
            $item->jam_pulang ?? '-',
            $item->guru?->nama_lengkap ?? '-',
            ucfirst($item->metode ?? 'manual'),
            $item->keterangan ?? '-',
        ];
    }

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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E293B']
                ]
            ],
        ];
    }
}

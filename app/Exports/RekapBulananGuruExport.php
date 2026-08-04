<?php

namespace App\Exports;

use App\Models\AbsensiGuru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapBulananGuruExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder, WithStyles, ShouldAutoSize
{
    protected int $bulan;
    protected int $tahun;

    public function __construct(int $bulan = null, int $tahun = null)
    {
        $this->bulan = $bulan ?? now()->month;
        $this->tahun = $tahun ?? now()->year;
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
        return AbsensiGuru::with('guru')
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->orderBy('tanggal')
            ->get();
    }

    public function map($item): array
    {
        return [
            $item->tanggal ? $item->tanggal->format('Y-m-d') : '-',
            (string) ($item->guru?->nip ?? ''),
            $item->guru?->nama_lengkap ?? '-',
            ucfirst($item->status ?? '-'),
            $item->jam_masuk ?? '-',
            $item->jam_pulang ?? '-',
            ucfirst($item->metode ?? 'manual'),
            $item->keterangan ?? '-',
        ];
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIP', 'Nama Guru', 'Status', 'Jam Masuk', 'Jam Pulang', 'Metode', 'Keterangan'];
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

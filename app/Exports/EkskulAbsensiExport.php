<?php

namespace App\Exports;

use App\Services\EkskulAbsensiService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EkskulAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected int $ekskulId;
    protected int $bulan;
    protected int $tahun;
    protected string $namaEkskul;
    protected string $namaBulan;

    public function __construct(int $ekskulId, string $namaEkskul, int $bulan, int $tahun)
    {
        $this->ekskulId   = $ekskulId;
        $this->namaEkskul = $namaEkskul;
        $this->bulan      = $bulan;
        $this->tahun      = $tahun;
        $this->namaBulan  = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
    }

    public function collection()
    {
        $service = app(EkskulAbsensiService::class);

        return $service->getRekapPerSiswa($this->ekskulId, $this->bulan, $this->tahun);
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->siswa->nis ?? '-',
            $row->siswa->nama_lengkap ?? '-',
            $row->siswa->kelas->nama ?? '-',
            $row->hadir,
            $row->izin,
            $row->sakit,
            $row->alpha,
            $row->terlambat,
            $row->persentase . '%',
        ];
    }

    public function headings(): array
    {
        return [
            ['REKAP ABSENSI EKSTRAKURIKULER'],
            [$this->namaEkskul],
            ["Periode: {$this->namaBulan} {$this->tahun}"],
            [], // empty row as spacer
            ['No', 'NIS', 'Nama Siswa', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Terlambat', 'Persentase'],
        ];
    }

    public function title(): string
    {
        return "Rekap {$this->namaEkskul}";
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for header rows
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        // Header row styling
        $sheet->getStyle('A1:A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Column headings style
        $sheet->getStyle('A5:J5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '374151'],
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Data rows border
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A6:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']],
            ],
        ]);

        // Center align number columns
        $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E6:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}

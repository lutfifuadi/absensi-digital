<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class SiswaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithCustomValueBinder
{
    protected $search;
    protected $tahunAkademikId;

    public function __construct($search = null, $tahunAkademikId = null)
    {
        $this->search = $search;
        $this->tahunAkademikId = $tahunAkademikId;
    }

    public function collection()
    {
        return Siswa::with(['kelas', 'tahunAkademik'])
            ->when($this->tahunAkademikId, function ($query) {
                $query->where('tahun_akademik_id', $this->tahunAkademikId);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_lengkap', 'like', "%{$this->search}%")
                      ->orWhere('nis', 'like', "%{$this->search}%")
                      ->orWhere('nisn', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Kelas',
            'Tahun Akademik',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No HP',
            'No HP Ortu',
            'Status',
        ];
    }

    /**
     * @param Siswa $siswa
     */
    public function map($siswa): array
    {
        return [
            (string) ($siswa->nis ?? ''),
            (string) ($siswa->nisn ?? ''),
            (string) ($siswa->nama_lengkap ?? ''),
            (string) ($siswa->jenis_kelamin ?? ''),
            (string) ($siswa->kelas?->nama ?? '-'),
            (string) ($siswa->tahunAkademik?->tahun ?? '-'),
            (string) ($siswa->tempat_lahir ?? ''),
            (string) ($siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d-m-Y') : '-'),
            (string) ($siswa->alamat ?? ''),
            (string) ($siswa->no_hp ?? ''),
            (string) ($siswa->no_hp_ortu ?? ''),
            (string) ($siswa->status ?? ''),
        ];
    }

    /**
     * Format seluruh kolom sebagai TEXT agar Excel tidak auto-format
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
     * Bind values to DataType::TYPE_STRING explicitly
     */
    public function bindValue(Cell $cell, $value)
    {
        // Ubah nilainya menjadi string dan atur tipenya sebagai DataType::TYPE_STRING secara eksplisit
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
        return true;
    }
}

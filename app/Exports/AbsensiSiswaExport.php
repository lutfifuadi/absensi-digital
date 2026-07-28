<?php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class AbsensiSiswaExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithCustomValueBinder
{
    protected array $filters;
    protected bool $isWaliKelas;
    protected $user;
    protected $tahunAjaranId;

    public function __construct(array $filters = [], bool $isWaliKelas = false, $user = null, $tahunAjaranId = null)
    {
        $this->filters = $filters;
        $this->isWaliKelas = $isWaliKelas;
        $this->user = $user;
        $this->tahunAjaranId = $tahunAjaranId;
    }

    public function collection()
    {
        $query = AbsensiSiswa::with(['siswa:id,nama_lengkap,nis,kelas_id', 'kelas:id,nama', 'guru:id,nama_lengkap']);

        // ── Wali kelas restriction ──
        if ($this->isWaliKelas && $this->user) {
            $guru = $this->user->guru;
            $kelasWaliId = null;
            if ($guru) {
                $kelasWali = \App\Models\Kelas::where('wali_kelas_id', $guru->id)
                    ->where('tahun_akademik_id', $this->tahunAjaranId)
                    ->first();
                if ($kelasWali) {
                    $kelasWaliId = $kelasWali->id;
                }
            }

            if ($kelasWaliId) {
                $query->where('absensi_siswa.kelas_id', $kelasWaliId);
                $this->filters['kelas_id'] = $kelasWaliId;
            } else {
                $query->whereNull('absensi_siswa.id');
            }
        }

        // ── Filters ──
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['kelas_id'])) {
            $query->where('absensi_siswa.kelas_id', $this->filters['kelas_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('absensi_siswa.status', $this->filters['status']);
        }

        if (!empty($this->filters['tanggal_from'])) {
            $query->whereDate('absensi_siswa.tanggal', '>=', $this->filters['tanggal_from']);
        }

        if (!empty($this->filters['tanggal_to'])) {
            $query->whereDate('absensi_siswa.tanggal', '<=', $this->filters['tanggal_to']);
        }

        // ── Sorting ──
        $sortBy = $this->filters['sort_by'] ?? 'tanggal';
        $sortDir = $this->filters['sort_dir'] ?? 'desc';

        // Validate sort
        if (!in_array($sortBy, ['tanggal', 'status'])) {
            $sortBy = 'tanggal';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $query->orderBy('absensi_siswa.' . $sortBy, $sortDir);

        return $query->get();
    }

    /**
     * @param AbsensiSiswa $item
     */
    public function map($item): array
    {
        return [
            $item->tanggal ? (is_string($item->tanggal) ? $item->tanggal : $item->tanggal->format('Y-m-d')) : '',
            $item->kelas?->nama ?? '',
            (string) ($item->siswa?->nis ?? ''),
            $item->siswa?->nama_lengkap ?? '',
            $item->status ?? '',
            $item->jam_masuk ?? '',
            $item->jam_pulang ?? '',
            $item->guru?->nama_lengkap ?? '',
            $item->metode ?? '',
            $item->keterangan ?? '',
        ];
    }

    /**
     * Format seluruh kolom sebagai TEXT agar Excel tidak auto-format.
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
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
        return true;
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

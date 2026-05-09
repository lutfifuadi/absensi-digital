<?php
/*
 * Created At: 2026-05-09
 * Description: Export class for Guru data
 */

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuruExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        return Guru::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_lengkap', 'like', "%{$this->search}%")
                      ->orWhere('nip', 'like', "%{$this->search}%")
                      ->orWhere('mata_pelajaran', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nama_lengkap')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIP',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Mata Pelajaran',
            'Jabatan',
            'No HP',
            'Status',
        ];
    }

    /**
     * @param Guru $guru
     */
    public function map($guru): array
    {
        return [
            $guru->nip,
            $guru->nama_lengkap,
            $guru->jenis_kelamin,
            $guru->mata_pelajaran,
            $guru->jabatan,
            $guru->no_hp,
            $guru->status,
        ];
    }
}

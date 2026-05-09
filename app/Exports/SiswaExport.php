<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiswaExport implements FromCollection, WithHeadings, WithMapping
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
            $siswa->nis,
            $siswa->nisn,
            $siswa->nama_lengkap,
            $siswa->jenis_kelamin,
            $siswa->kelas?->nama ?? '-',
            $siswa->tahunAkademik?->tahun ?? '-',
            $siswa->tempat_lahir,
            $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('d-m-Y') : '-',
            $siswa->alamat,
            $siswa->no_hp,
            $siswa->no_hp_ortu,
            $siswa->status,
        ];
    }
}

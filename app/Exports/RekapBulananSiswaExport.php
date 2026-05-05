<?php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapBulananSiswaExport implements FromCollection, WithHeadings
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
        $query = AbsensiSiswa::with(['siswa', 'kelas', 'guru'])
            ->when($this->kelasId, fn ($q) => $q->where('kelas_id', $this->kelasId))
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->orderBy('tanggal')
            ->get();

        return $query->map(function (AbsensiSiswa $item) {
            return [
                'Tanggal' => $item->tanggal->format('Y-m-d'),
                'Kelas' => $item->kelas?->nama,
                'NIS' => $item->siswa?->nis,
                'Nama Siswa' => $item->siswa?->nama_lengkap,
                'Status' => $item->status,
                'Jam Masuk' => $item->jam_masuk,
                'Jam Pulang' => $item->jam_pulang,
                'Guru' => $item->guru?->nama_lengkap,
                'Metode' => $item->metode,
                'Keterangan' => $item->keterangan,
            ];
        });
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

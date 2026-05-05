<?php

namespace App\Exports;

use App\Models\AbsensiGuru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapBulananGuruExport implements FromCollection, WithHeadings
{
    protected int $bulan;
    protected int $tahun;

    public function __construct(int $bulan = null, int $tahun = null)
    {
        $this->bulan = $bulan ?? now()->month;
        $this->tahun = $tahun ?? now()->year;
    }

    public function collection()
    {
        return AbsensiGuru::with('guru')
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->orderBy('tanggal')
            ->get()
            ->map(fn (AbsensiGuru $item) => [
                'Tanggal'    => $item->tanggal->format('Y-m-d'),
                'NIP'        => $item->guru?->nip,
                'Nama Guru'  => $item->guru?->nama_lengkap,
                'Status'     => $item->status,
                'Jam Masuk'  => $item->jam_masuk,
                'Jam Pulang' => $item->jam_pulang,
                'Metode'     => $item->metode,
                'Keterangan' => $item->keterangan,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIP', 'Nama Guru', 'Status', 'Jam Masuk', 'Jam Pulang', 'Metode', 'Keterangan'];
    }
}

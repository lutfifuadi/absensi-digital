<?php

namespace App\Exports;

use App\Models\AbsensiStaff;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RekapBulananStaffExport implements FromCollection, WithHeadings
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
        return AbsensiStaff::with('staff')
            ->whereYear('tanggal', $this->tahun)
            ->whereMonth('tanggal', $this->bulan)
            ->orderBy('tanggal')
            ->get()
            ->map(fn (AbsensiStaff $item) => [
                'Tanggal'    => $item->tanggal->format('Y-m-d'),
                'NIP'        => $item->staff?->nip,
                'Nama Staff' => $item->staff?->nama_lengkap,
                'Jabatan'    => $item->staff?->jabatan,
                'Status'     => $item->status,
                'Jam Masuk'  => $item->jam_masuk,
                'Jam Pulang' => $item->jam_pulang,
                'Metode'     => $item->metode,
                'Keterangan' => $item->keterangan,
            ]);
    }

    public function headings(): array
    {
        return ['Tanggal', 'NIP', 'Nama Staff', 'Jabatan', 'Status', 'Jam Masuk', 'Jam Pulang', 'Metode', 'Keterangan'];
    }
}

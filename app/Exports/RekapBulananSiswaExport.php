<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RekapBulananSiswaExport implements WithMultipleSheets
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

    public function sheets(): array
    {
        return [
            new RekapMatriksSiswaSheet($this->bulan, $this->tahun, $this->kelasId),
            new RekapDetailJamSiswaSheet($this->bulan, $this->tahun, $this->kelasId),
        ];
    }
}

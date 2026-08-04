<?php

namespace App\Exports;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class RekapMatriksSiswaSheet extends DefaultValueBinder implements FromView, WithTitle, WithCustomValueBinder, ShouldAutoSize
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

    public function title(): string
    {
        return 'Rekap Matriks Presensi';
    }

    public function bindValue(Cell $cell, $value)
    {
        if (is_numeric($value) || (is_string($value) && preg_match('/^[0-9]+$/', $value))) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function view(): View
    {
        $bulan = $this->bulan;
        $tahun = $this->tahun;
        $kelas = $this->kelasId ? Kelas::find($this->kelasId) : null;
        $siswaList = $this->kelasId
            ? Siswa::where('kelas_id', $this->kelasId)->orderBy('nama_lengkap')->get()
            : Siswa::orderBy('nama_lengkap')->get();

        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dates[] = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
        }

        $absensiPivot = [];
        if ($siswaList->isNotEmpty()) {
            $absensiRows = AbsensiSiswa::whereIn('siswa_id', $siswaList->pluck('id'))
                ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
                ->get()->groupBy('siswa_id');

            foreach ($siswaList as $s) {
                $rows = $absensiRows->get($s->id, collect())->keyBy(fn ($r) => $r->tanggal->format('Y-m-d'));
                foreach ($dates as $date) {
                    $absensiPivot[$s->id][$date] = $rows->get($date)?->status ?? null;
                }
            }
        }

        $namaBulan   = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
        $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

        return view('exports.rekap-siswa-excel', compact(
            'siswaList', 'dates', 'absensiPivot', 'kelas',
            'bulan', 'tahun', 'namaBulan', 'namaSekolah'
        ));
    }
}

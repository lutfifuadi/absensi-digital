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

class RekapDetailJamSiswaSheet extends DefaultValueBinder implements FromView, WithTitle, WithCustomValueBinder, ShouldAutoSize
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
        return 'Rincian Jam Masuk & Pulang';
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

        $absensiLogs = AbsensiSiswa::with(['siswa.kelas', 'guru'])
            ->when($this->kelasId, fn ($q) => $q->where('kelas_id', $this->kelasId))
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->orderBy('tanggal', 'asc')
            ->orderBy('kelas_id', 'asc')
            ->get();

        $namaBulan   = Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F');
        $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

        return view('exports.rekap-detail-jam-excel', compact(
            'absensiLogs', 'kelas', 'bulan', 'tahun', 'namaBulan', 'namaSekolah'
        ));
    }
}

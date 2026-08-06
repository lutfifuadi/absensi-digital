<?php

namespace App\Exports;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunAkademik;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class JadwalPelajaranTemplateExport implements WithMultipleSheets
{
    protected ?int $tahunAkademikId;

    public function __construct(?int $tahunAkademikId = null)
    {
        $this->tahunAkademikId = $tahunAkademikId ?? (session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id'));
    }

    public function sheets(): array
    {
        $kelas = Kelas::where('tahun_akademik_id', $this->tahunAkademikId)->orderBy('nama')->pluck('nama')->all();
        $mapels = Mapel::orderBy('nama_mapel')->pluck('nama_mapel')->all();
        $gurus = Guru::orderBy('nama_lengkap')->get()->map(function ($g) {
            return $g->nama_lengkap . ($g->nip ? " [{$g->nip}]" : '');
        })->all();
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

        return [
            new JadwalFormSheet($kelas, $haris, $mapels, $gurus),
            new JadwalReferensiSheet($kelas, $haris, $mapels, $gurus),
        ];
    }
}

class JadwalFormSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected array $kelas;
    protected array $haris;
    protected array $mapels;
    protected array $gurus;

    public function __construct(array $kelas, array $haris, array $mapels, array $gurus)
    {
        $this->kelas = $kelas;
        $this->haris = $haris;
        $this->mapels = $mapels;
        $this->gurus = $gurus;
    }

    public function title(): string
    {
        return 'Form_Jadwal';
    }

    public function collection()
    {
        // Berikan 5 baris contoh awal
        $sampleKelas = $this->kelas[0] ?? 'X-A';
        $sampleMapel = $this->mapels[0] ?? 'Matematika';
        $sampleGuru  = $this->gurus[0] ?? 'Guru Contoh';

        return collect([
            [$sampleKelas, 'Senin', '07:00', '07:45', $sampleMapel, $sampleGuru],
            [$sampleKelas, 'Senin', '07:45', '08:30', $sampleMapel, $sampleGuru],
        ]);
    }

    public function headings(): array
    {
        return [
            'Nama Kelas',
            'Hari',
            'Jam Mulai (HH:MM)',
            'Jam Selesai (HH:MM)',
            'Mata Pelajaran',
            'Guru Pengampu',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = 200; // Pasang validasi dropdown hingga baris 200

                $kelasMaxRow = max(count($this->kelas) + 1, 2);
                $hariMaxRow  = max(count($this->haris) + 1, 2);
                $mapelMaxRow = max(count($this->mapels) + 1, 2);
                $guruMaxRow  = max(count($this->gurus) + 1, 2);

                // Kolom A: Nama Kelas
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validation = $sheet->getCell("A{$i}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1("Data_Referensi!\$A\$2:\$A\${$kelasMaxRow}");
                }

                // Kolom B: Hari
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validation = $sheet->getCell("B{$i}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1("Data_Referensi!\$B\$2:\$B\${$hariMaxRow}");
                }

                // Kolom E: Mata Pelajaran
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validation = $sheet->getCell("E{$i}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1("Data_Referensi!\$C\$2:\$C\${$mapelMaxRow}");
                }

                // Kolom F: Guru Pengampu
                for ($i = 2; $i <= $rowCount; $i++) {
                    $validation = $sheet->getCell("F{$i}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1("Data_Referensi!\$D\$2:\$D\${$guruMaxRow}");
                }
            },
        ];
    }
}

class JadwalReferensiSheet implements FromCollection, WithHeadings, WithTitle
{
    protected array $kelas;
    protected array $haris;
    protected array $mapels;
    protected array $gurus;

    public function __construct(array $kelas, array $haris, array $mapels, array $gurus)
    {
        $this->kelas = $kelas;
        $this->haris = $haris;
        $this->mapels = $mapels;
        $this->gurus = $gurus;
    }

    public function title(): string
    {
        return 'Data_Referensi';
    }

    public function collection()
    {
        $maxCount = max(count($this->kelas), count($this->haris), count($this->mapels), count($this->gurus));
        $rows = [];

        for ($i = 0; $i < $maxCount; $i++) {
            $rows[] = [
                $this->kelas[$i] ?? '',
                $this->haris[$i] ?? '',
                $this->mapels[$i] ?? '',
                $this->gurus[$i] ?? '',
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Daftar Kelas',
            'Daftar Hari',
            'Daftar Mapel',
            'Daftar Guru',
        ];
    }
}

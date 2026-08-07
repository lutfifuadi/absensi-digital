<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class JadwalPelajaranImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    protected int $tahunAkademikId;
    public int $importedCount = 0;
    public array $errors = [];

    public function __construct(?int $tahunAkademikId = null)
    {
        $this->tahunAkademikId = $tahunAkademikId ?? (session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id'));
    }

    /**
     * Parse nilai jam dari berbagai format Excel (string HH:MM, HH:MM:SS, float/numeric Excel, dsb).
     */
    private function parseTime(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        // Handle jika nilai berupa float/numeric khas Excel (misal 0.3125 untuk 07:30)
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject($value)->format('H:i');
            } catch (\Throwable $e) {
                // Fallback jika konversi gagal
            }
        }

        $str = trim((string) $value);

        // Format H:i atau H:i:s -> ambil H:i (dengan padding misal 7:30 -> 07:30)
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return $str;
    }

    public function collection(Collection $rows)
    {
        $kelases = Kelas::where('tahun_akademik_id', $this->tahunAkademikId)->get();
        $gurus = Guru::all();
        $validHaris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // header + 1-based index

            $namaKelas  = trim((string) ($row['nama_kelas'] ?? $row['kelas'] ?? ''));
            $hariRaw    = trim((string) ($row['hari'] ?? ''));
            $hari       = ucfirst(strtolower($hariRaw));

            // Dukung 'jam_mulai_hhmm' (key dari header template: 'Jam Mulai (HH:MM)'), 'jam_mulai_hh_mm', dan 'jam_mulai'
            $rawJamMulai   = $row['jam_mulai_hhmm'] ?? $row['jam_mulai_hh_mm'] ?? $row['jam_mulai'] ?? null;
            $rawJamSelesai = $row['jam_selesai_hhmm'] ?? $row['jam_selesai_hh_mm'] ?? $row['jam_selesai'] ?? null;

            $jamMulai   = $this->parseTime($rawJamMulai);
            $jamSelesai = $this->parseTime($rawJamSelesai);

            $mapelName  = trim((string) ($row['mata_pelajaran'] ?? $row['mapel'] ?? ''));
            $guruRaw    = trim((string) ($row['guru_pengampu'] ?? $row['guru'] ?? ''));

            // Abaikan baris kosong total
            if (empty($namaKelas) && empty($mapelName) && empty($jamMulai) && empty($jamSelesai)) {
                continue;
            }

            // Peringatan jika kelas / mapel / hari diisi tapi jam kosong
            if (empty($jamMulai) || empty($jamSelesai)) {
                $this->errors[] = "Baris {$rowNum}: Jam mulai dan jam selesai tidak boleh kosong.";
                continue;
            }

            if (!in_array($hari, $validHaris, true)) {
                $this->errors[] = "Baris {$rowNum}: Hari '{$hariRaw}' tidak valid (Gunakan: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Ahad).";
                continue;
            }

            // Cari Kelas
            $kelasObj = $kelases->first(fn ($k) => strtolower($k->nama) === strtolower($namaKelas));
            if (!$kelasObj) {
                $this->errors[] = "Baris {$rowNum}: Kelas '{$namaKelas}' tidak ditemukan pada tahun akademik aktif.";
                continue;
            }

            // Cari Guru (jika diisi)
            $guruId = null;
            if (!empty($guruRaw)) {
                if (preg_match('/\[(.*?)\]/', $guruRaw, $mNip)) {
                    $nip = trim($mNip[1]);
                    $guruObj = $gurus->first(fn ($g) => $g->nip === $nip);
                } else {
                    $guruObj = $gurus->first(fn ($g) => strtolower($g->nama_lengkap) === strtolower($guruRaw) || ($g->nip && $g->nip === $guruRaw));
                }
                if ($guruObj) {
                    $guruId = $guruObj->id;
                }
            }

            // Upsert Jadwal Pelajaran
            JadwalPelajaran::updateOrCreate(
                [
                    'kelas_id'  => $kelasObj->id,
                    'hari'      => $hari,
                    'jam_mulai' => $jamMulai,
                ],
                [
                    'guru_id'        => $guruId,
                    'mata_pelajaran' => $mapelName,
                    'jam_selesai'    => $jamSelesai,
                ]
            );

            $this->importedCount++;
        }
    }
}


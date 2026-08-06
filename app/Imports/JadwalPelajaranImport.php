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

    public function collection(Collection $rows)
    {
        $kelases = Kelas::where('tahun_akademik_id', $this->tahunAkademikId)->get();
        $gurus = Guru::all();
        $validHaris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad'];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // header + 1-based index

            $namaKelas  = trim($row['nama_kelas'] ?? $row['kelas'] ?? '');
            $hariRaw    = trim($row['hari'] ?? '');
            $hari       = ucfirst(strtolower($hariRaw));
            $jamMulai   = trim($row['jam_mulai_hh_mm'] ?? $row['jam_mulai'] ?? '');
            $jamSelesai = trim($row['jam_selesai_hh_mm'] ?? $row['jam_selesai'] ?? '');
            $mapelName  = trim($row['mata_pelajaran'] ?? $row['mapel'] ?? '');
            $guruRaw    = trim($row['guru_pengampu'] ?? $row['guru'] ?? '');

            if (empty($namaKelas) && empty($mapelName)) {
                continue;
            }

            if (!in_array($hari, $validHaris, true)) {
                $this->errors[] = "Baris {$rowNum}: Hari '{$hariRaw}' tidak valid (Gunakan: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Ahad).";
                continue;
            }

            // Normalisasi Jam (misal "7:00" -> "07:00")
            if (preg_match('/^(\d{1,2}):(\d{2})/', $jamMulai, $m)) {
                $jamMulai = sprintf('%02d:%02d', $m[1], $m[2]);
            }
            if (preg_match('/^(\d{1,2}):(\d{2})/', $jamSelesai, $m)) {
                $jamSelesai = sprintf('%02d:%02d', $m[1], $m[2]);
            }

            if (empty($jamMulai) || empty($jamSelesai)) {
                $this->errors[] = "Baris {$rowNum}: Jam mulai dan jam selesai tidak boleh kosong.";
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

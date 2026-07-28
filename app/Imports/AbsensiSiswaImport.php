<?php

namespace App\Imports;

use App\Models\AbsensiSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AbsensiSiswaImport implements ToCollection, WithHeadingRow
{
    protected ?int $guruId = null;
    protected int $successCount = 0;
    protected array $importErrors = [];
    protected int $rowCount = 0;

    public function __construct(?int $guruId = null)
    {
        $this->guruId = $guruId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $this->rowCount++;
            
            // Normalize NIS
            $rawNis = isset($row['nis']) ? trim((string)$row['nis']) : null;
            if ($rawNis !== null && preg_match('/^\d+\.0$/', $rawNis)) {
                $rawNis = substr($rawNis, 0, -2);
            }

            // Normalize Keterangan
            $rawKeterangan = isset($row['keterangan']) ? trim((string)$row['keterangan']) : null;
            if ($rawKeterangan === '-' || $rawKeterangan === '') {
                $rawKeterangan = null;
            }

            // Normalize Status
            $rawStatus = isset($row['status']) ? strtolower(trim((string)$row['status'])) : null;
            if ($rawStatus === '-' || $rawStatus === '') {
                $rawStatus = null;
            }

            // Normalize Metode
            $rawMetode = isset($row['metode']) ? strtolower(trim((string)$row['metode'])) : 'import';
            if ($rawMetode === '-' || $rawMetode === '') {
                $rawMetode = 'import';
            }

            // Normalize row data
            $rowData = [
                'tanggal'    => $this->parseDate($row['tanggal'] ?? null),
                'nis'        => $rawNis,
                'status'     => $rawStatus,
                'jam_masuk'  => $this->parseTime($row['jam_masuk'] ?? null),
                'jam_pulang' => $this->parseTime($row['jam_pulang'] ?? null),
                'keterangan' => $rawKeterangan,
                'metode'     => $rawMetode,
            ];

            // Manual Validation
            $validator = Validator::make($rowData, [
                'nis' => ['required', 'string'],
                'tanggal' => ['required', 'date_format:Y-m-d'],
                'status' => ['required', Rule::in(['hadir', 'sakit', 'izin', 'alpha', 'terlambat', 'dispen'])],
                'jam_masuk' => ['nullable', 'date_format:H:i'],
                'jam_pulang' => ['nullable', 'date_format:H:i'],
                'keterangan' => ['nullable', 'string'],
            ], [
                'status.in' => 'Status harus salah satu dari: hadir, sakit, izin, alpha, terlambat, dispen.',
            ]);

            if ($validator->fails()) {
                $this->importErrors[] = [
                    'row' => $this->rowCount + 1,
                    'nis' => $rowData['nis'] ?? 'N/A',
                    'error' => implode(', ', $validator->errors()->all()),
                ];
                continue;
            }

            // Find Siswa by NIS
            $siswa = Siswa::where('nis', $rowData['nis'])->first();
            if (!$siswa) {
                $this->importErrors[] = [
                    'row' => $this->rowCount + 1,
                    'nis' => $rowData['nis'],
                    'error' => "Siswa dengan NIS '{$rowData['nis']}' tidak ditemukan.",
                ];
                continue;
            }

            $kelasId = $siswa->kelas_id;
            if (!$kelasId && !empty($row['kelas'])) {
                $kelasObj = \App\Models\Kelas::where('nama', trim((string)$row['kelas']))->first();
                if ($kelasObj) {
                    $kelasId = $kelasObj->id;
                }
            }

            // Overwrite logic (update or create)
            AbsensiSiswa::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'tanggal' => $rowData['tanggal'],
                ],
                [
                    'kelas_id' => $kelasId,
                    'jam_masuk' => $rowData['jam_masuk'],
                    'jam_pulang' => $rowData['jam_pulang'],
                    'status' => $rowData['status'],
                    'keterangan' => $rowData['keterangan'],
                    'guru_id' => $this->guruId,
                    'metode' => $rowData['metode'],
                ]
            );

            $this->successCount++;
        }
    }

    public function getImportResult(): array
    {
        return [
            'success' => $this->successCount,
            'errors' => $this->importErrors,
        ];
    }

    private function parseDate($value): ?string
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $valueStr = trim((string)$value);
        if ($valueStr === '-' || $valueStr === '') {
            return null;
        }

        // Try dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $valueStr)) {
            try {
                return Carbon::createFromFormat('d/m/Y', $valueStr)->format('Y-m-d');
            } catch (\Throwable $e) {
                // pass
            }
        }

        // Try yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valueStr)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $valueStr)->format('Y-m-d');
            } catch (\Throwable $e) {
                // pass
            }
        }

        try {
            return Carbon::parse($valueStr)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseTime($value): ?string
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Date::excelToDateTimeObject($value)->format('H:i');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $valueStr = trim((string)$value);
        if ($valueStr === '-' || $valueStr === '') {
            return null;
        }

        if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $valueStr)) {
            return substr($valueStr, 0, 5);
        }

        try {
            return Carbon::parse($valueStr)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
